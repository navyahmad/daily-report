<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Models\Division;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Admin/HRD dashboard.
     */
    public function index(Request $request): View
    {
        $selectedDate = $request->query('date', Carbon::today()->toDateString());
        $selectedDivisionId = $request->query('division_id');

        $divisions = Division::orderBy('name')->get();

        // 1. Total Active Employees
        $totalActiveEmployeesQuery = Employee::active();
        if ($selectedDivisionId) {
            $totalActiveEmployeesQuery->where('division_id', $selectedDivisionId);
        }
        $totalActiveEmployees = $totalActiveEmployeesQuery->count();

        // 2. Reports on selected date
        $reportsOnDateQuery = DailyReport::whereDate('report_date', $selectedDate)
            ->active()
            ->with(['employee', 'division']);

        if ($selectedDivisionId) {
            $reportsOnDateQuery->where('division_id', $selectedDivisionId);
        }

        $submittedCount = $reportsOnDateQuery->count();

        // 3. Total Reports All-Time
        $totalReportsAllTimeQuery = DailyReport::active();
        if ($selectedDivisionId) {
            $totalReportsAllTimeQuery->where('division_id', $selectedDivisionId);
        }
        $totalReportsAllTime = $totalReportsAllTimeQuery->count();

        // 4. Division report summary breakdown on selected date
        $divisionSummary = $divisions->map(function ($division) use ($selectedDate) {
            $totalInDiv = Employee::where('division_id', $division->id)->active()->count();
            $submittedOnDate = DailyReport::where('division_id', $division->id)
                ->whereDate('report_date', $selectedDate)
                ->active()
                ->count();
            $totalAllTime = DailyReport::where('division_id', $division->id)
                ->active()
                ->count();

            return [
                'id' => $division->id,
                'name' => $division->name,
                'code' => $division->code,
                'total_employees' => $totalInDiv,
                'submitted' => $submittedOnDate,
                'total_all_time' => $totalAllTime,
            ];
        });

        // 5. Recent Reports (10 latest submissions)
        $recentReportsQuery = DailyReport::with(['employee', 'division'])
            ->latest('submitted_at');

        if ($selectedDivisionId) {
            $recentReportsQuery->where('division_id', $selectedDivisionId);
        }

        $recentReports = $recentReportsQuery->take(10)->get();

        return view('admin.dashboard', compact(
            'selectedDate',
            'selectedDivisionId',
            'divisions',
            'totalActiveEmployees',
            'submittedCount',
            'totalReportsAllTime',
            'divisionSummary',
            'recentReports'
        ));
    }
}
