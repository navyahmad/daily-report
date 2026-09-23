<?php

namespace Tests\Feature;

use App\Models\AdminHrdUser;
use App\Models\DailyReport;
use App\Models\Division;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WibTimezoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_submitted_at_is_recorded_in_wib(): void
    {
        $this->travelTo(Carbon::parse('2026-09-23 09:25:00', 'UTC'));
        $division = Division::create(['name' => 'Finance', 'code' => 'finance']);
        $employee = Employee::create(['division_id' => $division->id, 'name' => 'Dewi Finance', 'is_active' => true]);

        $this->post(route('report.store'), [
            'division_id' => $division->id,
            'employee_id' => $employee->id,
            'report_date' => '2026-09-23',
            'email' => 'dewi@kantor.com',
            'form_data' => [
                'pekerjaan_hari_ini' => 'Rekonsiliasi bank',
                'invoice_count' => 0,
                'jurnal' => 'Jurnal kas masuk',
                'tomorrow_activities' => ['jurnal'],
                'tomorrow_activity_details' => ['jurnal' => 'Jurnal penyesuaian'],
            ],
        ])->assertRedirect(route('report.success'));

        $this->assertSame('Asia/Jakarta', config('app.timezone'));
        $this->assertSame('2026-09-23 16:25:00', DB::table('daily_reports')->value('submitted_at'));

        $admin = AdminHrdUser::create(['name' => 'Admin', 'username' => 'admin', 'password' => 'password', 'role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin, 'admin_hrd')
            ->get(route('admin.reports.show', DailyReport::sole()))
            ->assertSee('16:25:00, 23/09/2026');
    }

    public function test_dashboard_defaults_to_today_in_wib(): void
    {
        // 23 September 01:00 WIB masih 22 September di UTC.
        $this->travelTo(Carbon::parse('2026-09-22 18:00:00', 'UTC'));
        $admin = AdminHrdUser::create(['name' => 'Admin', 'username' => 'admin', 'password' => 'password', 'role' => 'admin', 'is_active' => true]);

        $response = $this->actingAs($admin, 'admin_hrd')->get(route('admin.dashboard'));

        $response->assertOk()->assertViewHas('selectedDate', '2026-09-23');
    }

    public function test_migration_shifts_existing_utc_timestamps_to_wib_and_back(): void
    {
        $division = Division::create(['name' => 'Finance', 'code' => 'finance']);
        $employee = Employee::create(['division_id' => $division->id, 'name' => 'Dewi Finance', 'is_active' => true]);
        $admin = AdminHrdUser::create(['name' => 'Admin', 'username' => 'admin', 'password' => 'password', 'role' => 'admin', 'is_active' => true]);
        $neverLoggedIn = AdminHrdUser::create(['name' => 'HRD', 'username' => 'hrd', 'password' => 'password', 'role' => 'hrd', 'is_active' => true]);
        $report = DailyReport::create([
            'employee_id' => $employee->id,
            'division_id' => $division->id,
            'report_date' => '2026-09-22',
            'email' => 'dewi@kantor.com',
            'employee_name_snapshot' => $employee->name,
            'division_name_snapshot' => $division->name,
            'division_code_snapshot' => $division->code,
            'form_data' => [],
            'submitted_at' => now(),
        ]);

        $utc = '2026-09-22 20:30:00';
        DB::table('daily_reports')->update(['submitted_at' => $utc, 'created_at' => $utc, 'updated_at' => $utc]);
        DB::table('employees')->update(['created_at' => $utc, 'updated_at' => $utc]);
        DB::table('divisions')->update(['created_at' => $utc, 'updated_at' => $utc]);
        DB::table('admin_hrd_users')->where('id', $admin->id)->update(['last_login_at' => $utc, 'created_at' => $utc, 'updated_at' => $utc]);

        $migration = require database_path('migrations/2026_09_23_000000_shift_existing_timestamps_to_wib.php');
        $migration->up();

        $wib = '2026-09-23 03:30:00';
        $this->assertSame($wib, DB::table('daily_reports')->where('id', $report->id)->value('submitted_at'));
        $this->assertSame($wib, DB::table('daily_reports')->where('id', $report->id)->value('updated_at'));
        $this->assertSame($wib, DB::table('employees')->where('id', $employee->id)->value('created_at'));
        $this->assertSame($wib, DB::table('divisions')->where('id', $division->id)->value('created_at'));
        $this->assertSame($wib, DB::table('admin_hrd_users')->where('id', $admin->id)->value('last_login_at'));
        $this->assertNull(DB::table('admin_hrd_users')->where('id', $neverLoggedIn->id)->value('last_login_at'));

        $migration->down();

        $this->assertSame($utc, DB::table('daily_reports')->where('id', $report->id)->value('submitted_at'));
        $this->assertSame($utc, DB::table('admin_hrd_users')->where('id', $admin->id)->value('last_login_at'));
    }
}
