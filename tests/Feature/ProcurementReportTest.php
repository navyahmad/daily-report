<?php

namespace Tests\Feature;

use App\Models\AdminHrdUser;
use App\Models\DailyReport;
use App\Models\Division;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class ProcurementReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_submit_other_work_and_multiple_procurement_plans(): void
    {
        $payload = $this->payload();

        $response = $this->post(route('report.store'), $payload);

        $response->assertRedirect(route('report.success'));
        $this->assertFormDataSame($payload['form_data'], DailyReport::sole()->form_data);
    }

    public function test_tomorrow_plan_is_required(): void
    {
        $payload = $this->payload();
        unset($payload['form_data']['tomorrow_activities'], $payload['form_data']['tomorrow_activity_details']);

        $response = $this->post(route('report.store'), $payload);

        $response->assertSessionHasErrors('form_data.tomorrow_activities');
        $this->assertDatabaseCount('daily_reports', 0);
    }

    #[DataProvider('invalidFormData')]
    public function test_submission_rejects_invalid_procurement_details(string $field, mixed $value, string $error): void
    {
        $payload = $this->payload();
        data_set($payload, 'form_data.'.$field, $value);

        $response = $this->post(route('report.store'), $payload);

        $response->assertSessionHasErrors('form_data.'.$error);
        $this->assertDatabaseCount('daily_reports', 0);
    }

    #[DataProvider('invalidFormData')]
    public function test_edit_rejects_invalid_procurement_details(string $field, mixed $value, string $error): void
    {
        $payload = $this->payload();
        $report = $this->report($payload);
        data_set($payload, 'form_data.'.$field, $value);

        $response = $this->actingAs($this->admin(), 'admin_hrd')
            ->put(route('admin.reports.update', $report), $payload + ['status' => 'active']);

        $response->assertSessionHasErrors('form_data.'.$error);
        $this->assertSame($report->form_data, $report->fresh()->form_data);
    }

    /**
     * @return array<string, array{string, mixed, string}>
     */
    public static function invalidFormData(): array
    {
        return [
            'other work needs detail' => ['detail_lainnya', '', 'detail_lainnya'],
            'goods plan needs detail' => ['tomorrow_activity_details.cari_barang', '', 'tomorrow_activity_details.cari_barang'],
            'technician plan needs detail' => ['tomorrow_activity_details.cari_teknisi', '', 'tomorrow_activity_details.cari_teknisi'],
            'purchase order plan needs detail' => ['tomorrow_activity_details.po', '', 'tomorrow_activity_details.po'],
            'other plan needs detail' => ['tomorrow_activity_details.lainnya', '', 'tomorrow_activity_details.lainnya'],
            'sales activity is not procurement work' => ['work_categories', ['follow_up'], 'work_categories.0'],
            'sales activity is not procurement plan' => ['tomorrow_activities', ['meeting'], 'tomorrow_activities.0'],
            'plan needs at least one option' => ['tomorrow_activities', [], 'tomorrow_activities'],
            'plan must be an array' => ['tomorrow_activities', 'po', 'tomorrow_activities'],
        ];
    }

    public function test_admin_can_edit_new_work_and_tomorrow_details(): void
    {
        $payload = $this->payload();
        $report = $this->report($payload);
        $payload['form_data']['detail_lainnya'] = 'Koreksi pekerjaan pengadaan';
        $payload['form_data']['tomorrow_activity_details']['po'] = 'Siapkan PO tambahan';

        $response = $this->actingAs($this->admin(), 'admin_hrd')
            ->put(route('admin.reports.update', $report), $payload + ['status' => 'active']);

        $response->assertRedirect(route('admin.reports.show', $report));
        $this->assertFormDataSame($payload['form_data'], $report->fresh()->form_data);
    }

    #[TestWith(['admin.reports.show'])]
    #[TestWith(['admin.reports.edit'])]
    public function test_new_report_details_are_visible_to_admin(string $route): void
    {
        $report = $this->report($this->payload());

        $response = $this->actingAs($this->admin(), 'admin_hrd')->get(route($route, $report));

        $response->assertOk()->assertSee('Yang lain');
        $response->assertSee('Evaluasi vendor cadangan');
        $response->assertSee('Cari kabel jaringan besok');
        $response->assertSee('Cari teknisi Surabaya besok');
        $response->assertSee('Siapkan PO router besok');
        $response->assertSee('Rapat pengadaan besok');
    }

    public function test_export_includes_other_work_and_all_selected_plans(): void
    {
        $this->report($this->payload());

        $response = $this->actingAs($this->admin(), 'admin_hrd')->get(route('admin.reports.export'));

        $response->assertOk();
        $csv = $response->streamedContent();
        $this->assertStringContainsString('Yang lain: Evaluasi vendor cadangan', $csv);
        $this->assertStringContainsString('Rencana Besok: Cari Barang: Cari kabel jaringan besok; Cari Teknisi: Cari teknisi Surabaya besok; PO: Siapkan PO router besok; Yang lain: Rapat pengadaan besok', $csv);
    }

    #[TestWith([1])]
    #[TestWith([2])]
    public function test_legacy_report_plan_is_visible_and_editable(int $version): void
    {
        $report = $this->legacyReport($version);
        $this->actingAs($this->admin(), 'admin_hrd');

        $this->get(route('admin.reports.show', $report))->assertOk()->assertSee('Rencana lama tetap tersimpan');
        $this->get(route('admin.reports.edit', $report))->assertOk()->assertSee('Rencana lama tetap tersimpan');
    }

    #[TestWith([1])]
    #[TestWith([2])]
    public function test_legacy_plan_is_preserved_when_admin_updates_report(int $version): void
    {
        $report = $this->legacyReport($version);
        $formData = [
            'work_categories' => ['cari_barang'],
            'detail_cari_barang' => 'Mencari router',
            'rencana_besok' => 'Rencana lama tetap tersimpan',
        ];

        $response = $this->actingAs($this->admin(), 'admin_hrd')->put(route('admin.reports.update', $report), [
            'division_id' => $report->division_id,
            'employee_id' => $report->employee_id,
            'report_date' => '2026-09-23',
            'email' => $report->email,
            'status' => 'active',
            'form_data' => $formData,
        ]);

        $response->assertRedirect(route('admin.reports.show', $report));
        $this->assertFormDataSame($formData, $report->fresh()->form_data);
        $this->assertSame(2, $report->fresh()->form_version);
    }

    #[TestWith([1])]
    #[TestWith([2])]
    public function test_legacy_plan_remains_in_csv_export(int $version): void
    {
        $this->legacyReport($version);

        $response = $this->actingAs($this->admin(), 'admin_hrd')->get(route('admin.reports.export'));

        $response->assertOk();
        $this->assertStringContainsString('Rencana lama tetap tersimpan', $response->streamedContent());
    }

    public function test_editor_does_not_restore_unchecked_saved_options_after_validation_failure(): void
    {
        $report = $this->report($this->payload());

        $response = $this->actingAs($this->admin(), 'admin_hrd')
            ->withSession(['_old_input' => ['form_data' => ['detail_lainnya' => 'Isian gagal validasi']]])
            ->get(route('admin.reports.edit', $report));

        $response->assertOk()->assertSee('categories: []', false)->assertSee('tomorrowActivities: []', false);
        $response->assertSee('Isian gagal validasi');
    }

    /**
     * @return array{division_id: int, employee_id: int, report_date: string, email: string, form_data: array<string, mixed>}
     */
    private function payload(): array
    {
        $division = Division::create(['name' => 'Admin Procurement', 'code' => 'admin_procurement']);
        $employee = Employee::create(['division_id' => $division->id, 'name' => 'Staf Procurement', 'is_active' => true]);

        return [
            'division_id' => $division->id,
            'employee_id' => $employee->id,
            'report_date' => '2026-09-23',
            'email' => 'procurement@example.com',
            'form_data' => [
                'work_categories' => ['cari_barang', 'cari_teknisi', 'po', 'lainnya'],
                'detail_cari_barang' => 'Mencari router',
                'detail_cari_teknisi' => 'Mencari teknisi Jakarta',
                'jumlah_po' => 2,
                'detail_po_vendor' => 'PO router vendor utama',
                'detail_lainnya' => 'Evaluasi vendor cadangan',
                'tomorrow_activities' => ['cari_barang', 'cari_teknisi', 'po', 'lainnya'],
                'tomorrow_activity_details' => [
                    'cari_barang' => 'Cari kabel jaringan besok',
                    'cari_teknisi' => 'Cari teknisi Surabaya besok',
                    'po' => 'Siapkan PO router besok',
                    'lainnya' => 'Rapat pengadaan besok',
                ],
            ],
        ];
    }

    /**
     * @param  array{division_id: int, employee_id: int, report_date: string, email: string, form_data: array<string, mixed>}  $payload
     */
    private function report(array $payload, int $version = 2): DailyReport
    {
        return DailyReport::create($payload + [
            'employee_name_snapshot' => 'Staf Procurement',
            'division_name_snapshot' => 'Admin Procurement',
            'division_code_snapshot' => 'admin_procurement',
            'form_version' => $version,
            'status' => 'active',
            'submitted_at' => now(),
        ]);
    }

    private function legacyReport(int $version): DailyReport
    {
        $payload = $this->payload();
        $payload['form_data'] = [
            'pekerjaan_hari_ini' => 'Mencari router',
            'jumlah_po' => 0,
            'vendor_dihubungi' => 'Vendor utama',
            'rencana_besok' => 'Rencana lama tetap tersimpan',
        ];
        if ($version === 2) {
            $payload['form_data']['work_categories'] = ['cari_barang'];
            $payload['form_data']['detail_cari_barang'] = 'Mencari router';
        }

        return $this->report($payload, $version);
    }

    /**
     * @param  array<string, mixed>  $expected
     * @param  array<string, mixed>  $actual
     */
    private function assertFormDataSame(array $expected, array $actual): void
    {
        ksort($expected);
        ksort($actual);

        $this->assertSame($expected, $actual);
    }

    private function admin(): AdminHrdUser
    {
        return AdminHrdUser::create([
            'name' => 'Admin Procurement Test',
            'username' => 'procurement-admin',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }
}
