<?php

namespace Tests\Unit\Services;

use App\Features\Ai\DTO\GeneratedStockItem;
use App\Features\Ai\Services\OpenAiStockGeneratorService;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class OpenAiStockGeneratorServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['openai.api_key' => 'test-key', 'openai.base_url' => 'https://api.test/v1']);
    }

    private function fakeContent(string $content, int $status = 200): void
    {
        Http::fake(['api.test/*' => Http::response(
            ['choices' => [['message' => ['content' => $content]]]],
            $status
        )]);
    }

    public function test_parses_items(): void
    {
        $this->fakeContent(json_encode(['items' => [
            ['name' => 'Spidol', 'quantity' => 12, 'unit' => 'pcs', 'price' => 5000],
            'bukan-array',
            ['name' => 'Penghapus', 'quantity' => 3],
        ]]));

        $items = (new OpenAiStockGeneratorService)->generate('alat tulis', 5);

        $this->assertCount(2, $items);
        $this->assertContainsOnlyInstancesOf(GeneratedStockItem::class, $items);
        $this->assertSame('Spidol', $items[0]->name);
        Http::assertSent(fn ($r) => $r->hasHeader('Authorization', 'Bearer test-key')
            && $r['model'] === config('openai.model'));
    }

    public function test_count_is_clamped_and_items_limited(): void
    {
        $this->fakeContent(json_encode(['items' => [['name' => 'A'], ['name' => 'B']]]));

        $this->assertCount(1, (new OpenAiStockGeneratorService)->generate('x', 0));
    }

    public function test_missing_api_key_throws(): void
    {
        config(['openai.api_key' => null]);
        $this->expectException(RuntimeException::class);

        (new OpenAiStockGeneratorService)->generate('x');
    }

    public function test_http_failure_throws(): void
    {
        $this->fakeContent('{}', 500);
        $this->expectException(RuntimeException::class);

        (new OpenAiStockGeneratorService)->generate('x');
    }

    public function test_invalid_json_throws(): void
    {
        $this->fakeContent('bukan json');
        $this->expectException(RuntimeException::class);

        (new OpenAiStockGeneratorService)->generate('x');
    }
}
