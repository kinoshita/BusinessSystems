<?php

namespace App\Exports;

use App\Models\AmazonItem;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
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
                $line = 1;
                $date = '09月22日(日)';
                $event->sheet->setCellValue("B{$line}", $date);
                $list_name = '出荷リスト(amazon)';

                $event->sheet->setCellValue("E{$line}", $list_name);
                // ヘッダ

                $count = 4;
                $before_count = $count;
                $total_count = 1;

                $event->sheet->setCellValue("C3", "buyer_name");
                $event->sheet->setCellValue("E3", "recipient_name");
                $event->sheet->setCellValue("L3", "quantity_to_ship");
                $event->sheet->setCellValue("M3", "product_name");

                $type_count['1'] = 1;
                $type_count['2'] = 1;
                $type_count['3'] = 1;
                $type_count['4'] = 1;
                $type_count['9'] = 1;

                $before_value = "";

                foreach($this->data as $key=>$value){

                    if($value->type == '1'){

                        $inner_count = $type_count['1'];
                    }elseif($value->type == '2'){
                        $inner_count = $type_count['2'];
                    }elseif($value->type == '3'){
                        $inner_count = $type_count['3'];
                    }elseif($value->type == '4'){
                        $inner_count = $type_count['4'];
                    }else{
                        $inner_count = $type_count['9'];
                    }
                    if($value->type != $before_value && $count != 4){
                        $count++;
                    }
                    $before_value = $value->type;
                    $event->sheet
                        ->setCellValue("A{$count}", $total_count);
                    $event->sheet
                        ->setCellValue("B{$count}", $inner_count);



                    $event->sheet
                        ->setCellValue("C{$count}", $value->buyer_name);
                    $event->sheet
                        ->setCellValue("E{$count}", $value->recipient_name);
                    $event->sheet
                        ->setCellValue("L{$count}", $value->quantity_to_ship);
                    $event->sheet
                        ->setCellValue("M{$count}", $value->product_name);
                    $count++;
                    $total_count++;
                    if($value->type == '1'){
                        $type_count['1'] += 1;
                    }elseif($value->type == '2'){
                        $type_count['2'] += 1;
                    }elseif($value->type == '3'){
                        $type_count['3'] += 1;
                    }elseif($value->type == '4'){
                        $type_count['4'] += 1;
                    }else{
                        $type_count['9'] += 1;
                    }
                }


            }
        ];
    }


}
