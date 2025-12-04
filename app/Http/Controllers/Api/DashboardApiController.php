<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TasFile;
use App\Models\Admitted;
use App\Models\ApprehendingOfficer;
use App\Models\TrafficViolation;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardApiController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function getStats(Request $request)
    {
        $cacheKey = 'dashboard_stats_' . auth()->id();
        
        $stats = Cache::remember($cacheKey, 300, function () {
            return [
                'cases_this_month' => TasFile::whereMonth('date_received', date('m'))->count(),
                'cases_this_year' => TasFile::whereYear('date_received', now())->count(),
                'admitted_total' => Admitted::count(),
                'active_officers' => ApprehendingOfficer::count(),
                'recent_cases' => TasFile::whereDate('created_at', today())
                    ->latest()
                    ->take(5)
                    ->get(),
            ];
        });

        return response()->json($stats);
    }

    /**
     * Get chart data
     */
    public function getChartData(Request $request)
    {
        $cacheKey = 'chart_data_' . $request->input('type', 'monthly');
        
        $data = Cache::remember($cacheKey, 600, function () use ($request) {
            $type = $request->input('type', 'monthly');
            
            if ($type === 'monthly') {
                return TasFile::select(
                    DB::raw('MONTH(date_received) as month'),
                    DB::raw('COUNT(*) as count')
                )
                ->whereYear('date_received', now()->year)
                ->groupBy('month')
                ->orderBy('month')
                ->get();
            }
            
            return [];
        });

        return response()->json($data);
    }

    /**
     * Get pie chart data
     */
    public function getPieChartData(Request $request)
    {
        $data = Cache::remember('pie_chart_data', 600, function () {
            return TasFile::select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get();
        });

        return response()->json($data);
    }

    /**
     * Get violation rankings
     */
    public function getViolationRankings(Request $request)
    {
        $data = Cache::remember('violation_rankings', 600, function () {
            $violations = [];
            $tasFiles = TasFile::all();
            
            foreach ($tasFiles as $file) {
                $codes = json_decode($file->violation, true);
                if (is_array($codes)) {
                    foreach ($codes as $code) {
                        if (!isset($violations[$code])) {
                            $violations[$code] = 0;
                        }
                        $violations[$code]++;
                    }
                }
            }
            
            arsort($violations);
            return array_slice($violations, 0, 10, true);
        });

        return response()->json($data);
    }

    /**
     * Get officers
     */
    public function getOfficers(Request $request)
    {
        $officers = ApprehendingOfficer::select('officer', 'department')
            ->orderBy('officer')
            ->get();

        return response()->json($officers);
    }

    /**
     * Get officers by department
     */
    public function getOfficersByDepartment(Request $request, $department)
    {
        $officers = ApprehendingOfficer::where('department', $department)
            ->select('officer', 'department')
            ->get();

        return response()->json($officers);
    }

    /**
     * Get violations
     */
    public function getViolations(Request $request)
    {
        $violations = TrafficViolation::orderBy('code', 'asc')->get();
        return response()->json($violations);
    }

    /**
     * Get departments
     */
    public function getDepartments(Request $request)
    {
        $departments = Department::all();
        return response()->json($departments);
    }
}
