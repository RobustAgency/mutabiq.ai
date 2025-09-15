<?php

namespace Tests\Feature\Actions\Stripe;

use Tests\TestCase;
use App\Models\User;
use App\Models\Plan;
use Mockery;
use App\Actions\Stripe\CreateNewSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Cashier\Subscription;

class CreateNewSubscriptionTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    private CreateNewSubscription $action;
    private $user;
    private Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new CreateNewSubscription();

        $this->user = Mockery::mock(User::class);

        $this->plan = Plan::factory()->create([
            'stripe_price_id' => 'price_test123'
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_creates_new_subscription()
    {
        // Mock the subscription
        $subscription = new Subscription();

        $subscription->stripe_status = 'active';
        $subscription->stripe_price = 'price_test123';

        // Set up mock expectations
        $this->user->shouldReceive('newSubscription')
            ->once()
            ->with('default', 'price_test123')
            ->andReturnSelf();

        $this->user->shouldReceive('create')
            ->once()
            ->andReturn($subscription);

        $this->user->shouldReceive('subscribed')
            ->once()
            ->with('default')
            ->andReturn(true);

        $this->user->shouldReceive('subscription')
            ->once()
            ->with('default')
            ->andReturn($subscription);

        // Execute the action
        $result = $this->action->execute($this->user, $this->plan);

        // Assert the subscription was created successfully
        $this->assertTrue($result);
    }
}
