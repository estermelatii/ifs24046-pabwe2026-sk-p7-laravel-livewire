<?php

namespace Tests\Unit\Policies;

use App\Features\Stock\Models\Stock;
use App\Features\Stock\Policies\StockPolicy;
use App\Models\User;
use Tests\TestCase;

class StockPolicyTest extends TestCase
{
    public function test_policy_rules(): void
    {
        $policy = new StockPolicy;
        $owner = new User(['role' => 'member']);
        $owner->id = 1;
        $other = new User(['role' => 'member']);
        $other->id = 2;
        $admin = new User(['role' => 'admin']);
        $admin->id = 3;
        $stock = new Stock(['user_id' => 1]);

        $this->assertTrue($policy->viewAny($owner));
        $this->assertTrue($policy->view($owner, $stock));
        $this->assertTrue($policy->view($admin, $stock));
        $this->assertFalse($policy->view($other, $stock));

        $this->assertTrue($policy->create($owner));
        $this->assertFalse($policy->create($admin));

        $this->assertTrue($policy->update($owner, $stock));
        $this->assertFalse($policy->update($other, $stock));
        $this->assertFalse($policy->update($admin, $stock));

        $this->assertTrue($policy->delete($owner, $stock));
        $this->assertFalse($policy->delete($admin, $stock));
    }
}
