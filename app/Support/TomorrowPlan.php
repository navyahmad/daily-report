<?php

namespace App\Support;

use Illuminate\Validation\Rule;

class TomorrowPlan
{
    /**
     * Opsi "Rencana Pekerjaan Besok" per kode divisi.
     * Admin Sales memakai opsi dan validasinya sendiri.
     *
     * @var array<string, array<string, string>>
     */
    public const OPTIONS = [
        'teknisi' => [
            'instalasi' => 'Instalasi',
            'maintenance' => 'Maintenance',
            'troubleshooting' => 'Troubleshooting',
            'survey' => 'Survey',
            'remote_support' => 'Remote Support',
            'lainnya' => 'Yang lain',
        ],
        'admin_project' => [
            'sow' => 'SOW',
            'bast' => 'BAST',
            'report' => 'Report',
            'lainnya' => 'Yang lain',
        ],
        'admin_procurement' => [
            'cari_barang' => 'Cari Barang',
            'cari_teknisi' => 'Cari Teknisi',
            'po' => 'PO',
            'lainnya' => 'Yang lain',
        ],
        'finance' => [
            'invoice' => 'Pembuatan Invoice',
            'jurnal' => 'Jurnal',
            'rekap_kas_bank' => 'Rekap Kas / Bank',
            'lainnya' => 'Yang lain',
        ],
        'system_informasi' => [
            'seo_organik' => 'Optimasi SEO Organik',
            'google_ads' => 'Google Ads',
            'social_media' => 'Social Media',
            'maintenance_website' => 'Maintenance Website',
            'support_it' => 'IT Support',
            'lainnya' => 'Yang lain',
        ],
    ];

    /**
     * @return array<string, string>
     */
    public static function options(string $code): array
    {
        return self::OPTIONS[$code] ?? [];
    }

    /**
     * Aturan validasi rencana besok. Detail wajib diisi untuk setiap opsi yang dipilih.
     *
     * @param  array<int, mixed>  $selected
     * @return array<string, array<int, mixed>>
     */
    public static function rules(string $code, array $selected, bool $required = true): array
    {
        $values = array_keys(self::options($code));

        $rules = [
            'form_data.tomorrow_activities' => [$required ? 'required' : 'nullable', 'array', 'min:1'],
            'form_data.tomorrow_activities.*' => ['string', 'distinct', Rule::in($values)],
            'form_data.tomorrow_activity_details' => ['nullable', 'array'],
            'form_data.tomorrow_activity_details.*' => ['nullable', 'string'],
            'form_data.rencana_besok' => ['nullable', 'string'],
        ];

        foreach ($values as $value) {
            if (in_array($value, $selected, true)) {
                $rules["form_data.tomorrow_activity_details.{$value}"] = ['required', 'string'];
            }
        }

        return $rules;
    }

    /**
     * Ringkasan satu baris, misalnya untuk export CSV. Laporan lama memakai teks rencana_besok.
     *
     * @param  array<string, mixed>  $data
     */
    public static function summary(string $code, array $data): string
    {
        if (empty($data['tomorrow_activities'])) {
            return (string) ($data['rencana_besok'] ?? '');
        }

        $options = self::options($code);

        return collect((array) $data['tomorrow_activities'])
            ->map(fn ($activity) => ($options[$activity] ?? ucfirst(str_replace('_', ' ', $activity))).': '.($data['tomorrow_activity_details'][$activity] ?? '-'))
            ->implode('; ');
    }
}
