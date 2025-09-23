<?php

namespace Tests\Feature\Actions\Stripe;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Actions\Stripe\CancelSubscription;

class CancelSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    // Use your actual Stripe test Price ID
    protected $stripeTestPriceId = 'price_1JRX5iI97c218XRnR2nHlpBb';

    public function test_it_cancels_active_subscription_and_returns_true()
    {
        $user = User::factory()->create();
        $paymentMethod = 'pm_card_visa';

        $user->createOrGetStripeCustomer();
        $user->updateDefaultPaymentMethod($paymentMethod);
        $user->newSubscription('default', $this->stripeTestPriceId)->create($paymentMethod);

        $action = new CancelSubscription();

        $result = $action->execute($user);

        $this->assertTrue($result);
        $this->assertTrue($user->subscription('default')->onGracePeriod());
    }

    public function test_it_returns_false_if_no_subscription_exists()
    {
        $user = User::factory()->create();
        $action = new CancelSubscription();

        $result = $action->execute($user);

        $this->assertFalse($result);
    }

    public function test_it_returns_false_if_subscription_already_cancelled()
    {
        $user = User::factory()->create();
        $paymentMethod = 'pm_card_visa';

        $user->createOrGetStripeCustomer();
        $user->updateDefaultPaymentMethod($paymentMethod);
        $subscription = $user->newSubscription('default', $this->stripeTestPriceId)->create($paymentMethod);
        $subscription->cancel();

        $action = new CancelSubscription();

        $result = $action->execute($user);

        $this->assertFalse($result);
    }
}
