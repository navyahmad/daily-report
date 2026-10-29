<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HrdReportRequest;
use App\Models\Employee;
use App\Models\HrdReport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HrdReportController extends Controller
{
    /**
     * Display the HRD report history.
     */
    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);

        $reports = $this->filteredQuery($filters)
            ->with('creator')
            ->orderBy('report_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.hrd-reports.index', compact('reports', 'filters'));
    }

    /**
     * Show the form for creating a new HRD report.
     */
    public function create(): View
    {
        return view('admin.hrd-reports.create', [
            'formState' => $this->formState(),
            'employeeOptions' => $this->employeeOptions(),
        ]);
    }

    /**
     * Store a newly created HRD report.
     */
    public function store(HrdReportRequest $request): RedirectResponse
    {
        $report = HrdReport::create($this->payload($request) + [
            'created_by' => Auth::guard('admin_hrd')->id(),
        ]);

        return redirect()->route('admin.hrd-reports.show', $report)
            ->with('success', 'Laporan Daily Report HRD berhasil disimpan.');
    }

    /**
     * Display the specified HRD report.
     */
    public function show(HrdReport $hrdReport): View
    {
        $hrdReport->load('creator');

        return view('admin.hrd-reports.show', ['report' => $hrdReport]);
    }

    /**
     * Show the form for editing the specified HRD report.
     */
    public function edit(HrdReport $hrdReport): View
    {
        return view('admin.hrd-reports.edit', [
            'report' => $hrdReport,
            'formState' => $this->formState($hrdReport),
            'employeeOptions' => $this->employeeOptions($hrdReport),
        ]);
    }

    /**
     * Update the specified HRD report.
     */
    public function update(HrdReportRequest $request, HrdReport $hrdReport): RedirectResponse
    {
        $hrdReport->update($this->payload($request));

        return redirect()->route('admin.hrd-reports.show', $hrdReport)
            ->with('success', 'Laporan Daily Report HRD berhasil diperbarui.');
    }

    /**
     * Remove the specified HRD report.
     */
    public function destroy(HrdReport $hrdReport): RedirectResponse
    {
        $hrdReport->delete();

        return redirect()->route('admin.hrd-reports.index')
            ->with('success', 'Laporan Daily Report HRD berhasil dihapus.');
    }

    /**
     * Export HRD reports to CSV, one row per monitored item.
     */
    public function export(Request $request): StreamedResponse
    {
        $reports = $this->filteredQuery($this->validatedFilters($request))
            ->with('creator')
            ->orderBy('report_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'daily_report_hrd_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($reports) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Microsoft Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'ID Laporan',
                'Tanggal Laporan',
                'Jenis Monitoring',
                'Pembuat',
                'Waktu Dibuat',
                'Bagian',
                'Subjek',
                'Kategori',
                'Status Tindak Lanjut / Tindakan HRD',
                'Detail Catatan / Temuan',
                'Catatan Tindakan',
            ]);

            foreach ($reports as $report) {
                $base = [
                    $report->id,
                    $report->report_date->format('Y-m-d'),
                    $report->typeLabel(),
                    $report->creator?->name ?? '-',
                    $report->created_at->format('Y-m-d H:i:s'),
                ];

                if ($report->include_employee) {
                    foreach ((array) $report->employee_entries as $entry) {
                        foreach ((array) ($entry['items'] ?? []) as $item) {
                            fputcsv($handle, array_merge($base, [
                                'Monitoring Karyawan',
                                $entry['employee_name'] ?? '-',
                                HrdReport::EMPLOYEE_CATEGORIES[$item['category']] ?? $item['category'],
                                HrdReport::FOLLOW_UP_STATUSES[$item['status']] ?? $item['status'],
                                $item['note'] ?? '',
                                '',
                            ]));
                        }
                    }
                }

                if ($report->include_office) {
                    foreach ((array) $report->office_findings as $finding) {
                        fputcsv($handle, array_merge($base, [
                            'Kondisi Kantor',
                            'Kondisi Kantor',
                            HrdReport::OFFICE_CATEGORIES[$finding['category']] ?? $finding['category'],
                            HrdReport::OFFICE_ACTIONS[$finding['action']] ?? $finding['action'],
                            $finding['finding'] ?? '',
                            $finding['action_note'] ?? '',
                        ]));
                    }
                }
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * @return array{start_date?: string, end_date?: string}
     */
    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);
    }

    /**
     * @param  array{start_date?: string|null, end_date?: string|null}  $filters
     * @return Builder<HrdReport>
     */
    private function filteredQuery(array $filters): Builder
    {
        return HrdReport::query()
            ->when($filters['start_date'] ?? null, fn (Builder $query, string $date) => $query->whereDate('report_date', '>=', $date))
            ->when($filters['end_date'] ?? null, fn (Builder $query, string $date) => $query->whereDate('report_date', '<=', $date));
    }

    /**
     * Convert validated form input into the stored report structure.
     *
     * @return array<string, mixed>
     */
    private function payload(HrdReportRequest $request): array
    {
        $validated = $request->validated();
        $includeEmployee = (bool) $validated['include_employee'];
        $includeOffice = (bool) $validated['include_office'];

        $entries = null;
        if ($includeEmployee) {
            $names = Employee::whereIn('id', collect($validated['employees'])->pluck('employee_id')->filter())->pluck('name', 'id');

            $entries = collect($validated['employees'])->map(function (array $entry) use ($names) {
                $isManual = $entry['mode'] === 'manual';
                $categories = array_intersect(array_keys(HrdReport::EMPLOYEE_CATEGORIES), $entry['categories']);

                return [
                    'employee_id' => $isManual ? null : (int) $entry['employee_id'],
                    'employee_name' => $isManual ? trim($entry['manual_name']) : $names[$entry['employee_id']],
                    'items' => collect($categories)->map(fn (string $category) => [
                        'category' => $category,
                        'status' => $entry['details'][$category]['status'],
                        'note' => $entry['details'][$category]['note'],
                    ])->values()->all(),
                ];
            })->values()->all();
        }

        $findings = null;
        if ($includeOffice) {
            $categories = array_intersect(array_keys(HrdReport::OFFICE_CATEGORIES), $validated['office_categories']);

            $findings = collect($categories)->map(fn (string $category) => [
                'category' => $category,
                'finding' => $validated['office'][$category]['finding'],
                'action' => $validated['office'][$category]['action'],
                'action_note' => $validated['office'][$category]['action_note'],
            ])->values()->all();
        }

        return [
            'report_date' => $validated['report_date'],
            'include_employee' => $includeEmployee,
            'include_office' => $includeOffice,
            'employee_entries' => $entries,
            'office_findings' => $findings,
        ];
    }

    /**
     * Initial state for the Alpine form: old input after a failed submit, the saved report, or defaults.
     *
     * @return array<string, mixed>
     */
    private function formState(?HrdReport $report = null): array
    {
        if (session()->hasOldInput()) {
            $employees = array_values((array) old('employees', []));

            return [
                'report_date' => (string) old('report_date', ''),
                'include_employee' => (bool) old('include_employee'),
                'include_office' => (bool) old('include_office'),
                'employees' => $employees === [] ? [$this->entryState()] : array_map(fn ($entry) => $this->entryState((array) $entry), $employees),
                'office_categories' => array_values((array) old('office_categories', [])),
                'office' => $this->officeState((array) old('office', [])),
            ];
        }

        if ($report === null) {
            return [
                'report_date' => now()->toDateString(),
                'include_employee' => true,
                'include_office' => false,
                'employees' => [$this->entryState()],
                'office_categories' => [],
                'office' => $this->officeState(),
            ];
        }

        $employees = collect($report->employee_entries ?? [])->map(function (array $entry) {
            return $this->entryState([
                'mode' => $entry['employee_id'] ? 'master' : 'manual',
                'employee_id' => $entry['employee_id'],
                'manual_name' => $entry['employee_id'] ? '' : $entry['employee_name'],
                'categories' => collect($entry['items'] ?? [])->pluck('category')->all(),
                'details' => collect($entry['items'] ?? [])->keyBy('category')->all(),
            ]);
        })->all();

        $findings = collect($report->office_findings ?? [])->keyBy('category');

        return [
            'report_date' => $report->report_date->toDateString(),
            'include_employee' => $report->include_employee,
            'include_office' => $report->include_office,
            'employees' => $employees === [] ? [$this->entryState()] : $employees,
            'office_categories' => $findings->keys()->all(),
            'office' => $this->officeState($findings->all()),
        ];
    }

    /**
     * @param  array<string, mixed>  $entry
     * @return array<string, mixed>
     */
    private function entryState(array $entry = []): array
    {
        $details = [];
        foreach (array_keys(HrdReport::EMPLOYEE_CATEGORIES) as $category) {
            $details[$category] = [
                'status' => (string) ($entry['details'][$category]['status'] ?? ''),
                'note' => (string) ($entry['details'][$category]['note'] ?? ''),
            ];
        }

        return [
            'mode' => ($entry['mode'] ?? 'master') === 'manual' ? 'manual' : 'master',
            'employee_id' => (string) ($entry['employee_id'] ?? ''),
            'manual_name' => (string) ($entry['manual_name'] ?? ''),
            'categories' => array_values((array) ($entry['categories'] ?? [])),
            'details' => $details,
        ];
    }

    /**
     * @param  array<string, mixed>  $office
     * @return array<string, array{finding: string, action: string, action_note: string}>
     */
    private function officeState(array $office = []): array
    {
        $state = [];
        foreach (array_keys(HrdReport::OFFICE_CATEGORIES) as $category) {
            $state[$category] = [
                'finding' => (string) ($office[$category]['finding'] ?? ''),
                'action' => (string) ($office[$category]['action'] ?? ''),
                'action_note' => (string) ($office[$category]['action_note'] ?? ''),
            ];
        }

        return $state;
    }

    /**
     * Active employees, plus any already referenced by the report being edited.
     *
     * @return array<int, array{id: string, label: string}>
     */
    private function employeeOptions(?HrdReport $report = null): array
    {
        $referencedIds = collect($report?->employee_entries ?? [])->pluck('employee_id')->filter()->all();

        return Employee::with('division')
            ->where(fn (Builder $query) => $query->where('is_active', true)->orWhereIn('id', $referencedIds))
            ->orderBy('name')
            ->get()
            ->map(fn (Employee $employee) => [
                'id' => (string) $employee->id,
                'label' => $employee->name.' — '.($employee->division->name ?? '-'),
            ])
            ->all();
    }
}
