<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrdReport extends Model
{
    /**
     * Kategori monitoring karyawan.
     *
     * @var array<string, string>
     */
    public const EMPLOYEE_CATEGORIES = [
        'kedisiplinan' => 'Kedisiplinan Karyawan',
        'sikap_perilaku' => 'Sikap dan Perilaku',
        'aktivitas_tanggung_jawab' => 'Aktivitas dan Tanggung Jawab Pekerjaan',
        'komunikasi_koordinasi' => 'Komunikasi dan Koordinasi',
        'kepatuhan_aturan' => 'Kepatuhan terhadap Aturan Perusahaan',
        'kerja_sama' => 'Kerja Sama Antarkaryawan',
        'kendala_permasalahan' => 'Kendala / Permasalahan Karyawan',
        'hal_positif' => 'Hal Positif / Apresiasi Karyawan',
        'temuan_lainnya' => 'Temuan Lainnya',
    ];

    /**
     * Status tindak lanjut HRD untuk monitoring karyawan.
     *
     * @var array<string, string>
     */
    public const FOLLOW_UP_STATUSES = [
        'selesai' => 'Selesai',
        'dalam_proses' => 'Dalam proses',
        'menunggu_pihak_terkait' => 'Menunggu pihak terkait',
        'perlu_monitoring_lanjutan' => 'Perlu monitoring lanjutan',
        'keputusan_direktur' => 'Memerlukan keputusan Direktur',
    ];

    /**
     * Kelas badge untuk setiap status tindak lanjut.
     *
     * @var array<string, string>
     */
    public const FOLLOW_UP_BADGES = [
        'selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'dalam_proses' => 'bg-sky-50 text-sky-700 border-sky-200',
        'menunggu_pihak_terkait' => 'bg-amber-50 text-amber-700 border-amber-200',
        'perlu_monitoring_lanjutan' => 'bg-purple-50 text-purple-700 border-purple-200',
        'keputusan_direktur' => 'bg-rose-50 text-rose-700 border-rose-200',
    ];

    /**
     * Kategori monitoring kondisi kantor.
     *
     * @var array<string, string>
     */
    public const OFFICE_CATEGORIES = [
        'kebersihan_kerapian' => 'Kebersihan dan Kerapian',
        'fasilitas_peralatan' => 'Fasilitas dan Peralatan',
        'keamanan_keselamatan' => 'Keamanan, Kenyamanan / Keselamatan Kerja',
    ];

    /**
     * Tindakan HRD untuk temuan kondisi kantor.
     *
     * @var array<string, string>
     */
    public const OFFICE_ACTIONS = [
        'tidak_perlu_tindakan' => 'Tidak perlu tindakan',
        'mengingatkan_karyawan' => 'Mengingatkan karyawan',
        'koordinasi_atasan' => 'Koordinasi dengan atasan',
        'melaporkan_kerusakan' => 'Melaporkan kerusakan',
        'mengajukan_perbaikan' => 'Mengajukan perbaikan',
    ];

    protected $fillable = [
        'report_date',
        'created_by',
        'include_employee',
        'include_office',
        'employee_entries',
        'office_findings',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'include_employee' => 'boolean',
            'include_office' => 'boolean',
            'employee_entries' => 'array',
            'office_findings' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(AdminHrdUser::class, 'created_by');
    }

    /**
     * Jenis laporan: employee, office, atau both.
     */
    public function type(): string
    {
        return match (true) {
            $this->include_employee && $this->include_office => 'both',
            $this->include_office => 'office',
            default => 'employee',
        };
    }

    public function typeLabel(): string
    {
        return match ($this->type()) {
            'both' => 'Karyawan & Kantor',
            'office' => 'Kondisi Kantor',
            default => 'Monitoring Karyawan',
        };
    }

    public function typeBadge(): string
    {
        return match ($this->type()) {
            'both' => 'bg-purple-50 text-purple-700 border-purple-200',
            'office' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            default => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        };
    }

    /**
     * @return array<int, string>
     */
    public function employeeNames(): array
    {
        if (! $this->include_employee) {
            return [];
        }

        return collect($this->employee_entries ?? [])->pluck('employee_name')->all();
    }

    /**
     * Subjek laporan dalam satu kalimat, misalnya "Bambang, Sri & Kondisi Kantor".
     */
    public function subjectLabel(): string
    {
        $names = implode(', ', $this->employeeNames());

        return match ($this->type()) {
            'both' => $names.' & Kondisi Kantor',
            'office' => 'Kondisi Kantor',
            default => $names,
        };
    }
}
