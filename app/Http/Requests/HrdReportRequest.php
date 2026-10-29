<?php

namespace App\Http\Requests;

use App\Models\HrdReport;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class HrdReportRequest extends FormRequest
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
        $rules = [
            'report_date' => ['required', 'date'],
            'include_employee' => ['required', 'boolean'],
            'include_office' => ['required', 'boolean'],
        ];

        if ($this->boolean('include_employee')) {
            $rules['employees'] = ['required', 'array', 'min:1'];
            $rules['employees.*.mode'] = ['required', 'in:master,manual'];
            $rules['employees.*.employee_id'] = ['distinct', 'exists:employees,id'];
            $rules['employees.*.categories'] = ['required', 'array', 'min:1'];

            foreach ((array) $this->input('employees', []) as $index => $entry) {
                $entry = (array) $entry;
                // Duplikat kategori dicek per karyawan; dua karyawan boleh punya kategori yang sama.
                $rules["employees.{$index}.categories.*"] = ['distinct', Rule::in(array_keys(HrdReport::EMPLOYEE_CATEGORIES))];

                if (($entry['mode'] ?? null) === 'manual') {
                    $rules["employees.{$index}.manual_name"] = ['required', 'string', 'max:255'];
                } else {
                    $rules["employees.{$index}.employee_id"] = ['required', 'distinct', 'exists:employees,id'];
                }

                foreach ((array) ($entry['categories'] ?? []) as $category) {
                    if (is_string($category) && array_key_exists($category, HrdReport::EMPLOYEE_CATEGORIES)) {
                        $rules["employees.{$index}.details.{$category}.status"] = ['required', Rule::in(array_keys(HrdReport::FOLLOW_UP_STATUSES))];
                        $rules["employees.{$index}.details.{$category}.note"] = ['required', 'string'];
                    }
                }
            }
        }

        if ($this->boolean('include_office')) {
            $rules['office_categories'] = ['required', 'array', 'min:1'];
            $rules['office_categories.*'] = ['distinct', Rule::in(array_keys(HrdReport::OFFICE_CATEGORIES))];

            foreach ((array) $this->input('office_categories', []) as $category) {
                if (is_string($category) && array_key_exists($category, HrdReport::OFFICE_CATEGORIES)) {
                    $rules["office.{$category}.finding"] = ['required', 'string'];
                    $rules["office.{$category}.action"] = ['required', Rule::in(array_keys(HrdReport::OFFICE_ACTIONS))];
                    $rules["office.{$category}.action_note"] = ['required', 'string'];
                }
            }
        }

        return $rules;
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->boolean('include_employee') && ! $this->boolean('include_office')) {
                $validator->errors()->add('sections', 'Sertakan minimal satu jenis monitoring: Monitoring Karyawan atau Monitoring Kondisi Kantor.');
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'employees.*.employee_id.required' => 'Pilih karyawan dari daftar.',
            'employees.*.employee_id.distinct' => 'Karyawan ini sudah dimasukkan di entri lain.',
            'employees.*.manual_name.required' => 'Nama karyawan wajib diisi.',
            'employees.*.categories.required' => 'Pilih minimal satu kategori monitoring.',
            'employees.*.details.*.status.required' => 'Pilih status tindak lanjut.',
            'employees.*.details.*.note.required' => 'Detail catatan wajib diisi.',
            'office_categories.required' => 'Pilih minimal satu kategori kondisi kantor.',
            'office.*.finding.required' => 'Detail temuan wajib diisi.',
            'office.*.action.required' => 'Pilih tindakan HRD.',
            'office.*.action_note.required' => 'Detail catatan tindakan wajib diisi.',
        ];
    }

    /**
     * Custom attribute names for validation errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'report_date' => 'Tanggal Laporan',
            'employees' => 'Daftar Karyawan',
            'employees.*.employee_id' => 'Karyawan',
            'employees.*.manual_name' => 'Nama Karyawan',
            'employees.*.categories' => 'Kategori Monitoring',
            'employees.*.details.*.status' => 'Status Tindak Lanjut',
            'office_categories' => 'Kategori Kondisi Kantor',
            'office.*.action' => 'Tindakan HRD',
        ];
    }
}
