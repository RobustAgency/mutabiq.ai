<?php

namespace Tests\Feature\Actions\Stripe;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Actions\Stripe\UpgradeSubscription;
use Mockery;
use App\Models\User;
use App\Models\Plan;
use Laravel\Cashier\Subscription;

class UpgradeSubscriptionTest extends TestCase
{
    use WithFaker;
    private UpgradeSubscription $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new UpgradeSubscription();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_upgrades_subscription()
    {
        $user = Mockery::mock(User::class);
        $subscription = Mockery::mock(Subscription::class);
        $plan = Plan::factory()->make([
            'stripe_price_id' => 'price_pro_123'
        ]);

        // Set up expectations
        $user->shouldReceive('subscription')
            ->once()
            ->with('default')
            ->andReturn($subscription);

        $subscription->shouldReceive('swap')
            ->once()
            ->with('price_pro_123');

        $subscription->shouldReceive('fresh')
            ->once()
            ->andReturnSelf();

        $subscription->shouldReceive('getAttribute')
            ->with('stripe_price')
            ->andReturn('price_pro_123');

        // Act
        $result = $this->action->execute($user, $plan);

        // Assert
        $this->assertTrue($result);
    }

    public function test_it_returns_false_if_subscription_does_not_update()
    {
        $user = Mockery::mock(User::class);
        $subscription = Mockery::mock(Subscription::class);
        $plan = Plan::factory()->make([
            'stripe_price_id' => 'price_pro_123'
        ]);

        // Set up expectations
        $user->shouldReceive('subscription')
            ->once()
            ->with('default')
            ->andReturn($subscription);

        $subscription->shouldReceive('swap')
            ->once()
            ->with('price_pro_123');

        $subscription->shouldReceive('fresh')
            ->once()
            ->andReturnSelf();

        $subscription->shouldReceive('getAttribute')
            ->with('stripe_price')
            ->andReturn('price_basic_456');

        $result = $this->action->execute($user, $plan);

        $this->assertFalse($result);
    }
}
