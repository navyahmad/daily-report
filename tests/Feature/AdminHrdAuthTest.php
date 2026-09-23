<?php

namespace Tests\Feature;

use App\Models\AdminHrdUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminHrdAuthTest extends TestCase
{
    use RefreshDatabase;

    private AdminHrdUser $admin;

    private AdminHrdUser $hrd;

    private AdminHrdUser $inactiveUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = AdminHrdUser::create([
            'name' => 'Admin Utama',
            'username' => 'admin',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->hrd = AdminHrdUser::create([
            'name' => 'Staf HRD',
            'username' => 'hrd',
            'password' => Hash::make('password123'),
            'role' => 'hrd',
            'is_active' => true,
        ]);

        $this->inactiveUser = AdminHrdUser::create([
            'name' => 'Mantan Admin',
            'username' => 'nonaktif',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => false,
        ]);
    }

    public function test_login_page_renders_with_custom_office_labels(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);
        $response->assertSee('Daily Report Kantor');
        $response->assertSee('Username');
        $response->assertSee('Password');
        $response->assertSee('Masuk Akun');
        // Ensure standard Breeze/Laravel registration links are absent
        $response->assertDontSee('Register');
        $response->assertDontSee('Forgot your password?');
    }

    public function test_active_admin_can_login_and_updates_last_login_at(): void
    {
        $this->assertNull($this->admin->last_login_at);

        $response = $this->post(route('admin.login.store'), [
            'username' => 'admin',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertTrue(Auth::guard('admin_hrd')->check());
        $this->assertEquals($this->admin->id, Auth::guard('admin_hrd')->id());

        $this->admin->refresh();
        $this->assertNotNull($this->admin->last_login_at);
    }

    public function test_active_hrd_can_login_and_access_dashboard(): void
    {
        $response = $this->post(route('admin.login.store'), [
            'username' => 'hrd',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertTrue(Auth::guard('admin_hrd')->check());
        $this->assertEquals('hrd', Auth::guard('admin_hrd')->user()->role);

        $dashResponse = $this->actingAs($this->hrd, 'admin_hrd')->get(route('admin.dashboard'));
        $dashResponse->assertStatus(200);
    }

    public function test_inactive_account_cannot_login(): void
    {
        $response = $this->post(route('admin.login.store'), [
            'username' => 'nonaktif',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertFalse(Auth::guard('admin_hrd')->check());
    }

    public function test_invalid_credentials_returns_generic_error(): void
    {
        $response = $this->post(route('admin.login.store'), [
            'username' => 'admin',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors(['username' => 'Username atau password tidak valid.']);
        $this->assertFalse(Auth::guard('admin_hrd')->check());
    }

    public function test_admin_and_hrd_can_logout(): void
    {
        $this->actingAs($this->admin, 'admin_hrd');

        $response = $this->post(route('admin.logout'));

        $response->assertRedirect(route('admin.login'));
        $this->assertFalse(Auth::guard('admin_hrd')->check());
    }

    public function test_unauthenticated_user_cannot_access_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_session_with_deactivated_account_is_blocked_by_middleware(): void
    {
        // Log in as active user
        $this->actingAs($this->admin, 'admin_hrd');

        // Deactivate the user while logged in
        $this->admin->update(['is_active' => false]);

        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
        $this->assertFalse(Auth::guard('admin_hrd')->check());
    }

    public function test_admin_role_can_manage_users_toggle_status_and_reset_password(): void
    {
        $this->actingAs($this->admin, 'admin_hrd');

        // 1. View User List
        $indexResponse = $this->get(route('admin.users.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Kelola Akun Manager & HRD');
        $indexResponse->assertSee('Staf HRD');

        // 2. Create New HRD User
        $storeResponse = $this->post(route('admin.users.store'), [
            'name' => 'Bambang HRD',
            'username' => 'bambang',
            'password' => 'secret123',
            'role' => 'hrd',
            'is_active' => '1',
        ]);
        $storeResponse->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('admin_hrd_users', [
            'username' => 'bambang',
            'role' => 'hrd',
            'is_active' => true,
        ]);

        $bambang = AdminHrdUser::where('username', 'bambang')->first();

        // 3. Toggle Status to Inactive
        $toggleResponse = $this->patch(route('admin.users.toggle-status', $bambang));
        $toggleResponse->assertRedirect(route('admin.users.index'));
        $this->assertFalse($bambang->fresh()->is_active);

        // 4. Reset Password
        $resetResponse = $this->put(route('admin.users.reset-password', $bambang), [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);
        $resetResponse->assertRedirect(route('admin.users.index'));
        $this->assertTrue(Hash::check('newpassword123', $bambang->fresh()->password));
    }

    public function test_hrd_role_cannot_access_user_management(): void
    {
        $this->actingAs($this->hrd, 'admin_hrd');

        $response = $this->get(route('admin.users.index'));
        $response->assertStatus(403);

        $createResponse = $this->get(route('admin.users.create'));
        $createResponse->assertStatus(403);
    }

    public function test_admin_cannot_deactivate_own_account(): void
    {
        $this->actingAs($this->admin, 'admin_hrd');

        $response = $this->patch(route('admin.users.toggle-status', $this->admin));

        $response->assertSessionHasErrors('error');
        $this->assertTrue($this->admin->fresh()->is_active);
    }
}
