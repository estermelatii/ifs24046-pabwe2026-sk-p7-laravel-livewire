<?php

namespace App\Features\Stock\Services;

use App\Features\Stock\Contracts\StockRepositoryInterface;
use App\Features\Stock\Enums\StockStatus;
use App\Models\User;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class StockExcelService
{
    public const HEADERS = ['name', 'sku', 'quantity', 'unit', 'price', 'min_stock', 'status', 'description'];

    public function __construct(private readonly StockRepositoryInterface $stocks) {}

    /**
     * Hasilkan isi file .xlsx (binary) dari koleksi stok.
     */
    public function export(Collection $stocks): string
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Stock');
        $sheet->fromArray(self::HEADERS, null, 'A1');

        $row = 2;
        foreach ($stocks as $stock) {
            $sheet->fromArray([
                $stock->name,
                $stock->sku,
                $stock->quantity,
                $stock->unit,
                $stock->price,
                $stock->min_stock,
                $stock->status->value,
                $stock->description,
            ], null, 'A'.$row);
            $row++;
        }

        $tmp = tempnam(sys_get_temp_dir(), 'stk');
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($tmp);
        $content = (string) file_get_contents($tmp);
        unlink($tmp);

        return $content;
    }

    /**
     * Import baris dari file xlsx ke stok milik user. Mengembalikan jumlah baris yang tersimpan.
     */
    public function import(User $user, string $path): int
    {
        // Reader Xlsx dipakai langsung: IOFactory::load() akan menerima file teks apa pun sebagai CSV.
        $rows = (new XlsxReader)->load($path)->getActiveSheet()->toArray(null, true, true, false);
        array_shift($rows);

        $count = 0;
        foreach ($rows as $row) {
            $name = trim((string) ($row[0] ?? ''));
            if ($name === '') {
                continue;
            }

            $quantity = max(0, (int) ($row[2] ?? 0));
            $min = isset($row[5]) && $row[5] !== '' ? max(0, (int) $row[5]) : 5;

            $this->stocks->create([
                'user_id' => $user->id,
                'name' => $name,
                'sku' => ($row[1] ?? null) !== '' ? $row[1] : null,
                'quantity' => $quantity,
                'unit' => ($row[3] ?? null) ?: 'pcs',
                'price' => isset($row[4]) && $row[4] !== '' ? $row[4] : null,
                'min_stock' => $min,
                'status' => StockStatus::fromQuantity($quantity, $min)->value,
                'description' => ($row[7] ?? null) !== '' ? ($row[7] ?? null) : null,
            ]);
            $count++;
        }

        return $count;
    }
}
