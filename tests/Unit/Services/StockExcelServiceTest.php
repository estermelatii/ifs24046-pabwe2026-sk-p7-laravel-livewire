<?php

namespace Tests\Unit\Services;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Repositories\StockRepository;
use App\Features\Stock\Services\StockExcelService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

class StockExcelServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): StockExcelService
    {
        return new StockExcelService(new StockRepository);
    }

    public function test_export_contains_header_and_rows(): void
    {
        $user = User::factory()->create();
        Stock::factory()->for($user)->create(['name' => 'Eksport 1', 'sku' => 'E-1']);

        $binary = $this->service()->export($user->stocks()->get());

        $path = tempnam(sys_get_temp_dir(), 'exp');
        file_put_contents($path, $binary);
        $rows = IOFactory::load($path)->getActiveSheet()->toArray();
        unlink($path);

        $this->assertSame(StockExcelService::HEADERS, $rows[0]);
        $this->assertSame('Eksport 1', $rows[1][0]);
        $this->assertSame('E-1', $rows[1][1]);
    }

    public function test_import_skips_blank_names_and_applies_defaults(): void
    {
        $user = User::factory()->create();
        $sheet = new Spreadsheet;
        $sheet->getActiveSheet()->fromArray([
            StockExcelService::HEADERS,
            ['Lengkap', 'S-1', 3, 'box', 2500, 10, '', 'keterangan'],
            ['Minimal', '', '', '', '', '', '', ''],
            ['', 'X', 5, '', '', '', '', ''],
        ], null, 'A1');
        $path = tempnam(sys_get_temp_dir(), 'imp');
        IOFactory::createWriter($sheet, 'Xlsx')->save($path);

        $count = $this->service()->import($user, $path);
        unlink($path);

        $this->assertSame(2, $count);

        $full = Stock::where('name', 'Lengkap')->first();
        $this->assertSame('low_stock', $full->status->value);
        $this->assertSame('S-1', $full->sku);
        $this->assertSame('keterangan', $full->description);
        $this->assertSame(10, $full->min_stock);

        $min = Stock::where('name', 'Minimal')->first();
        $this->assertNull($min->sku);
        $this->assertNull($min->price);
        $this->assertNull($min->description);
        $this->assertSame('pcs', $min->unit);
        $this->assertSame(5, $min->min_stock);
        $this->assertSame('out_of_stock', $min->status->value);
    }
}
