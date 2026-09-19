<?php

namespace App\Http\Requests;

use App\Models\DailyReport;
use App\Models\Division;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateDailyReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $division = Division::find($this->input('division_id'));
        $code = strtolower((string) $division?->code);

        $rules = [
            'division_id' => ['required', 'exists:divisions,id'],
            'employee_id' => [
                'required',
                'exists:employees,id',
            ],
            'email' => ['required', 'email'],
            'report_date' => ['required', 'date'],
            'status' => ['required', 'in:active,cancelled'],
            'form_data' => ['required', 'array'],
        ];

        if ($code === 'admin_procurement') {
            $rules['form_data.work_categories'] = ['required', 'array', 'min:1'];
            $rules['form_data.work_categories.*'] = ['string', 'in:cari_barang,cari_teknisi,po'];
            $rules['form_data.barang_diterima_dikirim'] = ['nullable', 'string'];
            $rules['form_data.kendala'] = ['nullable', 'string'];
            $rules['form_data.rencana_besok'] = ['nullable', 'string'];

            $categories = (array) $this->input('form_data.work_categories', []);
            if (in_array('cari_barang', $categories)) {
                $rules['form_data.detail_cari_barang'] = ['required', 'string'];
            }
            if (in_array('cari_teknisi', $categories)) {
                $rules['form_data.detail_cari_teknisi'] = ['required', 'string'];
            }
            if (in_array('po', $categories)) {
                $rules['form_data.jumlah_po'] = ['required', 'integer', 'min:1'];
                $rules['form_data.detail_po_vendor'] = ['required', 'string'];
            }
        } elseif ($code === 'admin_project') {
            $rules['form_data.documents_processed'] = ['required', 'array', 'min:1'];
            $rules['form_data.documents_processed.*'] = ['string', 'in:sow,bast,report,lainnya'];
            $rules['form_data.document_details'] = ['required', 'array'];

            $docs = (array) $this->input('form_data.documents_processed', []);
            foreach ($docs as $doc) {
                if (in_array($doc, ['sow', 'bast', 'report', 'lainnya'])) {
                    $rules["form_data.document_details.{$doc}"] = ['required', 'string'];
                }
            }

            $projectCount = (int) $this->input('form_data.project_count', 0);
            $rules['form_data.project_count'] = ['required', 'integer', 'min:0'];
            $rules['form_data.projects'] = ['array', "size:{$projectCount}"];
            if ($projectCount > 0) {
                $rules['form_data.projects.*.project_description'] = ['required', 'string'];
                $rules['form_data.projects.*.progress_percent'] = ['required', 'integer', 'min:0', 'max:100'];
            }

            $rules['form_data.kendala'] = ['nullable', 'string'];
            $rules['form_data.rencana_besok'] = ['nullable', 'string'];
        } elseif ($code === 'admin_sales') {
            $rules['form_data.today_activities'] = ['required', 'array', 'min:1'];
            $rules['form_data.today_activities.*'] = ['string', 'in:follow_up,membuat_penawaran,meeting,lainnya'];
            $rules['form_data.today_activity_details'] = ['required', 'array'];

            $todayActs = (array) $this->input('form_data.today_activities', []);
            foreach ($todayActs as $act) {
                if (in_array($act, ['follow_up', 'membuat_penawaran', 'meeting', 'lainnya'])) {
                    $rules["form_data.today_activity_details.{$act}"] = ['required', 'string'];
                }
            }

            $rules['form_data.tomorrow_activities'] = ['required', 'array', 'min:1'];
            $rules['form_data.tomorrow_activities.*'] = ['string', 'in:follow_up,membuat_penawaran,meeting,lainnya'];
            $rules['form_data.tomorrow_activity_details'] = ['required', 'array'];

            $tomorrowActs = (array) $this->input('form_data.tomorrow_activities', []);
            foreach ($tomorrowActs as $act) {
                if (in_array($act, ['follow_up', 'membuat_penawaran', 'meeting', 'lainnya'])) {
                    $rules["form_data.tomorrow_activity_details.{$act}"] = ['required', 'string'];
                }
            }

            $rules['form_data.jumlah_customer'] = ['required', 'integer', 'min:0'];
            $rules['form_data.jumlah_quotation'] = ['required', 'integer', 'min:0'];
            $rules['form_data.jumlah_closing'] = ['required', 'integer', 'min:0'];
            $rules['form_data.kendala'] = ['nullable', 'string'];
        } elseif ($code === 'finance') {
            $rules['form_data.pekerjaan_hari_ini'] = ['required', 'string'];

            $invoiceCount = (int) $this->input('form_data.invoice_count', 0);
            $rules['form_data.invoice_count'] = ['required', 'integer', 'min:0'];
            $rules['form_data.invoice_details'] = ['array', "size:{$invoiceCount}"];
            if ($invoiceCount > 0) {
                $rules['form_data.invoice_details.*.description'] = ['required', 'string'];
            }

            $rules['form_data.jurnal'] = ['required', 'string'];
            $rules['form_data.rekap_kas_bank'] = ['nullable', 'string'];
            $rules['form_data.kendala'] = ['nullable', 'string'];
            $rules['form_data.rencana_besok'] = ['nullable', 'string'];
        } elseif ($code === 'teknisi') {
            $rules['form_data.work_items'] = ['required', 'array', 'min:1'];
            $rules['form_data.work_items.*.type'] = ['required', 'string', 'in:instalasi,maintenance,troubleshooting,survey,remote_support,lainnya'];
            $rules['form_data.work_items.*.detail'] = ['required', 'string'];
            $rules['form_data.work_items.*.status'] = ['required', 'string', 'in:selesai,progres,pending'];
            $rules['form_data.kendala'] = ['nullable', 'string'];
            $rules['form_data.rencana_besok'] = ['nullable', 'string'];
        } elseif ($code === 'system_informasi') {
            $rules['form_data.system_activities'] = ['required', 'array', 'min:1'];
            $rules['form_data.system_activities.*'] = ['string', 'in:seo_organik,google_ads,social_media,maintenance_website,support_it,lainnya'];
            $rules['form_data.system_activity_details'] = ['required', 'array'];

            $activities = (array) $this->input('form_data.system_activities', []);
            foreach ($activities as $activity) {
                if (in_array($activity, ['seo_organik', 'google_ads', 'social_media', 'maintenance_website', 'support_it', 'lainnya'])) {
                    $rules["form_data.system_activity_details.{$activity}"] = ['required', 'string'];
                }
            }

            $rules['form_data.status_pengerjaan'] = ['required', 'string'];
            $rules['form_data.kendala'] = ['nullable', 'string'];
            $rules['form_data.rencana_besok'] = ['nullable', 'string'];
        }

        return $rules;
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $employeeId = $this->input('employee_id');
            $divisionId = $this->input('division_id');
            $reportDate = $this->input('report_date');
            $currentReportId = $this->route('report')?->id ?? $this->route('report');

            if ($employeeId && $reportDate) {
                $exists = DailyReport::where('employee_id', $employeeId)
                    ->whereDate('report_date', $reportDate)
                    ->where('id', '!=', $currentReportId)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('report_date', 'Laporan untuk karyawan ini pada tanggal yang dipilih sudah pernah dibuat.');
                }
            }

            $division = Division::find($divisionId);
            $code = strtolower((string) $division?->code);

            // Additional custom validations for Teknisi
            if ($code === 'teknisi') {
                $workItems = (array) $this->input('form_data.work_items', []);
                $typesSeen = [];
                foreach ($workItems as $index => $item) {
                    if (is_array($item)) {
                        $type = $item['type'] ?? '';
                        if ($type === 'lainnya' && empty(trim((string) ($item['custom_type'] ?? '')))) {
                            $validator->errors()->add("form_data.work_items.{$index}.custom_type", 'Nama/jenis pekerjaan lain wajib diisi.');
                        }
                        if (in_array($type, $typesSeen)) {
                            $validator->errors()->add("form_data.work_items.{$index}.type", 'Tidak boleh ada jenis pekerjaan yang duplikat.');
                        }
                        $typesSeen[] = $type;
                    }
                }
            }
        });
    }

    /**
     * Custom attribute names for validation errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'division_id' => 'Divisi',
            'employee_id' => 'Nama Karyawan',
            'email' => 'Email',
            'report_date' => 'Tanggal Pekerjaan',
            'status' => 'Status Laporan',
            'form_data.work_categories' => 'Pilihan Pekerjaan',
            'form_data.detail_cari_barang' => 'Detail Pencarian Barang',
            'form_data.detail_cari_teknisi' => 'Detail Pencarian Teknisi',
            'form_data.jumlah_po' => 'Jumlah PO Dibuat',
            'form_data.detail_po_vendor' => 'Detail PO dan Vendor',
            'form_data.documents_processed' => 'Dokumen yang Diproses',
            'form_data.project_count' => 'Jumlah Project',
            'form_data.projects' => 'Daftar Project',
            'form_data.projects.*.project_description' => 'Nama / Penjelasan Project',
            'form_data.projects.*.progress_percent' => 'Progres Project (%)',
            'form_data.today_activities' => 'Pekerjaan Hari Ini',
            'form_data.tomorrow_activities' => 'Rencana Pekerjaan Besok',
            'form_data.jumlah_customer' => 'Jumlah Customer',
            'form_data.jumlah_quotation' => 'Jumlah Quotation',
            'form_data.jumlah_closing' => 'Jumlah Closing',
            'form_data.invoice_count' => 'Jumlah Invoice',
            'form_data.invoice_details' => 'Daftar Detail Invoice',
            'form_data.invoice_details.*.description' => 'Detail Invoice',
            'form_data.jurnal' => 'Jurnal',
            'form_data.work_items' => 'Kelompok Pekerjaan',
            'form_data.work_items.*.detail' => 'Detail Pekerjaan',
            'form_data.work_items.*.status' => 'Status Pekerjaan',
            'form_data.pekerjaan_hari_ini' => 'Pekerjaan Hari Ini',
            'form_data.status_pengerjaan' => 'Status Pengerjaan',
            'form_data.kendala' => 'Kendala',
            'form_data.rencana_besok' => 'Rencana Besok',
        ];
    }
}
