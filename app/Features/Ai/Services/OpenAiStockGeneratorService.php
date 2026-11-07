<?php

namespace App\Features\Ai\Services;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedStockItem;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiStockGeneratorService implements AiClientInterface
{
    public function generate(string $prompt, int $count = 5): array
    {
        $apiKey = config('openai.api_key');

        if (! $apiKey) {
            throw new RuntimeException('OPENAI_API_KEY belum dikonfigurasi.');
        }

        $count = max(1, min(20, $count));

        $response = Http::withToken($apiKey)
            ->timeout((int) config('openai.timeout'))
            ->post(rtrim((string) config('openai.base_url'), '/').'/chat/completions', [
                'model' => config('openai.model'),
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Anda asisten inventaris. Balas HANYA JSON berbentuk {"items":[{"name","sku","description","quantity","unit","price","min_stock"}]}. '
                            ."Hasilkan tepat {$count} item inventaris/stok barang yang realistis dalam bahasa Indonesia. Harga dalam Rupiah.",
                    ],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Permintaan ke OpenAI gagal: HTTP '.$response->status());
        }

        $content = $response->json('choices.0.message.content');
        $decoded = is_string($content) ? json_decode($content, true) : null;

        if (! is_array($decoded) || ! isset($decoded['items']) || ! is_array($decoded['items'])) {
            throw new RuntimeException('Respons OpenAI tidak valid.');
        }

        $items = [];
        foreach (array_slice($decoded['items'], 0, $count) as $item) {
            if (is_array($item)) {
                $items[] = GeneratedStockItem::fromArray($item);
            }
        }

        return $items;
    }
}
