<?php

namespace Tests\Feature\Actions\Stripe;

use App\Actions\Stripe\SetupStripeAccount;
use Tests\TestCase;

class SetupStripeAccountTest extends TestCase
{

    public function test_execute_creates_product_and_prices_on_stripe(): void
    {
        // Arrange
        $productName = 'Test Product ' . uniqid();
        $plans = [
            ['name' => 'Basic', 'amount' => 1000],
            ['name' => 'Pro', 'amount' => 2000],
        ];

        $action = app(SetupStripeAccount::class);

        // Act
        $result = $action->execute($productName, $plans);

        // Assert: Product is created
        $this->assertNotEmpty($result['product']->id);
        $this->assertEquals($productName, $result['product']->name);

        // Assert: Prices are created and linked to product
        $this->assertArrayHasKey('Basic', $result['prices']);
        $this->assertArrayHasKey('Pro', $result['prices']);

        $basicPrice = $result['prices']['Basic'];
        $proPrice = $result['prices']['Pro'];

        $this->assertEquals(1000, $basicPrice->unit_amount);
        $this->assertEquals('month', $basicPrice->recurring->interval);
        $this->assertEquals($result['product']->id, $basicPrice->product);

        $this->assertEquals(2000, $proPrice->unit_amount);
        $this->assertEquals('month', $proPrice->recurring->interval);
        $this->assertEquals($result['product']->id, $proPrice->product);
    }
}
