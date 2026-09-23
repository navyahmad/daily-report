<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\Division;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyReportSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private Division $teknisiDiv;

    private Division $projectDiv;

    private Division $salesDiv;

    private Employee $activeEmployee;

    private Employee $inactiveEmployee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teknisiDiv = Division::create(['name' => 'Teknisi', 'code' => 'teknisi']);
        $this->projectDiv = Division::create(['name' => 'Admin Project', 'code' => 'admin_project']);
        $this->salesDiv = Division::create(['name' => 'Admin Sales', 'code' => 'admin_sales']);

        $this->activeEmployee = Employee::create([
            'division_id' => $this->teknisiDiv->id,
            'name' => 'Budi Pratama',
            'is_active' => true,
        ]);

        $this->inactiveEmployee = Employee::create([
            'division_id' => $this->teknisiDiv->id,
            'name' => 'Doni Nonaktif',
            'is_active' => false,
        ]);
    }

    public function test_public_report_page_can_be_rendered(): void
    {
        $response = $this->get(route('report.create'));

        $response->assertStatus(200);
        $response->assertSee('Daily Report');
        $response->assertSee('Teknisi');
    }

    public function test_api_employees_returns_only_active_employees_for_division(): void
    {
        $response = $this->getJson(route('api.employees', ['division_id' => $this->teknisiDiv->id]));

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['name' => 'Budi Pratama']);
        $response->assertJsonMissing(['name' => 'Doni Nonaktif']);
    }

    public function test_employee_can_submit_teknisi_report(): void
    {
        $payload = [
            'division_id' => $this->teknisiDiv->id,
            'employee_id' => $this->activeEmployee->id,
            'report_date' => '2026-09-17',
            'email' => 'budi@kantor.com',
            'form_data' => [
                'work_items' => [
                    [
                        'type' => 'instalasi',
                        'custom_type' => '',
                        'detail' => 'Instalasi access point di lantai 2',
                        'status' => 'selesai',
                    ],
                    [
                        'type' => 'maintenance',
                        'custom_type' => '',
                        'detail' => 'Pembersihan rack server utama',
                        'status' => 'progres',
                    ],
                ],
                'kendala' => 'Tidak ada kendala',
                'tomorrow_activities' => ['maintenance'],
                'tomorrow_activity_details' => ['maintenance' => 'Maintenance rack server gedung B'],
            ],
        ];

        $response = $this->post(route('report.store'), $payload);

        $response->assertRedirect(route('report.success'));
        $this->assertDatabaseHas('daily_reports', [
            'employee_id' => $this->activeEmployee->id,
            'division_id' => $this->teknisiDiv->id,
            'report_date' => '2026-09-17 00:00:00',
            'email' => 'budi@kantor.com',
            'employee_name_snapshot' => 'Budi Pratama',
            'division_name_snapshot' => 'Teknisi',
            'division_code_snapshot' => 'teknisi',
            'form_version' => 2,
            'status' => 'active',
        ]);
    }

    public function test_employee_cannot_submit_duplicate_report_for_same_date(): void
    {
        DailyReport::create([
            'employee_id' => $this->activeEmployee->id,
            'division_id' => $this->teknisiDiv->id,
            'report_date' => '2026-09-17',
            'email' => 'budi@kantor.com',
            'employee_name_snapshot' => 'Budi Pratama',
            'division_name_snapshot' => 'Teknisi',
            'division_code_snapshot' => 'teknisi',
            'form_version' => 2,
            'status' => 'active',
            'form_data' => [
                'work_items' => [
                    [
                        'type' => 'instalasi',
                        'custom_type' => '',
                        'detail' => 'Instalasi router',
                        'status' => 'selesai',
                    ],
                ],
            ],
            'submitted_at' => now(),
        ]);

        $payload = [
            'division_id' => $this->teknisiDiv->id,
            'employee_id' => $this->activeEmployee->id,
            'report_date' => '2026-09-17',
            'email' => 'budi@kantor.com',
            'form_data' => [
                'work_items' => [
                    [
                        'type' => 'maintenance',
                        'custom_type' => '',
                        'detail' => 'Maintenance switch',
                        'status' => 'selesai',
                    ],
                ],
            ],
        ];

        $response = $this->post(route('report.store'), $payload);

        $response->assertSessionHasErrors('report_date');
    }

    public function test_admin_procurement_report_submission(): void
    {
        $procDiv = Division::create(['name' => 'Admin Procurement', 'code' => 'admin_procurement']);
        $procEmp = Employee::create([
            'division_id' => $procDiv->id,
            'name' => 'Agus Procurement',
            'is_active' => true,
        ]);

        $payload = [
            'division_id' => $procDiv->id,
            'employee_id' => $procEmp->id,
            'report_date' => '2026-09-17',
            'email' => 'agus@kantor.com',
            'form_data' => [
                'work_categories' => ['cari_barang', 'po'],
                'detail_cari_barang' => 'Mencari switch cisco 24 port',
                'jumlah_po' => 2,
                'detail_po_vendor' => 'PO 001 PT ABC, PO 002 CV XYZ',
                'barang_diterima_dikirim' => 'Barang masuk 5 unit router',
                'kendala' => 'Stok vendor terbatas',
                'tomorrow_activities' => ['po'],
                'tomorrow_activity_details' => ['po' => 'Follow up vendor kabel LAN'],
            ],
        ];

        $response = $this->post(route('report.store'), $payload);
        $response->assertRedirect(route('report.success'));
        $this->assertDatabaseHas('daily_reports', [
            'employee_id' => $procEmp->id,
            'form_version' => 2,
        ]);
    }

    public function test_admin_project_report_submission_with_projects_and_documents(): void
    {
        $projectEmp = Employee::create([
            'division_id' => $this->projectDiv->id,
            'name' => 'Siti Project',
            'is_active' => true,
        ]);

        $payload = [
            'division_id' => $this->projectDiv->id,
            'employee_id' => $projectEmp->id,
            'report_date' => '2026-09-17',
            'email' => 'siti@kantor.com',
            'form_data' => [
                'documents_processed' => ['sow', 'bast'],
                'document_details' => [
                    'sow' => 'Penyusunan draft SOW project RS Medika',
                    'bast' => 'Finalisasi tanda tangan BAST gedung B',
                ],
                'project_count' => 2,
                'projects' => [
                    [
                        'project_description' => 'Migrasi Data Server RS',
                        'progress_percent' => 80,
                    ],
                    [
                        'project_description' => 'Pengadaan Access Point Wisma',
                        'progress_percent' => 45,
                    ],
                ],
                'kendala' => 'Menunggu approval direksi',
                'tomorrow_activities' => ['lainnya'],
                'tomorrow_activity_details' => ['lainnya' => 'Kick-off meeting tahap 2'],
            ],
        ];

        $response = $this->post(route('report.store'), $payload);

        $response->assertRedirect(route('report.success'));
        $this->assertDatabaseHas('daily_reports', [
            'employee_id' => $projectEmp->id,
            'division_code_snapshot' => 'admin_project',
            'form_version' => 2,
        ]);
    }

    public function test_admin_sales_submission_with_activity_details(): void
    {
        $salesEmp = Employee::create([
            'division_id' => $this->salesDiv->id,
            'name' => 'Rina Sales',
            'is_active' => true,
        ]);

        $payload = [
            'division_id' => $this->salesDiv->id,
            'employee_id' => $salesEmp->id,
            'report_date' => '2026-09-17',
            'email' => 'rina@kantor.com',
            'form_data' => [
                'today_activities' => ['follow_up', 'meeting'],
                'today_activity_details' => [
                    'follow_up' => 'Follow up PT Jaya Abadi terkait penawaran server',
                    'meeting' => 'Meeting zoom dengan klien CV Makmur',
                ],
                'tomorrow_activities' => ['membuat_penawaran'],
                'tomorrow_activity_details' => [
                    'membuat_penawaran' => 'Kirim penawaran final ke PT Mitra',
                ],
                'jumlah_customer' => 5,
                'jumlah_quotation' => 2,
                'jumlah_closing' => 1,
                'kendala' => '',
            ],
        ];

        $response = $this->post(route('report.store'), $payload);

        $response->assertRedirect(route('report.success'));
        $this->assertDatabaseHas('daily_reports', [
            'employee_id' => $salesEmp->id,
            'division_code_snapshot' => 'admin_sales',
            'form_version' => 2,
        ]);
    }

    public function test_finance_submission_with_dynamic_invoices_and_jurnal(): void
    {
        $finDiv = Division::create(['name' => 'Finance', 'code' => 'finance']);
        $finEmp = Employee::create([
            'division_id' => $finDiv->id,
            'name' => 'Dewi Finance',
            'is_active' => true,
        ]);

        $payload = [
            'division_id' => $finDiv->id,
            'employee_id' => $finEmp->id,
            'report_date' => '2026-09-17',
            'email' => 'dewi@kantor.com',
            'form_data' => [
                'pekerjaan_hari_ini' => 'Rekonsiliasi rekening koran dan pembukuan',
                'invoice_count' => 2,
                'invoice_details' => [
                    ['description' => 'INV-2026-001 PT ABC Rp 15.000.000'],
                    ['description' => 'INV-2026-002 CV XYZ Rp 8.500.000'],
                ],
                'jurnal' => 'Pencatatan kas masuk pelunasan invoice 001',
                'rekap_kas_bank' => 'Saldo akhir kas Rp 25.000.000',
                'kendala' => '',
                'tomorrow_activities' => ['invoice'],
                'tomorrow_activity_details' => ['invoice' => 'Penagihan invoice jatuh tempo'],
            ],
        ];

        $response = $this->post(route('report.store'), $payload);

        $response->assertRedirect(route('report.success'));
        $this->assertDatabaseHas('daily_reports', [
            'employee_id' => $finEmp->id,
            'division_code_snapshot' => 'finance',
            'form_version' => 2,
        ]);
    }

    public function test_system_informasi_can_submit_social_media_activity(): void
    {
        $sysDiv = Division::create(['name' => 'System Informasi', 'code' => 'system_informasi']);
        $sysEmp = Employee::create([
            'division_id' => $sysDiv->id,
            'name' => 'Rina System',
            'is_active' => true,
        ]);

        $this->get(route('report.create'))->assertOk()->assertSee('Social Media');

        $response = $this->post(route('report.store'), [
            'division_id' => $sysDiv->id,
            'employee_id' => $sysEmp->id,
            'report_date' => '2026-09-23',
            'email' => 'rina@kantor.com',
            'form_data' => [
                'system_activities' => ['social_media'],
                'system_activity_details' => ['social_media' => 'Posting konten Instagram produk baru'],
                'status_pengerjaan' => 'Selesai',
                'tomorrow_activities' => ['social_media'],
                'tomorrow_activity_details' => ['social_media' => 'Jadwalkan konten minggu depan'],
            ],
        ]);

        $response->assertRedirect(route('report.success'));
        $this->assertSame(['social_media'], DailyReport::sole()->form_data['system_activities']);
    }

    public function test_teknisi_custom_type_is_required_when_lainnya_is_selected(): void
    {
        $payload = [
            'division_id' => $this->teknisiDiv->id,
            'employee_id' => $this->activeEmployee->id,
            'report_date' => '2026-09-17',
            'email' => 'budi@kantor.com',
            'form_data' => [
                'work_items' => [
                    [
                        'type' => 'lainnya',
                        'custom_type' => '', // Should fail
                        'detail' => 'Penjelasan pekerjaan khusus',
                        'status' => 'selesai',
                    ],
                ],
            ],
        ];

        $response = $this->post(route('report.store'), $payload);
        $response->assertSessionHasErrors('form_data.work_items.0.custom_type');
    }

    public function test_teknisi_rejects_duplicate_work_types(): void
    {
        $payload = [
            'division_id' => $this->teknisiDiv->id,
            'employee_id' => $this->activeEmployee->id,
            'report_date' => '2026-09-17',
            'email' => 'budi@kantor.com',
            'form_data' => [
                'work_items' => [
                    [
                        'type' => 'instalasi',
                        'custom_type' => '',
                        'detail' => 'Instalasi router lantai 1',
                        'status' => 'selesai',
                    ],
                    [
                        'type' => 'instalasi', // duplicate
                        'custom_type' => '',
                        'detail' => 'Instalasi router lantai 2',
                        'status' => 'selesai',
                    ],
                ],
            ],
        ];

        $response = $this->post(route('report.store'), $payload);
        $response->assertSessionHasErrors('form_data.work_items.1.type');
    }

    public function test_procurement_requires_detail_for_selected_categories(): void
    {
        $procDiv = Division::create(['name' => 'Admin Procurement', 'code' => 'admin_procurement']);
        $procEmp = Employee::create([
            'division_id' => $procDiv->id,
            'name' => 'Agus Procurement',
            'is_active' => true,
        ]);

        $payload = [
            'division_id' => $procDiv->id,
            'employee_id' => $procEmp->id,
            'report_date' => '2026-09-17',
            'email' => 'agus@kantor.com',
            'form_data' => [
                'work_categories' => ['cari_barang', 'po'],
                // Missing detail_cari_barang, jumlah_po, detail_po_vendor
            ],
        ];

        $response = $this->post(route('report.store'), $payload);
        $response->assertSessionHasErrors([
            'form_data.detail_cari_barang',
            'form_data.jumlah_po',
            'form_data.detail_po_vendor',
        ]);
    }
}
