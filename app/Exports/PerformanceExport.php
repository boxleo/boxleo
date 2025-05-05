<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PerformanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $evaluations;

    public function __construct($evaluations)
    {
        $this->evaluations = $evaluations;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->evaluations;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Employee Name',
            // 'Evaluator',
            'Department',
            // 'Evaluation Date',
            'Attendance',
            'Problems Solved',
            'Reports Submitted',
            'Knowledge of Work',
            'Team Work',
            'Reliability & Visibility',
            'Productivity',
            'Discipline',
            'Quality of Work',
            'Communication',
            'Leadership',
            'Total Score',
            'Percentage (%)',
            'Evaluated On'
        ];
    }

  


    public function map($evaluation): array
{
    Log::info('Mapping evaluation record', ['evaluation' => $evaluation]);

    return [
        $evaluation['id'] ?? 'N/A', 

        // Employee Name
        ($evaluation['user']['firstname'] ?? 'N/A') . ' ' . ($evaluation['user']['lastname'] ?? 'N/A'),

        // Evaluator
        ($evaluation['evaluator']['firstname'] ?? 'N/A') . ' ' . ($evaluation['evaluator']['lastname'] ?? 'N/A'),

        // Department
        $evaluation['user']['department']['name'] ?? 'N/A',

        // Evaluation Date
        $evaluation['evaluation_date'] ? Carbon::parse($evaluation['evaluation_date'])->format('Y-m-d') : 'N/A',

        // Other fields
        $evaluation['attendance'] ?? 'N/A',
        $evaluation['problems_solved'] ?? 'N/A',
        $evaluation['reports_submitted'] ?? 'N/A',
        $evaluation['knowledge_of_work'] ?? 'N/A',
        $evaluation['team_work'] ?? 'N/A',
        $evaluation['reliability_visibility'] ?? 'N/A',
        $evaluation['productivity'] ?? 'N/A',
        $evaluation['discipline'] ?? 'N/A',
        $evaluation['quality_of_work'] ?? 'N/A',
        $evaluation['communication'] ?? 'N/A',
        $evaluation['leadership'] ?? 'N/A',
        $evaluation['total_score'] ?? 'N/A',
        $evaluation['percentage'] ?? 'N/A',
        $evaluation['created_at'] ? Carbon::parse($evaluation['created_at'])->format('Y-m-d H:i:s') : 'N/A',
    ];
}

    

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'CCCCCC']]],
        ];
    }
}
