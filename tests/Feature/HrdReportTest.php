<?php

namespace Tests\Feature;

use App\Models\AdminHrdUser;
use App\Models\Division;
use App\Models\Employee;
use App\Models\HrdReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HrdReportTest extends TestCase
{
    use RefreshDatabase;

    private AdminHrdUser $hrd;

    private Employee $bambang;

    private Employee $sri;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-10-29 10:30:00'));
        $division = Division::create(['name' => 'Daily Report Teknisi', 'code' => 'teknisi']);
        $this->bambang = Employee::create(['division_id' => $division->id, 'name' => 'Bambang Pamungkas', 'is_active' => true]);
        $this->sri = Employee::create(['division_id' => $division->id, 'name' => 'Sri Handayani', 'is_active' => true]);
        $this->hrd = $this->user('hrd', 'Tim HRD Kantor');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.hrd-reports.index'))->assertRedirect(route('admin.login'));
    }

    public function test_admin_role_cannot_access_hrd_reports(): void
    {
        $admin = $this->user('admin', 'Manager');
        $report = HrdReport::create(['report_date' => '2026-10-29', 'include_office' => true, 'office_findings' => []]);
        $this->actingAs($admin, 'admin_hrd');

        $this->get(route('admin.hrd-reports.index'))->assertForbidden();
        $this->get(route('admin.hrd-reports.create'))->assertForbidden();
        $this->post(route('admin.hrd-reports.store'), $this->payload())->assertForbidden();
        $this->get(route('admin.hrd-reports.show', $report))->assertForbidden();
        $this->delete(route('admin.hrd-reports.destroy', $report))->assertForbidden();
        $this->get(route('admin.hrd-reports.export'))->assertForbidden();
        $this->get(route('admin.dashboard'))->assertOk()->assertDontSee('Daily Report HRD');
        $this->assertDatabaseCount('hrd_reports', 1);
    }

    public function test_hrd_sees_menu_and_create_form_with_active_employees_only(): void
    {
        $inactive = Employee::create(['division_id' => $this->bambang->division_id, 'name' => 'Karyawan Nonaktif', 'is_active' => false]);
        $this->actingAs($this->hrd, 'admin_hrd');

        $this->get(route('admin.dashboard'))->assertOk()->assertSee(route('admin.hrd-reports.index'), false);
        $this->get(route('admin.hrd-reports.create'))
            ->assertOk()
            ->assertSee('Monitoring Karyawan')
            ->assertSee('Monitoring Kondisi Kantor')
            ->assertSee('Bambang Pamungkas — Daily Report Teknisi')
            ->assertDontSee($inactive->name);
    }

    public function test_hrd_can_store_employee_and_office_monitoring(): void
    {
        $response = $this->actingAs($this->hrd, 'admin_hrd')->post(route('admin.hrd-reports.store'), $this->payload());

        $report = HrdReport::sole();
        $response->assertRedirect(route('admin.hrd-reports.show', $report));
        $this->assertSame($this->hrd->id, $report->created_by);
        $this->assertSame('both', $report->type());
        $this->assertSame([
            [
                'employee_id' => $this->bambang->id,
                'employee_name' => 'Bambang Pamungkas',
                'items' => [
                    ['category' => 'kedisiplinan', 'status' => 'menunggu_pihak_terkait', 'note' => 'Terlambat dua kali'],
                    ['category' => 'aktivitas_tanggung_jawab', 'status' => 'selesai', 'note' => 'Target tercapai'],
                ],
            ],
            [
                'employee_id' => null,
                'employee_name' => 'Karyawan Baru',
                'items' => [
                    ['category' => 'kedisiplinan', 'status' => 'perlu_monitoring_lanjutan', 'note' => 'Masa orientasi'],
                ],
            ],
        ], $report->employee_entries);
        $this->assertSame([
            ['category' => 'kebersihan_kerapian', 'finding' => 'Pantry kotor', 'action' => 'koordinasi_atasan', 'action_note' => 'Jadwal piket dibuat'],
        ], $report->office_findings);

        $this->get(route('admin.hrd-reports.show', $report))
            ->assertOk()
            ->assertSee('Laporan Daily Report HRD berhasil disimpan.')
            ->assertSee('Monitoring Karyawan &amp; Kondisi Kantor', false)
            ->assertSee('Bambang Pamungkas, Karyawan Baru &amp; Kondisi Kantor', false)
            ->assertSee('Total: 2 Karyawan')
            ->assertSee('Tindak Lanjut: Menunggu pihak terkait')
            ->assertSee('Total: 1 Temuan')
            ->assertSee('Tindakan: Koordinasi dengan atasan')
            ->assertSee('Jadwal piket dibuat')
            ->assertSee('10:30, 29/10/2026');
    }

    public function test_skipped_section_is_not_stored(): void
    {
        $payload = $this->payload();
        $payload['include_employee'] = 0;

        $this->actingAs($this->hrd, 'admin_hrd')->post(route('admin.hrd-reports.store'), $payload)->assertSessionHasNoErrors();

        $report = HrdReport::sole();
        $this->assertSame('office', $report->type());
        $this->assertNull($report->employee_entries);
        $this->assertSame([], $report->employeeNames());
        $this->assertSame('Kondisi Kantor', $report->subjectLabel());
    }

    /**
     * @return array<string, array{callable(array<string, mixed>): array<string, mixed>, string}>
     */
    public static function invalidPayloads(): array
    {
        return [
            'no section included' => [fn (array $p) => ['include_employee' => 0, 'include_office' => 0] + $p, 'sections'],
            'employee without category' => [fn (array $p) => data_set($p, 'employees.0.categories', []) ?? $p, 'employees.0.categories'],
            'category without status' => [fn (array $p) => data_set($p, 'employees.0.details.kedisiplinan.status', '') ?? $p, 'employees.0.details.kedisiplinan.status'],
            'category without note' => [fn (array $p) => data_set($p, 'employees.0.details.kedisiplinan.note', '') ?? $p, 'employees.0.details.kedisiplinan.note'],
            'unknown status' => [fn (array $p) => data_set($p, 'employees.0.details.kedisiplinan.status', 'dipecat') ?? $p, 'employees.0.details.kedisiplinan.status'],
            'master without employee' => [fn (array $p) => data_set($p, 'employees.0.employee_id', '') ?? $p, 'employees.0.employee_id'],
            'manual without name' => [fn (array $p) => data_set($p, 'employees.1.manual_name', '') ?? $p, 'employees.1.manual_name'],
            'office category without action' => [fn (array $p) => data_set($p, 'office.kebersihan_kerapian.action', '') ?? $p, 'office.kebersihan_kerapian.action'],
            'office category without finding' => [fn (array $p) => data_set($p, 'office.kebersihan_kerapian.finding', '') ?? $p, 'office.kebersihan_kerapian.finding'],
            'office section without category' => [fn (array $p) => data_set($p, 'office_categories', []) ?? $p, 'office_categories'],
        ];
    }

    /**
     * @param  callable(array<string, mixed>): array<string, mixed>  $mutate
     */
    #[DataProvider('invalidPayloads')]
    public function test_invalid_report_is_rejected(callable $mutate, string $errorKey): void
    {
        $payload = $this->payload();
        $payload = $mutate($payload) ?? $payload;

        $this->actingAs($this->hrd, 'admin_hrd')
            ->from(route('admin.hrd-reports.create'))
            ->post(route('admin.hrd-reports.store'), $payload)
            ->assertRedirect(route('admin.hrd-reports.create'))
            ->assertSessionHasErrors($errorKey);
        $this->assertDatabaseCount('hrd_reports', 0);
    }

    public function test_same_master_employee_cannot_be_entered_twice(): void
    {
        $payload = $this->payload();
        $payload['employees'][1] = $payload['employees'][0];

        $this->actingAs($this->hrd, 'admin_hrd')
            ->post(route('admin.hrd-reports.store'), $payload)
            ->assertSessionHasErrors('employees.1.employee_id');
    }

    public function test_form_keeps_input_after_validation_failure(): void
    {
        $payload = $this->payload();
        $payload['employees'][1]['manual_name'] = '';
        $this->actingAs($this->hrd, 'admin_hrd');

        $this->from(route('admin.hrd-reports.create'))->post(route('admin.hrd-reports.store'), $payload);

        $this->get(route('admin.hrd-reports.create'))
            ->assertOk()
            ->assertSee('Terlambat dua kali')
            ->assertSee('Pantry kotor')
            ->assertSee('Nama karyawan wajib diisi.');
    }

    public function test_index_lists_reports_with_subject_and_type(): void
    {
        $this->actingAs($this->hrd, 'admin_hrd')->post(route('admin.hrd-reports.store'), $this->payload());
        $officeOnly = $this->payload();
        $officeOnly['include_employee'] = 0;
        $officeOnly['report_date'] = '2026-10-28';
        $this->post(route('admin.hrd-reports.store'), $officeOnly);

        $this->get(route('admin.hrd-reports.index'))
            ->assertOk()
            ->assertSee('Karyawan &amp; Kantor', false)
            ->assertSee('Bambang Pamungkas, Karyawan Baru')
            ->assertSee('2 Karyawan')
            ->assertSee('+ Kondisi Kantor')
            ->assertSee('Monitoring Kantor')
            ->assertSee('Tim HRD Kantor')
            ->assertSee('29 October 2026')
            ->assertSee('28 October 2026');
    }

    public function test_hrd_can_edit_report(): void
    {
        $this->actingAs($this->hrd, 'admin_hrd')->post(route('admin.hrd-reports.store'), $this->payload());
        $report = HrdReport::sole();
        $this->bambang->update(['is_active' => false]);

        $this->get(route('admin.hrd-reports.edit', $report))
            ->assertOk()
            ->assertSee('Terlambat dua kali')
            ->assertSee('Karyawan Baru')
            ->assertSee('Bambang Pamungkas — Daily Report Teknisi');

        $payload = $this->payload();
        $payload['report_date'] = '2026-10-27';
        $payload['employees'] = [[
            'mode' => 'master',
            'employee_id' => $this->sri->id,
            'categories' => ['hal_positif'],
            'details' => ['hal_positif' => ['status' => 'selesai', 'note' => 'Membantu rekan kerja']],
        ]];
        $payload['include_office'] = 0;

        $this->put(route('admin.hrd-reports.update', $report), $payload)
            ->assertRedirect(route('admin.hrd-reports.show', $report));

        $report->refresh();
        $this->assertSame('2026-10-27', $report->report_date->toDateString());
        $this->assertSame(['Sri Handayani'], $report->employeeNames());
        $this->assertSame('employee', $report->type());
        $this->assertNull($report->office_findings);
        $this->assertSame($this->hrd->id, $report->created_by);
    }

    public function test_hrd_can_delete_report(): void
    {
        $this->actingAs($this->hrd, 'admin_hrd')->post(route('admin.hrd-reports.store'), $this->payload());

        $this->delete(route('admin.hrd-reports.destroy', HrdReport::sole()))
            ->assertRedirect(route('admin.hrd-reports.index'));

        $this->assertDatabaseCount('hrd_reports', 0);
    }

    public function test_export_writes_one_row_per_item_and_respects_date_filter(): void
    {
        $this->actingAs($this->hrd, 'admin_hrd')->post(route('admin.hrd-reports.store'), $this->payload());
        $older = $this->payload();
        $older['report_date'] = '2026-09-15';
        $older['employees'][0]['details']['kedisiplinan']['note'] = 'Catatan bulan lalu';
        $this->post(route('admin.hrd-reports.store'), $older);

        $csv = $this->get(route('admin.hrd-reports.export', ['start_date' => '2026-10-01', 'end_date' => '2026-10-31']))->streamedContent();

        $rows = array_map('str_getcsv', array_filter(explode("\n", trim(str_replace("\xEF\xBB\xBF", '', $csv)))));
        $this->assertCount(5, $rows); // header + 3 baris karyawan + 1 baris kantor
        $this->assertSame('Kategori', $rows[0][7]);
        $this->assertContains(['Monitoring Karyawan', 'Bambang Pamungkas', 'Kedisiplinan Karyawan', 'Menunggu pihak terkait', 'Terlambat dua kali', ''], array_map(fn ($row) => array_slice($row, 5), $rows));
        $this->assertContains(['Monitoring Karyawan', 'Karyawan Baru', 'Kedisiplinan Karyawan', 'Perlu monitoring lanjutan', 'Masa orientasi', ''], array_map(fn ($row) => array_slice($row, 5), $rows));
        $this->assertContains(['Kondisi Kantor', 'Kondisi Kantor', 'Kebersihan dan Kerapian', 'Koordinasi dengan atasan', 'Pantry kotor', 'Jadwal piket dibuat'], array_map(fn ($row) => array_slice($row, 5), $rows));
        $this->assertStringNotContainsString('Catatan bulan lalu', $csv);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return [
            'report_date' => '2026-10-29',
            'include_employee' => 1,
            'include_office' => 1,
            'employees' => [
                [
                    'mode' => 'master',
                    'employee_id' => $this->bambang->id,
                    'categories' => ['aktivitas_tanggung_jawab', 'kedisiplinan'],
                    'details' => [
                        'kedisiplinan' => ['status' => 'menunggu_pihak_terkait', 'note' => 'Terlambat dua kali'],
                        'aktivitas_tanggung_jawab' => ['status' => 'selesai', 'note' => 'Target tercapai'],
                    ],
                ],
                [
                    'mode' => 'manual',
                    'manual_name' => 'Karyawan Baru',
                    'categories' => ['kedisiplinan'],
                    'details' => [
                        'kedisiplinan' => ['status' => 'perlu_monitoring_lanjutan', 'note' => 'Masa orientasi'],
                    ],
                ],
            ],
            'office_categories' => ['kebersihan_kerapian'],
            'office' => [
                'kebersihan_kerapian' => ['finding' => 'Pantry kotor', 'action' => 'koordinasi_atasan', 'action_note' => 'Jadwal piket dibuat'],
            ],
        ];
    }

    private function user(string $role, string $name): AdminHrdUser
    {
        return AdminHrdUser::create([
            'name' => $name,
            'username' => 'user-'.$role,
            'password' => 'password',
            'role' => $role,
            'is_active' => true,
        ]);
    }
}
