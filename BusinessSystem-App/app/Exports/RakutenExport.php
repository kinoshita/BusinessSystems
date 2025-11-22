<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style;
class RakutenExport implements WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($data)
    {
        $this->data = $data;
    }


    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                // 印刷範囲を指定（例: A1～H40までを印刷範囲にする）
                $event->sheet->getPageSetup()->setPrintArea("A1:L58");

                // 印刷倍率を80%に設定
                $event->sheet->getDelegate()->getPageSetup()->setScale(80);

                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(4.0); // A列を幅8に
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(4.0); // B列を幅10に


                $line = 1;
                $now = Carbon::now();
                $date = $now->format('Y年m月d日');
                //$event->sheet->setCellValue("B{$line}:C{$line}", $date);

                $event->sheet->getDelegate()->mergeCells("B{$line}:D{$line}");
                $event->sheet->setCellValue("B{$line}", $date);

                $event->sheet->getStyle("B{$line}")->getFont()->setBold(true)->setSize(18);
                $list_name = '出荷リスト(楽天)';
                $event->sheet->getDelegate()->getStyle("A3:Z3")->applyFromArray([
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);


                $event->sheet->setCellValue("E{$line}", $list_name);
                // ヘッダ

                $count = 4;
                $before_count = $count;
                $total_count = 1;

                $event->sheet->setCellValue("C3", "buyer_name");
                $event->sheet->setCellValue("E3", "recipient_name");
                $event->sheet->setCellValue("F3", "quantity_to_ship");
                $event->sheet->setCellValue("G3", "product_name");

                $type_count['1'] = 1;
                $type_count['2'] = 1;
                $type_count['3'] = 1;

                $type_count['10'] = 1;
                $type_count['11'] = 1;

                $type_count['20'] = 1;
                $type_count['21'] = 1;
                $type_count['22'] = 1;
                $type_count['30'] = 1;

                $before_value = "";



                $recipient_counts = collect($this->data)->groupBy('recipient_name')->map->count();
                $duplicates = $recipient_counts->filter(fn($count) => $count > 1)->keys();


                foreach ($this->data as $key => $value) {

                    if ($value->file_type == '1') {
                        $inner_count = $type_count['1'];
                    } elseif ($value->file_type == '2') {
                        $inner_count = $type_count['2'];
                    } elseif ($value->file_type == '3') {
                        $inner_count = $type_count['3'];
                    } else {
                        $inner_count = $type_count['3'];
                    }

                    if ($value->file_type != $before_value && $count != 4) {
                        $count++;
                    }
                    $before_value = $value->file_type;
                    $event->sheet
                        ->setCellValue("A{$count}", $total_count);
                    $event->sheet
                        ->setCellValue("B{$count}", $inner_count);


                    $event->sheet
                        ->setCellValue("C{$count}", $value->order_last_name . $value->order_first_name);
                    $event->sheet
                        ->setCellValue("E{$count}", $value->destination_last_name. $value->destination_first_name);

                    if ($duplicates->contains($value->destination_last_name.$value->destination_first_name)) {
                        $event->sheet->getStyle("E{$count}")
                            ->applyFromArray([
                                'font' => [
                                    'color' => ['rgb' => 'FF0000'],
                                ],
                            ]);
                    }




                    $event->sheet
                        ->setCellValue("F{$count}", $value->quantity);

                    //$product_name =
                    // preg_replace('/マグネット式電源コードタイプ ピュアポット /', '', $value->product_name);

                    $event->sheet
                        ->setCellValue("G{$count}", $value->product_name);
                    $count++;
                    $total_count++;
                    if ($value->file_type == '1') {
                        $type_count['1'] += 1;
                    } elseif ($value->file_type == '2') {
                        $type_count['2'] += 1;
                    } elseif ($value->file_type == '3') {
                        $type_count['3'] += 1;
                    }  else {
                        $type_count['3'] += 1;
                    }
                }

            }
        ];
    }
}
