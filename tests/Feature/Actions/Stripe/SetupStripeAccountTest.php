<?php

namespace Tests\Unit\Actions\Stripe;

use App\Actions\Stripe\SetupStripeAccount;
use Mockery;
use Tests\TestCase;
use Stripe\Price;
use Stripe\Product;
use Stripe\Service\PriceService;
use Stripe\Service\ProductService;
use Stripe\StripeClient;

class SetupStripeAccountTest extends TestCase
{
    protected $mockStripeClient;
    protected $mockProductService;
    protected $mockPriceService;
    protected $setupStripeAccount;

    protected function setUp(): void
    {
        parent::setUp();

        // Set config value for cashier secret
        config(['cashier.secret' => 'sk_test_fake_key']);

        // Mock Stripe services
        $this->mockProductService = Mockery::mock(ProductService::class);
        $this->mockPriceService = Mockery::mock(PriceService::class);

        // Mock StripeClient
        $this->mockStripeClient = Mockery::mock(StripeClient::class);
        $this->mockStripeClient->products = $this->mockProductService;
        $this->mockStripeClient->prices = $this->mockPriceService;

        // Create instance and inject mock
        $this->setupStripeAccount = new SetupStripeAccount();
        
        // Use reflection to inject the mock client
        $reflection = new \ReflectionClass($this->setupStripeAccount);
        $property = $reflection->getProperty('stripeClient');
        $property->setAccessible(true);
        $property->setValue($this->setupStripeAccount, $this->mockStripeClient);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_can_setup_stripe_account_with_product_and_plans()
    {
        // Arrange
        $productName = 'Test Product';
        $plans = [
            ['name' => 'Basic Plan', 'amount' => 999],
            ['name' => 'Pro Plan', 'amount' => 1999],
        ];

        $mockProduct = $this->createMockProduct('prod_123', $productName);
        $mockBasicPrice = $this->createMockPrice('price_basic_123', 'prod_123', 999, 'Basic Plan');
        $mockProPrice = $this->createMockPrice('price_pro_123', 'prod_123', 1999, 'Pro Plan');

        // Mock product creation
        $this->mockProductService
            ->shouldReceive('create')
            ->once()
            ->with(['name' => $productName])
            ->andReturn($mockProduct);

        // Mock price creation for Basic Plan
        $this->mockPriceService
            ->shouldReceive('create')
            ->once()
            ->with([
                'unit_amount' => 999,
                'currency' => 'usd',
                'recurring' => ['interval' => 'month'],
                'product' => 'prod_123',
                'nickname' => 'Basic Plan',
            ])
            ->andReturn($mockBasicPrice);

        // Mock price creation for Pro Plan
        $this->mockPriceService
            ->shouldReceive('create')
            ->once()
            ->with([
                'unit_amount' => 1999,
                'currency' => 'usd',
                'recurring' => ['interval' => 'month'],
                'product' => 'prod_123',
                'nickname' => 'Pro Plan',
            ])
            ->andReturn($mockProPrice);

        // Act
        $result = $this->setupStripeAccount->execute($productName, $plans);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('product', $result);
        $this->assertArrayHasKey('prices', $result);
        
        $this->assertEquals($mockProduct, $result['product']);
        $this->assertCount(2, $result['prices']);
        $this->assertArrayHasKey('Basic Plan', $result['prices']);
        $this->assertArrayHasKey('Pro Plan', $result['prices']);
        $this->assertEquals($mockBasicPrice, $result['prices']['Basic Plan']);
        $this->assertEquals($mockProPrice, $result['prices']['Pro Plan']);
    }

    public function test_it_can_handle_single_plan()
    {
        // Arrange
        $productName = 'Single Plan Product';
        $plans = [
            ['name' => 'Only Plan', 'amount' => 1499],
        ];

        $mockProduct = $this->createMockProduct('prod_456', $productName);
        $mockPrice = $this->createMockPrice('price_456', 'prod_456', 1499, 'Only Plan');

        $this->mockProductService
            ->shouldReceive('create')
            ->once()
            ->andReturn($mockProduct);

        $this->mockPriceService
            ->shouldReceive('create')
            ->once()
            ->andReturn($mockPrice);

        // Act
        $result = $this->setupStripeAccount->execute($productName, $plans);

        // Assert
        $this->assertCount(1, $result['prices']);
        $this->assertArrayHasKey('Only Plan', $result['prices']);
    }

    public function test_it_can_handle_empty_plans_array()
    {
        // Arrange
        $productName = 'No Plans Product';
        $plans = [];

        $mockProduct = $this->createMockProduct('prod_789', $productName);

        $this->mockProductService
            ->shouldReceive('create')
            ->once()
            ->andReturn($mockProduct);

        $this->mockPriceService
            ->shouldNotReceive('create');

        // Act
        $result = $this->setupStripeAccount->execute($productName, $plans);

        // Assert
        $this->assertEmpty($result['prices']);
        $this->assertEquals($mockProduct, $result['product']);
    }

    public function test_it_handles_multiple_plans_with_different_amounts()
    {
        // Arrange
        $productName = 'Multi-tier Product';
        $plans = [
            ['name' => 'Starter', 'amount' => 500],
            ['name' => 'Professional', 'amount' => 1500],
            ['name' => 'Enterprise', 'amount' => 5000],
        ];

        $mockProduct = $this->createMockProduct('prod_multi', $productName);
        
        $this->mockProductService
            ->shouldReceive('create')
            ->once()
            ->andReturn($mockProduct);

        // Mock each price creation
        foreach ($plans as $plan) {
            $mockPrice = $this->createMockPrice(
                'price_' . strtolower($plan['name']), 
                'prod_multi', 
                $plan['amount'], 
                $plan['name']
            );
            
            $this->mockPriceService
                ->shouldReceive('create')
                ->once()
                ->with([
                    'unit_amount' => $plan['amount'],
                    'currency' => 'usd',
                    'recurring' => ['interval' => 'month'],
                    'product' => 'prod_multi',
                    'nickname' => $plan['name'],
                ])
                ->andReturn($mockPrice);
        }

        // Act
        $result = $this->setupStripeAccount->execute($productName, $plans);

        // Assert
        $this->assertCount(3, $result['prices']);
        $this->assertArrayHasKey('Starter', $result['prices']);
        $this->assertArrayHasKey('Professional', $result['prices']);
        $this->assertArrayHasKey('Enterprise', $result['prices']);
    }

    /**
     * Create a mock Stripe Product object
     */
    private function createMockProduct(string $id, string $name): Product
    {
        // Create a real Product object from JSON data
        $productData = [
            'id' => $id,
            'name' => $name,
            'object' => 'product',
        ];
        
        return Product::constructFrom($productData);
    }

    /**
     * Create a mock Stripe Price object
     */
    private function createMockPrice(string $id, string $productId, int $amount, string $nickname): Price
    {
        // Create a real Price object from JSON data
        $priceData = [
            'id' => $id,
            'product' => $productId,
            'unit_amount' => $amount,
            'currency' => 'usd',
            'nickname' => $nickname,
            'object' => 'price',
            'recurring' => [
                'interval' => 'month',
            ],
        ];
        
        return Price::constructFrom($priceData);
    }
}