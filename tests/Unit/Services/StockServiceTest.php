<?php

namespace Tests\Unit\Services;

use App\Features\Ai\DTO\GeneratedStockItem;
use App\Features\Stock\Models\Stock;
use App\Features\Stock\Repositories\StockRepository;
use App\Features\Stock\Services\StockService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StockService(new StockRepository);
    }

    public function test_create_normalizes_and_derives_status(): void
    {
        $user = User::factory()->create();

        $stock = $this->service->create($user, [
            'name' => 'Baut', 'sku' => '', 'description' => '', 'quantity' => '3',
            'unit' => '', 'price' => '', 'min_stock' => '5',
        ]);

        $this->assertNull($stock->sku);
        $this->assertNull($stock->description);
        $this->assertNull($stock->price);
        $this->assertSame('pcs', $stock->unit);
        $this->assertSame('low_stock', $stock->status->value);
        $this->assertSame($user->id, $stock->user_id);
    }

    public function test_create_with_missing_optional_keys(): void
    {
        $stock = $this->service->create(User::factory()->create(), ['name' => 'Minimal']);

        $this->assertSame(0, $stock->quantity);
        $this->assertSame('out_of_stock', $stock->status->value);
        $this->assertSame(5, $stock->min_stock);
    }

    public function test_list_find_update(): void
    {
        $user = User::factory()->create();
        $stock = Stock::factory()->for($user)->withQuantity(20)->create();

        $this->assertSame(1, $this->service->list($user)->total());
        $this->assertSame($stock->id, $this->service->find($stock->id)->id);

        $updated = $this->service->update($stock, [
            'name' => 'Baru', 'quantity' => 1, 'min_stock' => 5, 'price' => '1234.50',
        ]);
        $this->assertSame('low_stock', $updated->status->value);
        $this->assertSame('1234.50', $updated->price);
    }

    public function test_adjust_quantity_clamps_at_zero(): void
    {
        $stock = Stock::factory()->withQuantity(2)->create();

        $this->assertSame(0, $this->service->adjustQuantity($stock, -10)->quantity);
        $this->assertSame(7, $this->service->adjustQuantity($stock->refresh(), 7)->quantity);
    }

    public function test_cover_lifecycle_and_delete(): void
    {
        Storage::fake('public');
        $stock = Stock::factory()->create();

        $stock = $this->service->storeCover($stock, UploadedFile::fake()->image('a.png'));
        $first = $stock->cover_image_path;
        Storage::disk('public')->assertExists($first);

        $stock = $this->service->storeCover($stock, UploadedFile::fake()->image('b.png'));
        Storage::disk('public')->assertMissing($first);

        $second = $stock->cover_image_path;
        $stock = $this->service->removeCover($stock);
        $this->assertNull($stock->cover_image_path);
        Storage::disk('public')->assertMissing($second);

        $stock = $this->service->storeCover($stock, UploadedFile::fake()->image('c.png'));
        $path = $stock->cover_image_path;
        $this->assertTrue($this->service->delete($stock));
        Storage::disk('public')->assertMissing($path);
    }

    public function test_delete_without_cover(): void
    {
        $this->assertTrue($this->service->delete(Stock::factory()->create()));
    }

    public function test_create_from_generated(): void
    {
        $user = User::factory()->create();

        $count = $this->service->createFromGenerated($user, [
            GeneratedStockItem::fromArray(['name' => 'A', 'quantity' => 1]),
            GeneratedStockItem::fromArray(['name' => 'B', 'quantity' => 50]),
        ]);

        $this->assertSame(2, $count);
        $this->assertSame(2, $user->stocks()->count());
    }
}
