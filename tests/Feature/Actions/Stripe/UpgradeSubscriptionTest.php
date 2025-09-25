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
    use RefreshDatabase;

    // Replace these with your actual Stripe test price IDs!
    protected $upgradePriceId = 'price_1JRXjII97c218XRne1NiTkAp';
    protected $originalPriceId = 'price_1JRX5iI97c218XRnR2nHlpBb';

    public function test_it_upgrades_the_subscription_to_the_new_plan(): void
    {
        $user = User::factory()->create();

        $originalPlan = Plan::factory()->create([
            'stripe_price_id' => $this->originalPriceId,
        ]);
        $upgradePlan = Plan::factory()->create([
            'stripe_price_id' => $this->upgradePriceId,
        ]);

        // Attach payment method
        $user->createOrGetStripeCustomer();
        $user->updateDefaultPaymentMethod('pm_card_visa');

        // Subscribe to the original plan
        $user->newSubscription('default', $originalPlan->stripe_price_id)->create();

        $action = app(UpgradeSubscription::class);

        $result = $action->execute($user, $upgradePlan);

        $subscription = $user->subscription('default');

        $this->assertTrue($result);
        $this->assertEquals($upgradePlan->stripe_price_id, $subscription->stripe_price);
    }
}
