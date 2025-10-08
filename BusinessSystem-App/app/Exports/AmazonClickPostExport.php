<?php

namespace App\Exports;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style;
class AmazonClickPostExport implements WithEvents
{
    private $data;
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        //

    }



    public function registerEvents():array
    {
        return [

            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getSheetView()->setZoomScale(160);
                $count = count($this->data);


                $page = floor($count /  12);
                Log::info("Page");
                Log::info($page);

                $sheet = $event->sheet->getDelegate();

                // 横線
                $define = 6;

                $end = 7 + 7*$page;

                for($i=0;$i<$end;$i++){
                    $line = $i*$define + 1;
                    $event->sheet->getDelegate()
                        ->getStyle("A{$line}:F{$line}")
                        ->applyFromArray([
                            'borders' => [
                                'top' => [
                                    'borderStyle' => Style\Border::BORDER_THIN,
                                    'color' => ['argb' => '000000'],
                                ],
                            ],
                        ]);

                }
                foreach (range(1, 100) as $i) {
                 //   $sheet->getRowDimension($i)->setRowHeight(20);
                }
                $highestRow = $sheet->getHighestRow(); // データ最終行を自動取得
                $sheet->getPageSetup()->setPrintArea("A1:F{$highestRow}");
            }
        ];
    }
}
