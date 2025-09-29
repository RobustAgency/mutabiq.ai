<?php

namespace Tests\Feature\Actions\Stripe;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Actions\Stripe\DowngradeSubscription;
use Mockery;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Subscription;
use PHPUnit\Framework\Attributes\Test;

class DowngradeSubscriptionTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected $currentPriceId = 'price_1JRXjII97c218XRne1NiTkAp';
    protected $downgradePriceId = 'price_1JRX5iI97c218XRnR2nHlpBb';

    public function test_it_downgrades_the_subscription_to_the_new_plan(): void
    {
        $user = User::factory()->create();

        $currentPlan = Plan::factory()->create([
            'stripe_price_id' => $this->currentPriceId,
        ]);
        $downgradePlan = Plan::factory()->create([
            'stripe_price_id' => $this->downgradePriceId,
        ]);

        // Attach payment method
        $user->createOrGetStripeCustomer();
        $user->updateDefaultPaymentMethod('pm_card_visa');

        // Subscribe to the current plan
        $user->newSubscription('default', $currentPlan->stripe_price_id)->create();

        $action = app(DowngradeSubscription::class);

        $result = $action->execute($user, $downgradePlan);

        $subscription = $user->subscription('default');

        $this->assertTrue($result);
        $this->assertEquals($downgradePlan->stripe_price_id, $subscription->stripe_price);
    }
}
