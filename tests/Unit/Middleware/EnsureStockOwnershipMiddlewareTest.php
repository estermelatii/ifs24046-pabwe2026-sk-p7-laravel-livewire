<?php

namespace Tests\Unit\Middleware;

use App\Features\Stock\Models\Stock;
use App\Http\Middleware\EnsureStockOwnership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsureStockOwnershipMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private function invoke(?User $user, mixed $param, bool $withParam = true): Response
    {
        $request = Request::create('/stocks/1');
        $route = new Route('GET', '/stocks/{stock}', fn () => 'x');
        if ($withParam) {
            $route->bind($request);
            $route->setParameter('stock', $param);
        }
        $request->setRouteResolver(fn () => $route);
        $request->setUserResolver(fn () => $user);

        return (new EnsureStockOwnership)->handle($request, fn () => new Response('ok'));
    }

    public function test_owner_with_model_or_id_passes(): void
    {
        $owner = User::factory()->create();
        $stock = Stock::factory()->for($owner)->create();

        $this->assertSame('ok', $this->invoke($owner, $stock)->getContent());
        $this->assertSame('ok', $this->invoke($owner, (string) $stock->id)->getContent());
    }

    public function test_non_owner_forbidden(): void
    {
        $stock = Stock::factory()->create();

        try {
            $this->invoke(User::factory()->create(), $stock);
            $this->fail('Expected 403');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    public function test_unknown_stock_is_404(): void
    {
        try {
            $this->invoke(User::factory()->create(), '999999');
            $this->fail('Expected 404');
        } catch (HttpException $e) {
            $this->assertSame(404, $e->getStatusCode());
        }
    }

    public function test_guest_forbidden(): void
    {
        $this->expectException(HttpException::class);
        $this->invoke(null, Stock::factory()->create());
    }

    public function test_without_route_parameter_passes_through(): void
    {
        $request = Request::create('/stocks');
        $route = new Route('GET', '/stocks', fn () => 'x');
        $route->bind($request);
        $request->setRouteResolver(fn () => $route);

        $this->assertSame('ok', (new EnsureStockOwnership)->handle($request, fn () => new Response('ok'))->getContent());
    }
}
