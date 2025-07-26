<?php

namespace App\Http\Controllers;

use App\Exports\EmployeeRankingExport;
use App\Exports\PerformanceExport;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class PerformanceApiReportController extends Controller
{

    // Export ranked evaluations as Excel
    public function exportRankedEmployees(Request $request)
    {
        $evaluations = collect($request->input('evaluations', []));
        $ranked = $this->rankEvaluations($evaluations);

        return Excel::download(new EmployeeRankingExport($ranked), 'ranked_employees.xlsx');
    }

    // Export raw performance evaluations as Excel
    public function exportPerformanceEvaluations(Request $request)
    {
        $evaluations = collect($request->input('evaluations', []));

        return Excel::download(new PerformanceExport($evaluations), 'performance_evaluations.xlsx');
    }

    // Helper to assign ranking based on percentage 
    private function rankEvaluations(Collection $evaluations): Collection
    {
        return $evaluations->sortByDesc('percentage')->values()->map(function ($item, $index) {
            $item['rank'] = $index + 1;
            return $item;
        });
    }
}
