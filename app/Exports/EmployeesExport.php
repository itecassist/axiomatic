<?php

namespace App\Exports;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeesExport implements FromCollection, WithHeadings, WithStyles, ShouldQueue
{
    public function __construct(private $employees) {}

    public function collection()
    {
        return $this->employees->map(fn ($emp) => [
            $emp->employee_number,
            $emp->employee_display,
            $emp->branch?->name ?? '',
            $emp->branch?->company?->name ?? '',
        ]);
    }

    public function headings(): array
    {
        return ['Employee Number', 'Name', 'Branch', 'Company'];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('1')->getFont()->setBold(true);
        $sheet->getStyle('1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD3D3D3');

        foreach (range('A', 'D') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [];
    }
}
