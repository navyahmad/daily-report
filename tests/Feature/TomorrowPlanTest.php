<?php

namespace Tests\Feature;

use App\Models\AdminHrdUser;
use App\Models\DailyReport;
use App\Models\Division;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TomorrowPlanTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function divisions(): array
    {
        return [
            'teknisi' => ['teknisi', 'survey', 'Survey'],
            'admin project' => ['admin_project', 'bast', 'BAST'],
            'finance' => ['finance', 'rekap_kas_bank', 'Rekap Kas / Bank'],
            'system informasi' => ['system_informasi', 'google_ads', 'Google Ads'],
        ];
    }

    #[DataProvider('divisions')]
    public function test_employee_can_submit_division_specific_tomorrow_plan(string $code, string $option, string $label): void
    {
        $payload = $this->payload($code, $option);

        $response = $this->post(route('report.store'), $payload);

        $response->assertRedirect(route('report.success'));
        $formData = DailyReport::sole()->form_data;
        $this->assertSame([$option], $formData['tomorrow_activities']);
        $this->assertSame('Rencana besok '.$code, $formData['tomorrow_activity_details'][$option]);
    }

    #[DataProvider('divisions')]
    public function test_tomorrow_plan_is_required(string $code, string $option, string $label): void
    {
        $payload = $this->payload($code, $option);
        unset($payload['form_data']['tomorrow_activities'], $payload['form_data']['tomorrow_activity_details']);

        $response = $this->post(route('report.store'), $payload);

        $response->assertSessionHasErrors('form_data.tomorrow_activities');
        $this->assertDatabaseCount('daily_reports', 0);
    }

    #[DataProvider('divisions')]
    public function test_selected_tomorrow_option_needs_detail(string $code, string $option, string $label): void
    {
        $payload = $this->payload($code, $option);
        $payload['form_data']['tomorrow_activity_details'][$option] = '';

        $response = $this->post(route('report.store'), $payload);

        $response->assertSessionHasErrors("form_data.tomorrow_activity_details.{$option}");
        $this->assertDatabaseCount('daily_reports', 0);
    }

    #[DataProvider('divisions')]
    public function test_option_from_another_division_is_rejected(string $code, string $option, string $label): void
    {
        $payload = $this->payload($code, $option);
        $payload['form_data']['tomorrow_activities'] = ['follow_up'];
        $payload['form_data']['tomorrow_activity_details'] = ['follow_up' => 'Opsi milik Admin Sales'];

        $response = $this->post(route('report.store'), $payload);

        $response->assertSessionHasErrors('form_data.tomorrow_activities.0');
        $this->assertDatabaseCount('daily_reports', 0);
    }

    #[DataProvider('divisions')]
    public function test_admin_sees_tomorrow_plan_in_detail_edit_and_export(string $code, string $option, string $label): void
    {
        $report = $this->report($this->payload($code, $option));
        $this->actingAs($this->admin(), 'admin_hrd');

        $this->get(route('admin.reports.show', $report))->assertOk()->assertSee($label)->assertSee('Rencana besok '.$code);
        $this->get(route('admin.reports.edit', $report))->assertOk()->assertSee($label)->assertSee('Rencana besok '.$code);

        $csv = $this->get(route('admin.reports.export'))->streamedContent();
        $this->assertStringContainsString("Rencana Besok: {$label}: Rencana besok {$code}", $csv);
    }

    #[DataProvider('divisions')]
    public function test_legacy_report_can_still_be_edited_without_choosing_plan(string $code, string $option, string $label): void
    {
        $payload = $this->payload($code, $option);
        unset($payload['form_data']['tomorrow_activities'], $payload['form_data']['tomorrow_activity_details']);
        $payload['form_data']['rencana_besok'] = 'Rencana lama '.$code;
        $report = $this->report($payload);
        $this->actingAs($this->admin(), 'admin_hrd');

        $this->get(route('admin.reports.show', $report))->assertOk()->assertSee('Rencana lama '.$code);
        $this->get(route('admin.reports.edit', $report))->assertOk()->assertSee('Rencana lama '.$code);
        $this->assertStringContainsString('Rencana Besok: Rencana lama '.$code, $this->get(route('admin.reports.export'))->streamedContent());

        $response = $this->put(route('admin.reports.update', $report), $payload + ['status' => 'active']);

        $response->assertRedirect(route('admin.reports.show', $report));
        $this->assertSame('Rencana lama '.$code, $report->fresh()->form_data['rencana_besok']);
    }

    #[DataProvider('divisions')]
    public function test_new_report_plan_cannot_be_removed_on_edit(string $code, string $option, string $label): void
    {
        $payload = $this->payload($code, $option);
        $report = $this->report($payload);
        unset($payload['form_data']['tomorrow_activities'], $payload['form_data']['tomorrow_activity_details']);

        $response = $this->actingAs($this->admin(), 'admin_hrd')
            ->put(route('admin.reports.update', $report), $payload + ['status' => 'active']);

        $response->assertSessionHasErrors('form_data.tomorrow_activities');
    }

    /**
     * @return array{division_id: int, employee_id: int, report_date: string, email: string, form_data: array<string, mixed>}
     */
    private function payload(string $code, string $option): array
    {
        $division = Division::create(['name' => 'Daily Report '.$code, 'code' => $code]);
        $employee = Employee::create(['division_id' => $division->id, 'name' => 'Karyawan '.$code, 'is_active' => true]);

        $formData = match ($code) {
            'teknisi' => [
                'work_items' => [['type' => 'instalasi', 'custom_type' => '', 'detail' => 'Instalasi access point', 'status' => 'selesai']],
            ],
            'admin_project' => [
                'documents_processed' => ['sow'],
                'document_details' => ['sow' => 'Draft SOW'],
                'project_count' => 0,
                'projects' => [],
            ],
            'finance' => [
                'pekerjaan_hari_ini' => 'Rekonsiliasi bank',
                'invoice_count' => 0,
                'invoice_details' => [],
                'jurnal' => 'Jurnal kas masuk',
                'rekap_kas_bank' => null,
            ],
            'system_informasi' => [
                'system_activities' => ['seo_organik'],
                'system_activity_details' => ['seo_organik' => 'Optimasi artikel'],
                'status_pengerjaan' => 'Selesai',
            ],
        };

        return [
            'division_id' => $division->id,
            'employee_id' => $employee->id,
            'report_date' => '2026-09-23',
            'email' => $code.'@example.com',
            'form_data' => $formData + [
                'kendala' => null,
                'tomorrow_activities' => [$option],
                'tomorrow_activity_details' => [$option => 'Rencana besok '.$code],
            ],
        ];
    }

    /**
     * @param  array{division_id: int, employee_id: int, report_date: string, email: string, form_data: array<string, mixed>}  $payload
     */
    private function report(array $payload): DailyReport
    {
        $division = Division::find($payload['division_id']);

        return DailyReport::create($payload + [
            'employee_name_snapshot' => 'Karyawan '.$division->code,
            'division_name_snapshot' => $division->name,
            'division_code_snapshot' => $division->code,
            'form_version' => 2,
            'status' => 'active',
            'submitted_at' => now(),
        ]);
    }

    private function admin(): AdminHrdUser
    {
        return AdminHrdUser::create([
            'name' => 'Admin Rencana Besok',
            'username' => 'rencana-admin',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }
}
