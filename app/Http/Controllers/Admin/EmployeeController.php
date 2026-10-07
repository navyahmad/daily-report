<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Employee;
use Carbon\Carbon;
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
     * Display the daily report recap of the specified employee.
     */
    public function show(Request $request, Employee $employee): View
    {
        $filters = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        // Default periode: bulan berjalan.
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;
        if (! $startDate && ! $endDate) {
            $startDate = now()->startOfMonth()->toDateString();
            $endDate = now()->endOfMonth()->toDateString();
        } elseif (! $endDate) {
            $endDate = max($startDate, now()->toDateString());
        } elseif (! $startDate) {
            $startDate = Carbon::parse($endDate)->startOfMonth()->toDateString();
        }

        $employee->load('division');

        $periodQuery = $employee->dailyReports()
            ->whereDate('report_date', '>=', $startDate)
            ->whereDate('report_date', '<=', $endDate);

        $periodActiveCount = (clone $periodQuery)->active()->count();
        $periodCancelledCount = (clone $periodQuery)->where('status', 'cancelled')->count();
        $totalActiveCount = $employee->dailyReports()->active()->count();
        $lastReport = $employee->dailyReports()->active()->latest('report_date')->first();

        $reports = $periodQuery->orderBy('report_date', 'desc')
            ->orderBy('submitted_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.employees.show', compact(
            'employee',
            'startDate',
            'endDate',
            'periodActiveCount',
            'periodCancelledCount',
            'totalActiveCount',
            'lastReport',
            'reports'
        ));
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
