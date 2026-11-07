<?php

namespace Tests\Feature\Stock;

use App\Features\Stock\Livewire\StockDetail;
use App\Features\Stock\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class StockDetailLivewireTest extends TestCase
{
    use RefreshDatabase;

    private User $member;

    private Stock $stock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->member = User::factory()->create();
        $this->stock = Stock::factory()->for($this->member)->withQuantity(10)->create(['description' => '**tebal**']);
        $this->actingAs($this->member);
    }

    public function test_renders_markdown_description(): void
    {
        Livewire::test(StockDetail::class, ['stock' => $this->stock])
            ->assertSeeHtml('<strong>tebal</strong>');
    }

    public function test_markdown_strips_raw_html(): void
    {
        $this->stock->update(['description' => '<script>alert(1)</script>ok']);

        Livewire::test(StockDetail::class, ['stock' => $this->stock->refresh()])
            ->assertDontSeeHtml('<script>alert(1)</script>');
    }

    public function test_other_user_cannot_view(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(StockDetail::class, ['stock' => $this->stock])->assertForbidden();
    }

    public function test_admin_can_view_but_not_modify(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(StockDetail::class, ['stock' => $this->stock])
            ->assertOk()
            ->call('startEdit')->assertForbidden();
    }

    public function test_edit_save_and_cancel(): void
    {
        Livewire::test(StockDetail::class, ['stock' => $this->stock])
            ->call('startEdit')->assertSet('editing', true)
            ->call('cancelEdit')->assertSet('editing', false)
            ->call('startEdit')
            ->set('name', 'Diubah')->set('quantity', 2)
            ->call('save')->assertHasNoErrors()->assertSet('editing', false);

        $this->assertSame('Diubah', $this->stock->refresh()->name);
        $this->assertSame('low_stock', $this->stock->status->value);
    }

    public function test_save_validates(): void
    {
        Livewire::test(StockDetail::class, ['stock' => $this->stock])
            ->call('startEdit')->set('name', '')->call('save')->assertHasErrors('name');
    }

    public function test_adjust_quantity(): void
    {
        Livewire::test(StockDetail::class, ['stock' => $this->stock])
            ->call('adjust', 5)->assertSet('quantity', 15);

        $this->assertSame(15, $this->stock->refresh()->quantity);
    }

    public function test_upload_and_remove_cover(): void
    {
        Storage::fake('public');

        Livewire::test(StockDetail::class, ['stock' => $this->stock])
            ->set('cover', UploadedFile::fake()->image('cover.png'))
            ->call('saveCover')->assertHasNoErrors();

        $path = $this->stock->refresh()->cover_image_path;
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);

        Livewire::test(StockDetail::class, ['stock' => $this->stock])->call('removeCover');
        $this->assertNull($this->stock->refresh()->cover_image_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_cover_must_be_image(): void
    {
        Livewire::test(StockDetail::class, ['stock' => $this->stock])
            ->set('cover', UploadedFile::fake()->create('x.pdf', 10, 'application/pdf'))
            ->call('saveCover')->assertHasErrors('cover');
    }

    public function test_delete_redirects_to_list_and_removes_cover(): void
    {
        Storage::fake('public');
        $path = UploadedFile::fake()->image('c.png')->store('stock-covers', 'public');
        $this->stock->update(['cover_image_path' => $path]);

        Livewire::test(StockDetail::class, ['stock' => $this->stock->refresh()])
            ->call('delete')->assertRedirect('/stocks');

        $this->assertDatabaseMissing('stocks', ['id' => $this->stock->id]);
        Storage::disk('public')->assertMissing($path);
    }
}
