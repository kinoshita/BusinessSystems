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
                $write_position = 1;
                foreach ($this->data as $key => $value) {
                    Log::info("Data .....");
                    //Log::info($value[$key]);
                    if ($key % 14 == 0 && $key !== 0) {
                        $write_position += 6;
                    }


                    if($key % 2 == 0 && $key !== 0) {
                        $write_position += 6;
                    }
                    if ($key % 2 == 0 && $key !== 1) {
                        $event->sheet
                            ->setCellValue("A{$write_position}", $value->ship_postal_code);

                        $city = 1;
                        $city = $city + $write_position;
                        $event->sheet
                            ->setCellValue("A{$city}", $value->ship_state);

                        $address_1 = 2;
                        $address_1  = $address_1 + $write_position;
                        $event->sheet
                            ->setCellValue("A{$address_1}", $value->ship_address_1);

                        $address_2 = 3;
                        $address_2  = $address_2 + $write_position;
                        $event->sheet
                            ->setCellValue("A{$address_2}", $value->ship_address_2);

                        $address_3 = 4;
                        $address_3  = $address_3 + $write_position;
                        $event->sheet
                            ->setCellValue("A{$address_3}", $value->ship_address_3);

                        $name = 5;
                        $name  = $name + $write_position;
                        $event->sheet
                            ->setCellValue("A{$name}", $value->recipient_name);


                    }else {
                        //dd($value);
                        $event->sheet
                            ->setCellValue("D{$write_position}", $value->ship_postal_code);

                        $city = 1;
                        $city = $city + $write_position;
                        $event->sheet
                            ->setCellValue("D{$city}", $value->ship_state);

                        $address_1 = 2;
                        $address_1  = $address_1 + $write_position;
                        $event->sheet
                            ->setCellValue("D{$address_1}", $value->ship_address_1);

                        $address_2 = 3;
                        $address_2  = $address_2 + $write_position;
                        $event->sheet
                            ->setCellValue("D{$address_2}", $value->ship_address_2);

                        $address_3 = 4;
                        $address_3  = $address_3 + $write_position;
                        $event->sheet
                            ->setCellValue("D{$address_3}", $value->ship_address_3);

                        $name = 5;
                        $name  = $name + $write_position;
                        $event->sheet
                            ->setCellValue("D{$name}", $value->recipient_name);

                    }



                }


                $highestRow = $sheet->getHighestRow(); // データ最終行を自動取得
                $sheet->getPageSetup()->setPrintArea("A1:F{$highestRow}");
            }
        ];
    }
}
