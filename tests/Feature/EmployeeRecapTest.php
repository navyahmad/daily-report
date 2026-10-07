<?php

namespace Tests\Feature;

use App\Models\AdminHrdUser;
use App\Models\DailyReport;
use App\Models\Division;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class EmployeeRecapTest extends TestCase
{
    use RefreshDatabase;

    private Division $division;

    private Employee $employee;

    private Employee $otherEmployee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-10-08 10:00:00'));
        $this->division = Division::create(['name' => 'Daily Report Teknisi', 'code' => 'teknisi']);
        $this->employee = Employee::create(['division_id' => $this->division->id, 'name' => 'Ammar', 'is_active' => true]);
        $this->otherEmployee = Employee::create(['division_id' => $this->division->id, 'name' => 'Arry', 'is_active' => true]);
    }

    public function test_guest_cannot_open_employee_recap(): void
    {
        $this->get(route('admin.employees.show', $this->employee))->assertRedirect(route('admin.login'));
    }

    public function test_employee_list_links_to_recap(): void
    {
        $this->actingAs($this->user('admin'), 'admin_hrd')
            ->get(route('admin.employees.index'))
            ->assertOk()
            ->assertSee(route('admin.employees.show', $this->employee), false);
    }

    #[TestWith(['admin'])]
    #[TestWith(['hrd'])]
    public function test_recap_shows_only_this_employees_reports_for_current_month(string $role): void
    {
        $thisMonth = $this->report($this->employee, '2026-10-05');
        $cancelled = $this->report($this->employee, '2026-10-06', 'cancelled');
        $lastMonth = $this->report($this->employee, '2026-09-30');
        $other = $this->report($this->otherEmployee, '2026-10-07');

        $response = $this->actingAs($this->user($role), 'admin_hrd')->get(route('admin.employees.show', $this->employee));

        $response->assertOk()
            ->assertSee('Rekap Laporan: Ammar')
            ->assertViewHas('startDate', '2026-10-01')
            ->assertViewHas('endDate', '2026-10-31')
            ->assertViewHas('periodActiveCount', 1)
            ->assertViewHas('periodCancelledCount', 1)
            ->assertViewHas('totalActiveCount', 2)
            ->assertViewHas('lastReport', fn ($report) => $report->is($thisMonth));
        $response->assertSee(route('admin.reports.show', $thisMonth), false);
        $response->assertSee(route('admin.reports.show', $cancelled), false);
        $response->assertDontSee(route('admin.reports.show', $lastMonth), false);
        $response->assertDontSee(route('admin.reports.show', $other), false);
    }

    public function test_recap_can_be_filtered_by_date_range(): void
    {
        $september = $this->report($this->employee, '2026-09-30');
        $october = $this->report($this->employee, '2026-10-05');

        $response = $this->actingAs($this->user('admin'), 'admin_hrd')
            ->get(route('admin.employees.show', [$this->employee, 'start_date' => '2026-09-01', 'end_date' => '2026-09-30']));

        $response->assertOk()->assertViewHas('periodActiveCount', 1);
        $response->assertSee(route('admin.reports.show', $september), false);
        $response->assertDontSee(route('admin.reports.show', $october), false);
    }

    public function test_recap_rejects_end_date_before_start_date(): void
    {
        $this->actingAs($this->user('admin'), 'admin_hrd')
            ->get(route('admin.employees.show', [$this->employee, 'start_date' => '2026-10-05', 'end_date' => '2026-10-01']))
            ->assertSessionHasErrors('end_date');
    }

    public function test_recap_of_employee_without_reports_is_empty(): void
    {
        $this->actingAs($this->user('admin'), 'admin_hrd')
            ->get(route('admin.employees.show', $this->employee))
            ->assertOk()
            ->assertSee('Belum pernah mengirim laporan')
            ->assertSee('Belum ada laporan dari karyawan ini pada periode yang dipilih.');
    }

    public function test_export_can_be_limited_to_one_employee(): void
    {
        $this->report($this->employee, '2026-10-05');
        $this->report($this->otherEmployee, '2026-10-05');

        $csv = $this->actingAs($this->user('admin'), 'admin_hrd')
            ->get(route('admin.reports.export', ['employee_id' => $this->employee->id, 'start_date' => '2026-10-01', 'end_date' => '2026-10-31']))
            ->streamedContent();

        $this->assertStringContainsString('Ammar', $csv);
        $this->assertStringNotContainsString('Arry', $csv);
    }

    private function report(Employee $employee, string $date, string $status = 'active'): DailyReport
    {
        return DailyReport::create([
            'employee_id' => $employee->id,
            'division_id' => $this->division->id,
            'report_date' => $date,
            'email' => strtolower($employee->name).'@kantor.com',
            'employee_name_snapshot' => $employee->name,
            'division_name_snapshot' => $this->division->name,
            'division_code_snapshot' => $this->division->code,
            'form_version' => 2,
            'status' => $status,
            'form_data' => [
                'work_items' => [['type' => 'survey', 'custom_type' => '', 'detail' => 'Survey lokasi', 'status' => 'selesai']],
                'kendala' => null,
                'tomorrow_activities' => ['survey'],
                'tomorrow_activity_details' => ['survey' => 'Survey lanjutan'],
            ],
            'submitted_at' => now(),
        ]);
    }

    private function user(string $role): AdminHrdUser
    {
        return AdminHrdUser::create([
            'name' => 'User '.$role,
            'username' => 'user-'.$role,
            'password' => 'password',
            'role' => $role,
            'is_active' => true,
        ]);
    }
}
