<?php

namespace Tests\Feature\Actions\Stripe;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Actions\Stripe\DowngradeSubscription;
use Mockery;
use App\Models\User;
use App\Models\Plan;
use Laravel\Cashier\Subscription;
use PHPUnit\Framework\Attributes\Test;

class DowngradeSubscriptionTest extends TestCase
{
    use WithFaker;
    private DowngradeSubscription $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new DowngradeSubscription();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_swaps_subscription_to_new_plan_successfully()
    {
        // Mock user and subscription
        $user = Mockery::mock(User::class);
        $subscription = Mockery::mock(Subscription::class);

        // Fake plan
        $plan = Plan::factory()->make([
            'stripe_price_id' => 'price_new123'
        ]);

        // Expectations
        $user->shouldReceive('subscription')
            ->once()
            ->with('default')
            ->andReturn($subscription);

        $subscription->shouldReceive('swap')
            ->once()
            ->with('price_new123');

        $subscription->shouldReceive('fresh')
            ->once()
            ->andReturnSelf();

        $subscription->shouldReceive('getAttribute')
            ->with('stripe_price')
            ->andReturn('price_new123');

        // Execute action
        $result = $this->action->execute($user, $plan);

        // Assert subscription was swapped successfully
        $this->assertTrue($result);
    }

    public function test_it_returns_false_if_subscription_price_does_not_match()
    {
        $user = Mockery::mock(User::class);
        $subscription = Mockery::mock(Subscription::class);

        $plan = Plan::factory()->make([
            'stripe_price_id' => 'price_new123'
        ]);

        $user->shouldReceive('subscription')
            ->once()
            ->with('default')
            ->andReturn($subscription);

        $subscription->shouldReceive('swap')
            ->once()
            ->with('price_new123');

        $subscription->shouldReceive('fresh')
            ->once()
            ->andReturnSelf();

        $subscription->shouldReceive('getAttribute')
            ->with('stripe_price')
            ->andReturn('price_old456');

        $result = $this->action->execute($user, $plan);

        $this->assertFalse($result);
    }
}
