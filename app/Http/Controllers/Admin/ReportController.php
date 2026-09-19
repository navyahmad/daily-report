<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateDailyReportRequest;
use App\Models\DailyReport;
use App\Models\Division;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display a listing of reports with filters.
     */
    public function index(Request $request): View
    {
        $query = DailyReport::with(['employee', 'division']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('report_date', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('date')) {
            $query->whereDate('report_date', $request->date);
        }

        if ($request->filled('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('employee_name_snapshot', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $reports = $query->orderBy('report_date', 'desc')
            ->orderBy('submitted_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $divisions = Division::orderBy('name')->get();

        return view('admin.reports.index', compact('reports', 'divisions'));
    }

    /**
     * Display the specified report details.
     */
    public function show(DailyReport $report): View
    {
        $report->load(['employee', 'division']);

        return view('admin.reports.show', compact('report'));
    }

    /**
     * Show the form for editing the specified report.
     */
    public function edit(DailyReport $report): View
    {
        $report->load(['employee', 'division']);
        $divisions = Division::orderBy('name')->get();
        $employees = Employee::where('division_id', $report->division_id)->orderBy('name')->get();

        return view('admin.reports.edit', compact('report', 'divisions', 'employees'));
    }

    /**
     * Update the specified report in storage.
     */
    public function update(UpdateDailyReportRequest $request, DailyReport $report): RedirectResponse
    {
        $validated = $request->validated();

        $employee = Employee::find($validated['employee_id']);
        $division = Division::find($validated['division_id']);

        $report->update([
            'employee_id' => $validated['employee_id'],
            'division_id' => $validated['division_id'],
            'report_date' => $validated['report_date'],
            'email' => $validated['email'],
            'status' => $validated['status'],
            'employee_name_snapshot' => $employee ? $employee->name : $report->employee_name_snapshot,
            'division_name_snapshot' => $division ? $division->name : $report->division_name_snapshot,
            'division_code_snapshot' => $division ? $division->code : $report->division_code_snapshot,
            'form_data' => $validated['form_data'],
        ]);

        return redirect()->route('admin.reports.show', $report)
            ->with('success', 'Laporan harian berhasil diperbarui.');
    }

    /**
     * Cancel the specified report.
     */
    public function cancel(DailyReport $report): RedirectResponse
    {
        $report->update(['status' => 'cancelled']);

        return back()->with('success', 'Laporan berhasil ditandai sebagai dibatalkan.');
    }

    /**
     * Restore a cancelled report.
     */
    public function restore(DailyReport $report): RedirectResponse
    {
        $report->update(['status' => 'active']);

        return back()->with('success', 'Laporan berhasil dipulihkan ke status aktif.');
    }

    /**
     * Export reports to CSV (Excel compatible).
     */
    public function export(Request $request): StreamedResponse
    {
        $query = DailyReport::with(['employee', 'division']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('report_date', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('date')) {
            $query->whereDate('report_date', $request->date);
        }

        if ($request->filled('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->orderBy('report_date', 'desc')
            ->orderBy('submitted_at', 'desc')
            ->get();

        $filename = 'daily_reports_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($reports) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Microsoft Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // Header row
            fputcsv($handle, [
                'ID',
                'Tanggal Pekerjaan',
                'Nama Karyawan',
                'Divisi',
                'Email',
                'Status Laporan',
                'Waktu Pengiriman',
                'Rincian Laporan (JSON / Detail)',
            ]);

            foreach ($reports as $report) {
                $formDataSummary = [];
                $data = is_array($report->form_data) ? $report->form_data : [];
                $code = $report->division_code_snapshot;
                $v = (int) ($report->form_version ?? 1);

                if ($code === 'teknisi' && $v >= 2 && ! empty($data['work_items'])) {
                    $itemsStr = [];
                    foreach ((array) $data['work_items'] as $item) {
                        $t = $item['type'] ?? '';
                        $custom = $item['custom_type'] ?? '';
                        $tName = ($t === 'lainnya' && $custom) ? $custom : ucfirst(str_replace('_', ' ', $t));
                        $st = ucfirst($item['status'] ?? 'selesai');
                        $det = $item['detail'] ?? '';
                        $itemsStr[] = "[{$tName} ({$st}): {$det}]";
                    }
                    $formDataSummary[] = 'Pekerjaan: '.implode('; ', $itemsStr);
                    if (! empty($data['kendala'])) {
                        $formDataSummary[] = 'Kendala: '.$data['kendala'];
                    }
                    if (! empty($data['rencana_besok'])) {
                        $formDataSummary[] = 'Rencana Besok: '.$data['rencana_besok'];
                    }
                } elseif ($code === 'admin_sales' && $v >= 2 && ! empty($data['today_activities'])) {
                    $todayStr = [];
                    foreach ((array) $data['today_activities'] as $act) {
                        $det = $data['today_activity_details'][$act] ?? '';
                        $todayStr[] = ucfirst(str_replace('_', ' ', $act)).($det ? " ({$det})" : '');
                    }
                    $formDataSummary[] = 'Pekerjaan Hari Ini: '.implode('; ', $todayStr);

                    $tomStr = [];
                    foreach ((array) ($data['tomorrow_activities'] ?? []) as $act) {
                        $det = $data['tomorrow_activity_details'][$act] ?? '';
                        $tomStr[] = ucfirst(str_replace('_', ' ', $act)).($det ? " ({$det})" : '');
                    }
                    $formDataSummary[] = 'Rencana Besok: '.implode('; ', $tomStr);
                    $formDataSummary[] = "Follow Up: {$data['jumlah_customer']} | Quotation: {$data['jumlah_quotation']} | Closing: {$data['jumlah_closing']}";
                    if (! empty($data['kendala'])) {
                        $formDataSummary[] = 'Kendala: '.$data['kendala'];
                    }
                } elseif ($code === 'admin_project' && $v >= 2 && isset($data['documents_processed'])) {
                    $docStr = [];
                    foreach ((array) $data['documents_processed'] as $doc) {
                        $det = $data['document_details'][$doc] ?? '';
                        $docStr[] = strtoupper($doc).($det ? " ({$det})" : '');
                    }
                    $formDataSummary[] = 'Dokumen: '.implode('; ', $docStr);

                    $projStr = [];
                    foreach ((array) ($data['projects'] ?? []) as $proj) {
                        $projStr[] = ($proj['project_description'] ?? '')." ({$proj['progress_percent']}%)";
                    }
                    $formDataSummary[] = 'Project ('.count($projStr).'): '.(empty($projStr) ? 'Tidak ada' : implode('; ', $projStr));
                    if (! empty($data['kendala'])) {
                        $formDataSummary[] = 'Kendala: '.$data['kendala'];
                    }
                    if (! empty($data['rencana_besok'])) {
                        $formDataSummary[] = 'Rencana Besok: '.$data['rencana_besok'];
                    }
                } elseif ($code === 'admin_procurement' && $v >= 2 && isset($data['work_categories'])) {
                    $catStr = [];
                    foreach ((array) $data['work_categories'] as $cat) {
                        if ($cat === 'cari_barang') {
                            $catStr[] = 'Cari Barang: '.($data['detail_cari_barang'] ?? '-');
                        } elseif ($cat === 'cari_teknisi') {
                            $catStr[] = 'Cari Teknisi: '.($data['detail_cari_teknisi'] ?? '-');
                        } elseif ($cat === 'po') {
                            $catStr[] = "PO ({$data['jumlah_po']}): ".($data['detail_po_vendor'] ?? '-');
                        }
                    }
                    $formDataSummary[] = 'Pekerjaan: '.implode('; ', $catStr);
                    if (! empty($data['barang_diterima_dikirim'])) {
                        $formDataSummary[] = 'Barang: '.$data['barang_diterima_dikirim'];
                    }
                    if (! empty($data['kendala'])) {
                        $formDataSummary[] = 'Kendala: '.$data['kendala'];
                    }
                    if (! empty($data['rencana_besok'])) {
                        $formDataSummary[] = 'Rencana Besok: '.$data['rencana_besok'];
                    }
                } elseif ($code === 'finance' && $v >= 2 && isset($data['invoice_count'])) {
                    $formDataSummary[] = 'Pekerjaan: '.($data['pekerjaan_hari_ini'] ?? '-');
                    $invStr = [];
                    foreach ((array) ($data['invoice_details'] ?? []) as $inv) {
                        $invStr[] = $inv['description'] ?? '';
                    }
                    $formDataSummary[] = 'Invoice ('.count($invStr).'): '.(empty($invStr) ? 'Tidak ada' : implode('; ', $invStr));
                    $formDataSummary[] = 'Jurnal: '.($data['jurnal'] ?? '-');
                    if (! empty($data['rekap_kas_bank'])) {
                        $formDataSummary[] = 'Kas/Bank: '.$data['rekap_kas_bank'];
                    }
                    if (! empty($data['kendala'])) {
                        $formDataSummary[] = 'Kendala: '.$data['kendala'];
                    }
                    if (! empty($data['rencana_besok'])) {
                        $formDataSummary[] = 'Rencana Besok: '.$data['rencana_besok'];
                    }
                } elseif ($code === 'system_informasi' && $v >= 2 && ! empty($data['system_activities'])) {
                    $activityStr = [];
                    foreach ((array) $data['system_activities'] as $activity) {
                        $detail = $data['system_activity_details'][$activity] ?? '';
                        $activityStr[] = ucfirst(str_replace('_', ' ', $activity)).($detail ? " ({$detail})" : '');
                    }
                    $formDataSummary[] = 'Aktivitas: '.implode('; ', $activityStr);
                    $formDataSummary[] = 'Status: '.($data['status_pengerjaan'] ?? '-');
                    if (! empty($data['kendala'])) {
                        $formDataSummary[] = 'Kendala: '.$data['kendala'];
                    }
                    if (! empty($data['rencana_besok'])) {
                        $formDataSummary[] = 'Rencana Besok: '.$data['rencana_besok'];
                    }
                } else {
                    // Fallback for legacy v1 or other divisions
                    foreach ($data as $key => $val) {
                        if (is_array($val)) {
                            $formattedVal = json_encode($val, JSON_UNESCAPED_UNICODE);
                        } else {
                            $formattedVal = (string) $val;
                        }
                        $formDataSummary[] = ucfirst(str_replace('_', ' ', $key)).': '.$formattedVal;
                    }
                }

                fputcsv($handle, [
                    $report->id,
                    $report->report_date->format('Y-m-d'),
                    $report->employee_name_snapshot,
                    $report->division_name_snapshot,
                    $report->email,
                    $report->status === 'active' ? 'Aktif' : 'Dibatalkan',
                    $report->submitted_at->format('Y-m-d H:i:s'),
                    implode(' | ', $formDataSummary),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
