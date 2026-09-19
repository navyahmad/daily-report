<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDailyReportRequest;
use App\Models\DailyReport;
use App\Models\Division;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicReportController extends Controller
{
    /**
     * Show the daily report submission form.
     */
    public function create(): View
    {
        $divisions = Division::orderBy('name')->get();

        return view('public.report-form', compact('divisions'));
    }

    /**
     * Get active employees for a given division.
     */
    public function getEmployees(Request $request): JsonResponse
    {
        $divisionId = $request->query('division_id');

        $employees = Employee::where('division_id', $divisionId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($employees);
    }

    /**
     * Store a newly created daily report.
     */
    public function store(StoreDailyReportRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $employee = Employee::with('division')->findOrFail($validated['employee_id']);
        $division = $employee->division ?? Division::findOrFail($validated['division_id']);

        $report = DailyReport::create([
            'employee_id' => $employee->id,
            'division_id' => $division->id,
            'report_date' => $validated['report_date'],
            'email' => $validated['email'],
            'employee_name_snapshot' => $employee->name,
            'division_name_snapshot' => $division->name,
            'division_code_snapshot' => $division->code,
            'form_version' => 2,
            'status' => 'active',
            'form_data' => $validated['form_data'],
            'submitted_at' => now(),
        ]);

        return redirect()->route('report.success')->with([
            'employee_name' => $report->employee_name_snapshot,
            'report_date' => $report->report_date->translatedFormat('d F Y'),
            'division_name' => $report->division_name_snapshot,
        ]);
    }

    /**
     * Display the success confirmation page.
     */
    public function success(): View
    {
        return view('public.report-success');
    }
}
