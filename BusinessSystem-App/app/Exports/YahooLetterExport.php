<?php

namespace App\Exports;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style;

class YahooLetterExport implements WithEvents
{
    private $data;

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        //
    }

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->getSheetView()->setZoomScale(160);

                Log::info("registerEvents Yahoo Data");
                Log::info($this->data);

                //$count = count($this->data);
                $count = collect($this->data)->reduce(function ($carry, $item) {
                    $arr = [];
                    if (!empty($item->QuantityDetail)) {
                        parse_str($item->QuantityDetail, $arr); // L1=1&L2=2 → ['L1'=>1,'L2'=>2]
                    }
                    return $carry + count($arr);
                }, 0);


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
                $key_base = 0;

                foreach ($this->data as $key => $value) {
//dd($value);
                    // QuantityDetail Collection の中身は1件の文字列として入っている
                    $qtyString = $value["QuantityDetail"] ?? '';

                    // L1=1&L2=1 の形を分割して配列に変換
                    $qtyArray = collect(explode('&', $qtyString))->mapWithKeys(function ($item) {
                        [$k, $v] = explode('=', $item);
                        return [$k => $v];
                    });

                    foreach ($qtyArray as $detailKey => $detailValue) {

                        // ---- ここから元のロジックに組み込む ----
                        // 左右切替判定（書く前に判定）
                        $isLeft = ($key_base % 2 == 0);
                        $col = $isLeft ? 'A' : 'D';
                        // プリント位置分ずらす
                        if ($key_base % 10 == 0 && $key_base !== 0) {
                            $write_position += 8;
                        }

                        // --- 住所系共通処理 ---
                        $event->sheet->setCellValue("{$col}{$write_position}", $value->ShipZipCode);
                        $event->sheet->setCellValue("{$col}" . ($write_position + 1), $value->ShipPrefecture);
                        $event->sheet->setCellValue("{$col}" . ($write_position + 2), $value->ShipCity);
                        $event->sheet->setCellValue("{$col}" . ($write_position + 3), $value->ShipAddress1);
                        $event->sheet->setCellValue("{$col}" . ($write_position + 4), $value->ShipAddress2);

                        $event->sheet->setCellValue("{$col}" . ($write_position + 6), $value->ShipName . '様');
                        $event->sheet->setCellValue("{$col}" . ($write_position + 7), $value->ShipPhoneNumber);

                        // 列幅調整
                        $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(22);
                        $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(22);

                        // ---- ここで次の書き込み位置を進める ----
                        // 右側を書いたら次の行へ
                        if (!$isLeft) {
                            $write_position += 8;
                        }

                        // ページ印刷範囲更新
                        $highestRow = $sheet->getHighestRow();
                        $sheet->getPageSetup()->setPrintArea("A1:F{$highestRow}");
                        $key_base++;
                    }
                }
            }
        ];
    }
}
