<?php

namespace Tests\Feature\Stock;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedStockItem;
use App\Features\Stock\Livewire\StockList;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

class StockListLivewireTest extends TestCase
{
    use RefreshDatabase;

    private User $member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->member = User::factory()->create();
        $this->actingAs($this->member);
    }

    public function test_lists_only_own_stocks_with_search_and_filter(): void
    {
        Stock::factory()->for($this->member)->withQuantity(50)->create(['name' => 'Kertas']);
        Stock::factory()->for($this->member)->withQuantity(0)->create(['name' => 'Tinta']);
        Stock::factory()->for($this->member)->withQuantity(40)->create(['name' => 'Map Folder']);
        Stock::factory()->create(['name' => 'Milik Orang Lain']);

        // 'Tinta' habis, jadi selalu tampil di panel "Perlu diisi ulang"; uji pencarian/filter memakai barang tersedia.
        Livewire::test(StockList::class)
            ->assertSee('Kertas')->assertSee('Tinta')->assertSee('Map Folder')->assertDontSee('Milik Orang Lain')
            ->set('search', 'Kertas')->assertSee('Kertas')->assertDontSee('Map Folder')
            ->set('search', '')
            ->set('statusFilter', 'out_of_stock')->assertSee('Tinta')->assertDontSee('Kertas')->assertDontSee('Map Folder');
    }

    public function test_create_stock_and_status_is_derived(): void
    {
        Livewire::test(StockList::class)
            ->call('create')->assertSet('showForm', true)
            ->set('name', 'Pulpen')->set('sku', 'P-1')->set('quantity', 3)
            ->set('unit', 'pcs')->set('price', 2500)->set('min_stock', 5)
            ->call('save')->assertHasNoErrors()->assertSet('showForm', false);

        $this->assertDatabaseHas('stocks', [
            'user_id' => $this->member->id, 'name' => 'Pulpen', 'status' => 'low_stock',
        ]);
    }

    public function test_create_validates_input(): void
    {
        Livewire::test(StockList::class)
            ->call('create')->set('name', '')->set('quantity', -1)
            ->call('save')->assertHasErrors(['name', 'quantity']);
    }

    public function test_edit_and_update_stock(): void
    {
        $stock = Stock::factory()->for($this->member)->withQuantity(20)->create(['name' => 'Lama']);

        Livewire::test(StockList::class)
            ->call('edit', $stock->id)
            ->assertSet('name', 'Lama')->assertSet('editingId', $stock->id)
            ->set('name', 'Baru')->set('quantity', 0)
            ->call('save')->assertHasNoErrors();

        $stock->refresh();
        $this->assertSame('Baru', $stock->name);
        $this->assertSame('out_of_stock', $stock->status->value);
    }

    public function test_cannot_edit_or_delete_other_users_stock(): void
    {
        $foreign = Stock::factory()->create();

        Livewire::test(StockList::class)->call('edit', $foreign->id)->assertForbidden();
        Livewire::test(StockList::class)->call('delete', $foreign->id)->assertForbidden();
        Livewire::test(StockList::class)->call('adjust', $foreign->id, 1)->assertForbidden();
        $this->assertDatabaseHas('stocks', ['id' => $foreign->id]);
    }

    public function test_missing_stock_returns_not_found(): void
    {
        Livewire::test(StockList::class)->call('delete', 999999)->assertNotFound();
    }

    public function test_adjust_quantity_updates_status_and_never_negative(): void
    {
        $stock = Stock::factory()->for($this->member)->withQuantity(6, 5)->create();

        Livewire::test(StockList::class)->call('adjust', $stock->id, -1);
        $this->assertSame('low_stock', $stock->refresh()->status->value);

        Livewire::test(StockList::class)->call('adjust', $stock->id, -100);
        $this->assertSame(0, $stock->refresh()->quantity);
        $this->assertSame('out_of_stock', $stock->status->value);

        Livewire::test(StockList::class)->call('adjust', $stock->id, 50);
        $this->assertSame('available', $stock->refresh()->status->value);
    }

    public function test_delete_own_stock(): void
    {
        $stock = Stock::factory()->for($this->member)->create();

        Livewire::test(StockList::class)->call('delete', $stock->id);

        $this->assertDatabaseMissing('stocks', ['id' => $stock->id]);
    }

    public function test_close_form_resets_state(): void
    {
        Livewire::test(StockList::class)
            ->call('create')->set('name', 'X')->call('closeForm')
            ->assertSet('showForm', false)->assertSet('name', '');
    }

    public function test_export_downloads_xlsx(): void
    {
        Stock::factory()->for($this->member)->create(['name' => 'Eksport']);

        Livewire::test(StockList::class)->call('export')->assertFileDownloaded('stok-barang.xlsx');
    }

    public function test_import_xlsx(): void
    {
        $sheet = new Spreadsheet;
        $sheet->getActiveSheet()->fromArray([
            ['name', 'sku', 'quantity', 'unit', 'price', 'min_stock', 'status', 'description'],
            ['Impor A', 'IA-1', 10, 'box', 1000, 5, '', 'desk'],
        ], null, 'A1');
        $path = tempnam(sys_get_temp_dir(), 'imp').'.xlsx';
        IOFactory::createWriter($sheet, 'Xlsx')->save($path);

        $file = UploadedFile::fake()->createWithContent('stok.xlsx', file_get_contents($path));

        Livewire::test(StockList::class)->set('importFile', $file)->call('import')->assertHasNoErrors();

        $this->assertDatabaseHas('stocks', ['name' => 'Impor A', 'user_id' => $this->member->id]);
        unlink($path);
    }

    public function test_import_rejects_invalid_file(): void
    {
        $file = UploadedFile::fake()->createWithContent('rusak.xlsx', 'bukan excel');

        Livewire::test(StockList::class)->set('importFile', $file)->call('import')->assertHasErrors('importFile');
    }

    public function test_ai_generate_preview_select_and_save(): void
    {
        $stub = $this->createStub(AiClientInterface::class);
        $stub->method('generate')->willReturn([
            GeneratedStockItem::fromArray(['name' => 'AI Satu', 'quantity' => 4]),
            GeneratedStockItem::fromArray(['name' => 'AI Dua', 'quantity' => 9]),
        ]);
        $this->app->instance(AiClientInterface::class, $stub);

        Livewire::test(StockList::class)
            ->call('openAi')->assertSet('showAi', true)
            ->set('aiPrompt', 'alat tulis')->set('aiCount', 2)
            ->call('generate')->assertHasNoErrors()
            ->assertSee('AI Satu')->assertSee('AI Dua')
            ->set('aiSelected', [1])
            ->call('saveGenerated')->assertSet('showAi', false);

        $this->assertDatabaseHas('stocks', ['name' => 'AI Dua', 'user_id' => $this->member->id]);
        $this->assertDatabaseMissing('stocks', ['name' => 'AI Satu']);
    }

    public function test_ai_failure_is_reported(): void
    {
        $stub = $this->createStub(AiClientInterface::class);
        $stub->method('generate')->willThrowException(new \RuntimeException('boom'));
        $this->app->instance(AiClientInterface::class, $stub);

        Livewire::test(StockList::class)
            ->set('aiPrompt', 'x')->call('generate')->assertHasErrors('aiPrompt');
    }

    public function test_ai_validation_and_close(): void
    {
        Livewire::test(StockList::class)
            ->call('generate')->assertHasErrors('aiPrompt')
            ->set('aiPrompt', 'abc')->call('closeAi')->assertSet('aiPrompt', '');
    }

    public function test_admin_cannot_create(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(StockList::class)->call('create')->assertForbidden();
    }
}
