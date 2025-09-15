<?php

namespace Tests\Feature\Actions\Stripe;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Cashier\Subscription;
use Mockery;
use App\Actions\Stripe\CancelSubscription;
use App\Models\User;
use Tests\TestCase;

class CancelSubscriptionTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    private CancelSubscription $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CancelSubscription();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_returns_false_if_no_subscription_exists()
    {
        $user = Mockery::mock(User::class);

        $user->shouldReceive('subscription')
            ->once()
            ->with('default')
            ->andReturn(null);

        $result = $this->action->execute($user);

        $this->assertFalse($result);
    }

    public function test_it_returns_false_if_subscription_already_canceled()
    {
        $user = Mockery::mock(User::class);
        $subscription = Mockery::mock(Subscription::class);

        $user->shouldReceive('subscription')
            ->once()
            ->with('default')
            ->andReturn($subscription);

        $subscription->shouldReceive('canceled')
            ->once()
            ->andReturn(true);

        $result = $this->action->execute($user);

        $this->assertFalse($result);
    }

    public function test_it_cancels_active_subscription_and_returns_true()
    {
        $user = Mockery::mock(User::class);
        $subscription = Mockery::mock(Subscription::class);

        $user->shouldReceive('subscription')
            ->once()
            ->with('default')
            ->andReturn($subscription);

        $subscription->shouldReceive('canceled')
            ->once()
            ->andReturn(false);

        $subscription->shouldReceive('cancel')
            ->once();

        $subscription->shouldReceive('fresh')
            ->once()
            ->andReturnSelf();

        $subscription->shouldReceive('onGracePeriod')
            ->once()
            ->andReturn(true);

        $result = $this->action->execute($user);

        $this->assertTrue($result);
    }
}
