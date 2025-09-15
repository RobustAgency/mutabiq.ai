<?php

namespace Tests\Feature\Actions\Stripe;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Actions\Stripe\ResumeSubscription;
use Mockery;
use App\Models\User;
use App\Models\Plan;
use Laravel\Cashier\Subscription;

class ResumeSubscriptionTest extends TestCase
{
    use WithFaker;
    private ResumeSubscription $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new ResumeSubscription();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_resumes_subscription_successfully()
    {
        $user = Mockery::mock(User::class);
        $subscription = Mockery::mock(Subscription::class);
        $plan = Plan::factory()->make();

        $user->shouldReceive('subscription')
            ->once()
            ->with('default')
            ->andReturn($subscription);

        $subscription->shouldReceive('resume')
            ->once();

        $subscription->shouldReceive('onGracePeriod')
            ->once()
            ->andReturn(false);

        $subscription->shouldReceive('ended')
            ->once()
            ->andReturn(false);

        $result = $this->action->execute($user, $plan);

        $this->assertTrue($result);
    }

    public function test_it_returns_false_if_subscription_is_ended()
    {
        $user = Mockery::mock(User::class);
        $subscription = Mockery::mock(Subscription::class);
        $plan = Plan::factory()->make();

        $user->shouldReceive('subscription')
            ->once()
            ->with('default')
            ->andReturn($subscription);

        $subscription->shouldReceive('resume')
            ->once();

        $subscription->shouldReceive('onGracePeriod')
            ->once()
            ->andReturn(false);

        $subscription->shouldReceive('ended')
            ->once()
            ->andReturn(true);

        $result = $this->action->execute($user, $plan);

        $this->assertFalse($result);
    }
}
