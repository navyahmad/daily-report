<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminHrdUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    /**
     * Display a listing of Admin and HRD user accounts.
     */
    public function index(): View
    {
        $users = AdminHrdUser::orderBy('role')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user account.
     */
    public function create(): View
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user account.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:admin_hrd_users,username'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:admin,hrd,manager'],
            'is_active' => ['nullable'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah terdaftar.',
            'password.required' => 'Password awal wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'role.required' => 'Role akun wajib dipilih.',
            'role.in' => 'Pilihan role tidak valid.',
        ]);

        AdminHrdUser::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Akun '.strtoupper($validated['role']).' berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified user account.
     */
    public function edit(AdminHrdUser $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user account.
     */
    public function update(Request $request, AdminHrdUser $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('admin_hrd_users')->ignore($user->id)],
            'role' => ['required', 'in:admin,hrd,manager'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah terdaftar untuk pengguna lain.',
            'role.required' => 'Role akun wajib dipilih.',
        ]);

        // Prevent self-demoting from admin if editing own account
        if ($user->id === Auth::guard('admin_hrd')->id() && $validated['role'] !== 'admin') {
            return back()->withErrors([
                'role' => 'Anda tidak dapat mengubah role akun Anda sendiri menjadi non-admin.',
            ]);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Data akun '.$user->name.' berhasil diperbarui.');
    }

    /**
     * Toggle active/inactive status of the user account.
     */
    public function toggleStatus(AdminHrdUser $user): RedirectResponse
    {
        if ($user->id === Auth::guard('admin_hrd')->id()) {
            return back()->withErrors([
                'error' => 'Anda tidak dapat menonaktifkan akun Anda sendiri yang sedang aktif digunakan.',
            ]);
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.users.index')
            ->with('success', "Akun {$user->name} berhasil {$statusText}.");
    }

    /**
     * Reset password for the specified user account.
     */
    public function resetPassword(Request $request, AdminHrdUser $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Password untuk akun {$user->name} berhasil diperbarui.");
    }
}
