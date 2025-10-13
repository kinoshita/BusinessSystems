<?php

namespace App\Exports;

use App\Models\AmazonYamatoItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class AmazonYamatoExport implements WithEvents
{

    public function __construct($data, $header)
    {
        $this->data = $data;
        $this->header = $header;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        //
    }

    public function registerEvents(): array
    {
        // TODO: Implement registerEvents() method.
        return [


            AfterSheet::class => function (AfterSheet $event) {
                $colIndex = 1;
                foreach ($this->header as $key=>$value){
                    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                    // セルに値を設定（例：A1, B1, C1...）
                    $event->sheet->setCellValue("{$columnLetter}1", $value);
                    $colIndex++;
                }
                $rowIndex = 2; // データは2行目から開始

                foreach ($this->data as $record) {
                    $colIndex = 1;

                    // ヘッダー順に値を取り出してセット
                    foreach ($this->header as $key) {
                        $columnLetter = Coordinate::stringFromColumnIndex($colIndex);
                        $value = $record->$key ?? ''; // データが無ければ空文字
                        $event->sheet->setCellValue("{$columnLetter}{$rowIndex}", $value);
                        $colIndex++;
                    }

                    $rowIndex++;
                }

                //dd($this->data);




            }
        ];
    }


}
