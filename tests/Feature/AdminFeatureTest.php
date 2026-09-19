<?php

namespace Tests\Feature;

use App\Models\AdminHrdUser;
use App\Models\DailyReport;
use App\Models\Division;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminFeatureTest extends TestCase
{
    use RefreshDatabase;

    private AdminHrdUser $admin;

    private Division $teknisiDiv;

    private Employee $emp1;

    private Employee $emp2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = AdminHrdUser::create([
            'name' => 'Admin Test',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->teknisiDiv = Division::create(['name' => 'Teknisi', 'code' => 'teknisi']);

        $this->emp1 = Employee::create([
            'division_id' => $this->teknisiDiv->id,
            'name' => 'Budi Teknisi',
            'is_active' => true,
        ]);

        $this->emp2 = Employee::create([
            'division_id' => $this->teknisiDiv->id,
            'name' => 'Andi Teknisi',
            'is_active' => true,
        ]);
    }

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_dashboard_with_report_metrics(): void
    {
        // emp1 submits report
        DailyReport::create([
            'employee_id' => $this->emp1->id,
            'division_id' => $this->teknisiDiv->id,
            'report_date' => '2026-09-17',
            'email' => 'budi@kantor.com',
            'employee_name_snapshot' => $this->emp1->name,
            'division_name_snapshot' => $this->teknisiDiv->name,
            'division_code_snapshot' => $this->teknisiDiv->code,
            'form_version' => 1,
            'status' => 'active',
            'form_data' => ['ringkasan_pekerjaan' => 'Kerja hari ini'],
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($this->admin, 'admin_hrd')->get(route('admin.dashboard', ['date' => '2026-09-17']));

        $response->assertStatus(200);
        $response->assertSee('Dashboard Monitoring Daily Report');
        $response->assertSee('Total Karyawan Aktif');
        $response->assertSee('Laporan Tanggal Ini');
        $response->assertSee('Budi Teknisi');
        $response->assertSee('Teknisi');
    }

    public function test_admin_can_create_and_toggle_employee(): void
    {
        $response = $this->actingAs($this->admin, 'admin_hrd')->post(route('admin.employees.store'), [
            'name' => 'Karyawan Baru',
            'division_id' => $this->teknisiDiv->id,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.employees.index'));
        $this->assertDatabaseHas('employees', [
            'name' => 'Karyawan Baru',
            'is_active' => true,
        ]);

        $employee = Employee::where('name', 'Karyawan Baru')->first();

        // Toggle status to inactive
        $this->actingAs($this->admin, 'admin_hrd')->patch(route('admin.employees.toggle-status', $employee));

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_cancel_and_restore_report(): void
    {
        $report = DailyReport::create([
            'employee_id' => $this->emp1->id,
            'division_id' => $this->teknisiDiv->id,
            'report_date' => '2026-09-17',
            'email' => 'budi@kantor.com',
            'employee_name_snapshot' => $this->emp1->name,
            'division_name_snapshot' => $this->teknisiDiv->name,
            'division_code_snapshot' => $this->teknisiDiv->code,
            'form_version' => 1,
            'status' => 'active',
            'form_data' => ['ringkasan_pekerjaan' => 'Kerja Teknisi'],
            'submitted_at' => now(),
        ]);

        // Cancel report
        $this->actingAs($this->admin, 'admin_hrd')->patch(route('admin.reports.cancel', $report));
        $this->assertEquals('cancelled', $report->fresh()->status);

        // Restore report
        $this->actingAs($this->admin, 'admin_hrd')->patch(route('admin.reports.restore', $report));
        $this->assertEquals('active', $report->fresh()->status);
    }

    public function test_admin_can_export_reports_to_csv(): void
    {
        DailyReport::create([
            'employee_id' => $this->emp1->id,
            'division_id' => $this->teknisiDiv->id,
            'report_date' => '2026-09-17',
            'email' => 'budi@kantor.com',
            'employee_name_snapshot' => $this->emp1->name,
            'division_name_snapshot' => $this->teknisiDiv->name,
            'division_code_snapshot' => $this->teknisiDiv->code,
            'form_version' => 1,
            'status' => 'active',
            'form_data' => ['ringkasan_pekerjaan' => 'Kerja Teknisi'],
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($this->admin, 'admin_hrd')->get(route('admin.reports.export', [
            'date' => '2026-09-17',
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
