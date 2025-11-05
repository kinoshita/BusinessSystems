<?php

namespace App\Exports;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style;

class AmazonLetterExport implements WithEvents
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


    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getSheetView()->setZoomScale(160);
                $count = count($this->data);


                $page = ceil($count / 10);
                Log::info("Page");
                Log::info($page);

                $sheet = $event->sheet->getDelegate();

                // 横線
                $define = 8; // 1ブロック7行
                $pageBlock = 2; // 左右2ブロック
                $totalBlock = 6 * $page;

                for ($i = 0; $i < $totalBlock; $i++) {
                    // 各ブロックの最初の行（1, 7, 13, ...）を算出
                    $line = $i * $define + 1;

                    $event->sheet->getDelegate()
                        ->getStyle("A{$line}:F{$line}")
                        ->applyFromArray([
                            'borders' => [
                                'top' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR,
                                    'color' => ['argb' => '000000'],
                                ],
                            ],
                        ]);
                    /*
                                        if ($i % 5 == 0 && $i !==0 ){
                                            $event->sheet->getDelegate()
                                                ->getStyle("A{$line}:F{$line}")
                                                ->applyFromArray([
                                                    'borders' => [
                                                        'bottom' => [
                                                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                                            'color' => ['argb' => '000000'],
                                                        ],
                                                    ],
                                                ]);
                                        }
                    */


                }
                // 横線
                //$page = 2;




                $plus = 48;
                for ($i = 0; $i < $page; $i++) {
                    $start_line = 1 + $plus * $i;
                    $end_line = 40 + $plus * $i;
                    $event->sheet->getDelegate()
                        ->getStyle("A{$start_line}:A{$end_line}")
                        ->applyFromArray([
                            'borders' => [
                                'left' => [
                                    'borderStyle' => Style\Border::BORDER_HAIR,
                                    'color' => ['argb' => '000000'],
                                ],
                            ],
                        ]);

                    $event->sheet->getDelegate()
                        ->getStyle("D{$start_line}:D{$end_line}")
                        ->applyFromArray([
                            'borders' => [
                                'left' => [
                                    'borderStyle' => Style\Border::BORDER_HAIR,
                                    'color' => ['argb' => '000000'],
                                ],
                            ],
                        ]);

                    $event->sheet->getDelegate()
                        ->getStyle("F{$start_line}:F{$end_line}")
                        ->applyFromArray([
                            'borders' => [
                                'right' => [
                                    'borderStyle' => Style\Border::BORDER_HAIR,
                                    'color' => ['argb' => '000000'],
                                ],
                            ],
                        ]);
                }

                $write_position = 1;
                foreach ($this->data as $key => $value) {
                    //Log::info($value[$key]);
                    if ($key % 10 == 0 && $key !== 0) {
                        $write_position += 8;
                    }


                    if ($key % 2 == 0 && $key !== 0) {
                        $write_position += 8;
                    }
                    if ($key % 2 == 0 && $key !== 1) {
                        $event->sheet
                            ->setCellValue("A{$write_position}", $value->ship_postal_code);

                        $city = 1;
                        $city = $city + $write_position;
                        $event->sheet
                            ->setCellValue("A{$city}", $value->ship_state);

                        $address_1 = 2;
                        $address_1 = $address_1 + $write_position;
                        $event->sheet
                            ->setCellValue("A{$address_1}", $value->ship_address_1);

                        $address_2 = 3;
                        $address_2 = $address_2 + $write_position;
                        $event->sheet
                            ->setCellValue("A{$address_2}", $value->ship_address_2);

                        $address_3 = 4;
                        $address_3 = $address_3 + $write_position;
                        $event->sheet
                            ->setCellValue("A{$address_3}", $value->ship_address_3);

                        $name = 6;
                        $name = $name + $write_position;
                        $event->sheet
                            ->setCellValue("A{$name}", $value->recipient_name . '様');
                        $tel = 7;
                        $tel = $tel + $write_position;
                        $event->sheet
                            ->setCellValue("A{$tel}", $value->buyer_phone_number);
                        // 広げる
                        $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(22);
                        $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(22);

                    } else {
                        //dd($value);
                        $event->sheet
                            ->setCellValue("D{$write_position}", $value->ship_postal_code);

                        $city = 1;
                        $city = $city + $write_position;
                        $event->sheet
                            ->setCellValue("D{$city}", $value->ship_state);

                        $address_1 = 2;
                        $address_1 = $address_1 + $write_position;
                        $event->sheet
                            ->setCellValue("D{$address_1}", $value->ship_address_1);

                        $address_2 = 3;
                        $address_2 = $address_2 + $write_position;
                        $event->sheet
                            ->setCellValue("D{$address_2}", $value->ship_address_2);

                        $address_3 = 4;
                        $address_3 = $address_3 + $write_position;
                        $event->sheet
                            ->setCellValue("D{$address_3}", $value->ship_address_3);

                        $name = 6;
                        $name = $name + $write_position;
                        $event->sheet
                            ->setCellValue("D{$name}", $value->recipient_name . '様');

                        $tel = 7;
                        $tel = $tel + $write_position;
                        $event->sheet
                            ->setCellValue("D{$tel}", $value->buyer_phone_number);
                        // 広げる
                        $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(22);
                        $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(22);

                    }
                }
                $highestRow = $sheet->getHighestRow(); // データ最終行を自動取得
                $sheet->getPageSetup()->setPrintArea("A1:F{$highestRow}");
            }
        ];
    }
}
