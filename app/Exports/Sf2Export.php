<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

/**
 * Renders the SF2 grid (an HTML table with colspan/rowspan) into an XLSX with
 * matching merged cells, then applies landscape print setup + borders.
 */
class Sf2Export implements FromView, WithEvents, WithTitle
{
    public function __construct(private readonly array $data) {}

    public function view(): View
    {
        return view('reports.sf2.excel', ['data' => $this->data] + $this->data);
    }

    public function title(): string
    {
        return 'SF2';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $setup = $sheet->getPageSetup();
                $setup->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $setup->setPaperSize(PageSetup::PAPERSIZE_A4);
                $setup->setFitToWidth(1);
                $setup->setFitToHeight(0);
                $sheet->getPageMargins()->setTop(0.2)->setBottom(0.2)->setLeft(0.2)->setRight(0.2);

                // Thin borders across the whole used range.
                $sheet->getStyle('A1:'.$sheet->getHighestColumn().$sheet->getHighestRow())
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                $sheet->getColumnDimension('B')->setWidth(28); // learner name column
            },
        ];
    }
}
