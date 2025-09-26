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

    protected $stripeTestPriceId = 'price_1JRX5iI97c218XRnR2nHlpBb';

    public function test_it_creates_a_new_subscription_and_returns_true(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create([
            'stripe_price_id' => $this->stripeTestPriceId,
        ]);

        // Attach a payment method (required for Stripe)
        $user->createOrGetStripeCustomer();
        $user->updateDefaultPaymentMethod('pm_card_visa');

        $action = app(CreateNewSubscription::class);

        $result = $action->execute($user, $plan);

        $this->assertTrue($result);
        $this->assertTrue($user->subscribed('default'));
        $this->assertTrue($user->subscription('default')->active());
    }
}
