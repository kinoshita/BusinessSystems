<?php

namespace App\Exports;

use App\Models\AmazonItem;
use Carbon\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style;
use PhpOffice\PhpSpreadsheet\Reader\Xml\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AmazonExport implements WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */
    /*
        public function collection()
        {
            //
            //return AmazonItem::where();
            return AmazonItem::all();
        }
    */
    /*
        public function view(): View
        {

        }
    */
    public function __construct($data)
    {
        $this->tour_id = '1';
        $this->name = 'test';

        $this->data = $data;
    }


    public function styles(Worksheet $sheet)
    {

    }

    /*
    public function headings():array{
        return [
            'tour_id',
            'name'
        ];
    }
*/
    public function registerEvents(): array
    {
        // TODO: Implement registerEvents() method.
        return [
            AfterSheet::class => function (AfterSheet $event) {


                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(4.0); // A列を幅8に
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(4.0); // B列を幅10に


                $line = 1;
                $now = Carbon::now();
                $date = $now->format('Y年m月d日');
                $event->sheet->setCellValue("B{$line}", $date);
                $event->sheet->getStyle("B{$line}")->getFont()->setBold(true)->setSize(18);
                $list_name = '出荷リスト(amazon)';
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
                        ->setCellValue("C{$count}", $value->buyer_name);
                    $event->sheet
                        ->setCellValue("E{$count}", $value->recipient_name);
                    $event->sheet
                        ->setCellValue("F{$count}", $value->quantity_to_ship);

                    $product_name = preg_replace('/マグネット式電源コードタイプ ピュアポット /', '', $value->product_name);

                    $event->sheet
                        ->setCellValue("G{$count}", $product_name);
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
