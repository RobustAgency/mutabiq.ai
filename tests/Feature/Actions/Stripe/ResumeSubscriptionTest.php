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
    use RefreshDatabase, WithFaker;

    protected $stripeTestPriceId = 'price_1JRX5iI97c218XRnR2nHlpBb';

    public function test_it_resumes_a_cancelled_subscription_and_returns_true(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create([
            'stripe_price_id' => $this->stripeTestPriceId,
        ]);

        // Attach payment method
        $user->createOrGetStripeCustomer();
        $user->updateDefaultPaymentMethod('pm_card_visa');

        // Subscribe to the plan
        $user->newSubscription('default', $plan->stripe_price_id)->create();

        // Cancel the subscription (put on grace period)
        $user->subscription('default')->cancel();

        // Resume the subscription
        $action = app(ResumeSubscription::class);
        $result = $action->execute($user, $plan);

        $subscription = $user->subscription('default');

        $this->assertTrue($result);
        $this->assertFalse($subscription->onGracePeriod());
        $this->assertFalse($subscription->ended());
    }
}
