<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the employees.
     */
    public function index(Request $request): View
    {
        $query = Employee::with('division');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $employees = $query->orderBy('name')->paginate(15)->withQueryString();
        $divisions = Division::orderBy('name')->get();

        return view('admin.employees.index', compact('employees', 'divisions'));
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create(): View
    {
        $divisions = Division::orderBy('name')->get();

        return view('admin.employees.create', compact('divisions'));
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'division_id' => ['required', 'exists:divisions,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        Employee::create($validated);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Data karyawan berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(Employee $employee): View
    {
        $divisions = Division::orderBy('name')->get();

        return view('admin.employees.edit', compact('employee', 'divisions'));
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'division_id' => ['required', 'exists:divisions,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        $employee->update($validated);

        return redirect()->route('admin.employees.index')
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    /**
     * Toggle the active status of an employee.
     */
    public function toggleStatus(Employee $employee): RedirectResponse
    {
        $employee->update([
            'is_active' => ! $employee->is_active,
        ]);

        $statusText = $employee->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Karyawan {$employee->name} berhasil {$statusText}.");
    }
}
