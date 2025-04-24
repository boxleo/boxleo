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

class EmployeeRankingExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $rankedEmployees;

    public function __construct($rankedEmployees)
    {
        $this->rankedEmployees = $rankedEmployees;
    }

    public function collection()
    {
        return $this->rankedEmployees;
    }

    public function headings(): array
    {
        return [
            'Rank',
            'Employee Name',
            'Department',
            'Total Score',
            'Percentage (%)',
            'Evaluation Date',
        ];
    }

    public function map($employee): array
    {

        Log::info('Mapping evaluation record', ['evaluation' => $employee]);

        return [
            $employee['rank'] ?? 'N/A',

            ($employee['user']['firstname'] ?? 'N/A') . ' ' . ($employee['user']['lastname'] ?? 'N/A'),

            $employee['user']['department']['name'] ?? 'N/A',
            

            $employee['total_score'] ?? 'N/A',

            $employee['percentage'] ?? 'N/A',

            $employee['created_at'] 
                ? Carbon::parse($employee['evaluation_date'])->format('Y-m-d') 
                : 'N/A',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'CCCCCC']
                ],
            ],
        ];
    }
}
