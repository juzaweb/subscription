<?php

namespace Juzaweb\Modules\Subscription\Tests\Methods;

use Illuminate\Http\Request;
use Juzaweb\Modules\Core\Tests\TestCase;
use Juzaweb\Modules\Subscription\Entities\SubscribeResult;
use Juzaweb\Modules\Subscription\Entities\SubscriptionReturnResult;
use Juzaweb\Modules\Subscription\Entities\WebhookResult;
use Juzaweb\Modules\Subscription\Exceptions\SubscriptionException;
use Juzaweb\Modules\Subscription\Methods\PayPal;
use Juzaweb\Modules\Subscription\Models\Plan;
use Juzaweb\Modules\Subscription\Models\PlanSubscriptionMethod;
use Juzaweb\Modules\Subscription\Models\SubscriptionHistory;
use Mockery;
use Mockery\MockInterface;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class MockPayPal extends PayPal
{
    public $mockProvider;

    protected function getProvider(): PayPalClient
    {
        return $this->mockProvider;
    }
}

class PayPalTest extends TestCase
{
    protected MockPayPal $paypal;

    protected MockInterface|PayPalClient $providerMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->providerMock = Mockery::mock(PayPalClient::class);

        $this->paypal = new MockPayPal;
        $this->paypal->mockProvider = $this->providerMock;
        $this->paypal->setConfigs([
            'client_id' => 'test_client_id',
            'secret' => 'test_secret',
            'webhook_id' => 'test_webhook_id',
            'sandbox' => 1,
            'sandbox_client_id' => 'test_sandbox_client_id',
            'sandbox_secret' => 'test_sandbox_secret',
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        parent::defineDatabaseMigrations();
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }

    public function test_get_configs()
    {
        $configs = $this->paypal->getConfigs();
        $this->assertIsArray($configs);
        $this->assertArrayHasKey('client_id', $configs);
        $this->assertArrayHasKey('secret', $configs);
        $this->assertArrayHasKey('webhook_id', $configs);
    }

    public function test_sandbox()
    {
        $this->paypal->sandbox(true);
        $this->assertEquals(1, $this->paypal->config('sandbox'));

        $this->paypal->sandbox(false);
        $this->assertEquals(0, $this->paypal->config('sandbox'));
    }

    public function test_create_plan_returns_existing()
    {
        $plan = Plan::create([
            'name' => 'Test Plan',
            'description' => 'Test Description',
            'price' => 10,
            'status' => 'active',
            'module' => 'test',
        ]);

        $method = PlanSubscriptionMethod::create([
            'plan_id' => $plan->id,
            'method' => 'PayPal',
            'payment_plan_id' => 'P-1234567890',
            'data' => [],
        ]);

        $result = $this->paypal->createPlan($plan);

        $this->assertInstanceOf(PlanSubscriptionMethod::class, $result);
        $this->assertEquals($method->id, $result->id);
    }

    public function test_create_plan_throws_exception_on_product_error()
    {
        $plan = Plan::create([
            'name' => 'Test Plan',
            'description' => 'Test Description',
            'price' => 10,
            'status' => 'active',
            'module' => 'test',
        ]);

        $this->providerMock->shouldReceive('createProduct')
            ->once()
            ->andReturn(['error' => ['message' => 'Product creation failed']]);

        $this->expectException(SubscriptionException::class);
        $this->expectExceptionMessage('Product creation failed');

        $this->paypal->createPlan($plan);
    }

    public function test_create_plan_throws_exception_on_plan_error()
    {
        $plan = Plan::create([
            'name' => 'Test Plan',
            'description' => 'Test Description',
            'price' => 10,
            'status' => 'active',
            'module' => 'test',
        ]);

        $this->providerMock->shouldReceive('createProduct')
            ->once()
            ->andReturn(['id' => 'PROD-123']);

        $this->providerMock->shouldReceive('createPlan')
            ->once()
            ->andReturn(['error' => ['message' => 'Plan creation failed']]);

        $this->expectException(SubscriptionException::class);
        $this->expectExceptionMessage('Plan creation failed');

        $this->paypal->createPlan($plan);
    }

    public function test_create_plan_success()
    {
        $plan = Plan::create([
            'name' => 'Test Plan',
            'description' => 'Test Description',
            'price' => 10,
            'status' => 'active',
            'module' => 'test',
        ]);

        $this->providerMock->shouldReceive('createProduct')
            ->once()
            ->andReturn(['id' => 'PROD-123']);

        $this->providerMock->shouldReceive('createPlan')
            ->once()
            ->andReturn(['id' => 'PLAN-123']);

        $result = $this->paypal->createPlan($plan);

        $this->assertInstanceOf(PlanSubscriptionMethod::class, $result);
        $this->assertEquals('PLAN-123', $result->payment_plan_id);
        $this->assertEquals('PayPal', $result->method);
        $this->assertEquals($plan->id, $result->plan_id);
    }

    public function test_subscribe()
    {
        $plan = Plan::create([
            'name' => 'Test Plan',
            'description' => 'Test Description',
            'price' => 10,
            'status' => 'active',
            'module' => 'test',
        ]);

        $this->providerMock->shouldReceive('createProduct')
            ->once()
            ->andReturn(['id' => 'PROD-123']);

        $this->providerMock->shouldReceive('createPlan')
            ->once()
            ->andReturn(['id' => 'PLAN-123']);

        $this->providerMock->shouldReceive('addProductById')
            ->once()
            ->with('PROD-123')
            ->andReturnSelf();

        $this->providerMock->shouldReceive('addBillingPlanById')
            ->once()
            ->with('PLAN-123')
            ->andReturnSelf();

        $this->providerMock->shouldReceive('setReturnAndCancelUrl')
            ->once()
            ->with('http://return.url', 'http://cancel.url')
            ->andReturnSelf();

        $this->providerMock->shouldReceive('setupSubscription')
            ->once()
            ->with('Test Customer', 'test@example.com')
            ->andReturn([
                'id' => 'SUB-123',
                'links' => [
                    ['rel' => 'approve', 'href' => 'http://approve.url'],
                ],
            ]);

        $result = $this->paypal->subscribe($plan, [
            'customer_name' => 'Test Customer',
            'customer_email' => 'test@example.com',
            'return_url' => 'http://return.url',
            'cancel_url' => 'http://cancel.url',
        ]);

        $this->assertInstanceOf(SubscribeResult::class, $result);
        $this->assertEquals('SUB-123', $result->getTransactionId());
        $this->assertEquals('http://approve.url', $result->getRedirectUrl());
    }

    public function test_complete()
    {
        $plan = Plan::create([
            'name' => 'Test Plan',
            'description' => 'Test Description',
            'price' => 10,
            'status' => 'active',
            'module' => 'test',
        ]);

        $history = SubscriptionHistory::create([
            'plan_id' => $plan->id,
            'agreement_id' => 'AGR-123',
            'method' => 'PayPal',
            'module' => 'test',
            'token' => 'test-token',
            'status' => 'pending',
            'type' => 'subscription',
            'driver' => 'PayPal',
            'amount' => 10,
            'billable_id' => 1,
            'billable_type' => 'App\\Models\\User',
            'custom_id' => 'custom-1',
        ]);

        $this->providerMock->shouldReceive('showSubscriptionDetails')
            ->once()
            ->with('AGR-123')
            ->andReturn(['status' => 'ACTIVE']);

        $result = $this->paypal->complete($history, ['token' => 'test-token']);

        $this->assertInstanceOf(SubscriptionReturnResult::class, $result);
        $this->assertEquals('AGR-123', $result->getTransactionId());
        $this->assertTrue($result->isSuccessful());
    }

    public function test_complete_unsuccessful()
    {
        $plan = Plan::create([
            'name' => 'Test Plan',
            'description' => 'Test Description',
            'price' => 10,
            'status' => 'active',
            'module' => 'test',
        ]);

        $history = SubscriptionHistory::create([
            'plan_id' => $plan->id,
            'agreement_id' => 'AGR-123',
            'method' => 'PayPal',
            'module' => 'test',
            'token' => 'test-token',
            'status' => 'pending',
            'type' => 'subscription',
            'driver' => 'PayPal',
            'amount' => 10,
            'billable_id' => 1,
            'billable_type' => 'App\\Models\\User',
            'custom_id' => 'custom-1',
        ]);

        $this->providerMock->shouldReceive('showSubscriptionDetails')
            ->once()
            ->with('AGR-123')
            ->andReturn(['status' => 'CANCELLED']);

        $result = $this->paypal->complete($history, ['token' => 'test-token']);

        $this->assertInstanceOf(SubscriptionReturnResult::class, $result);
        $this->assertEquals('AGR-123', $result->getTransactionId());
        $this->assertFalse($result->isSuccessful());
    }

    public function test_webhook_invalid_signature()
    {
        $request = Request::create('/webhook', 'POST', [], [], [], [
            'HTTP_PAYPAL-AUTH-ALGO' => 'algo',
            'HTTP_PAYPAL-CERT-URL' => 'url',
            'HTTP_PAYPAL-TRANSMISSION-ID' => 'id',
            'HTTP_PAYPAL-TRANSMISSION-SIG' => 'sig',
            'HTTP_PAYPAL-TRANSMISSION-TIME' => 'time',
        ], json_encode(['event' => 'test']));

        $this->providerMock->shouldReceive('verifyWebHook')
            ->once()
            ->andReturn(['verification_status' => 'FAILURE']);

        $this->expectException(SubscriptionException::class);
        $this->expectExceptionMessage('Invalid webhook signature');

        $this->paypal->webhook($request);
    }

    public function test_webhook_sale_completed()
    {
        $request = Request::create('/webhook', 'POST', [
            'event_type' => 'PAYMENT.SALE.COMPLETED',
            'resource' => [
                'state' => 'completed',
                'billing_agreement_id' => 'AGR-123',
            ],
        ], [], [], [
            'HTTP_PAYPAL-AUTH-ALGO' => 'algo',
            'HTTP_PAYPAL-CERT-URL' => 'url',
            'HTTP_PAYPAL-TRANSMISSION-ID' => 'id',
            'HTTP_PAYPAL-TRANSMISSION-SIG' => 'sig',
            'HTTP_PAYPAL-TRANSMISSION-TIME' => 'time',
        ], json_encode(['event' => 'test']));

        $this->providerMock->shouldReceive('verifyWebHook')
            ->once()
            ->andReturn(['verification_status' => 'SUCCESS']);

        $result = $this->paypal->webhook($request);

        $this->assertInstanceOf(WebhookResult::class, $result);
        $this->assertEquals('AGR-123', $result->getTransactionId());
        $this->assertEquals('completed', $result->getStatus());
        $this->assertTrue($result->isSuccessful());
    }

    public function test_webhook_subscription_cancelled()
    {
        $request = Request::create('/webhook', 'POST', [
            'event_type' => 'BILLING.SUBSCRIPTION.CANCELLED',
            'resource' => [
                'id' => 'AGR-123',
            ],
        ], [], [], [
            'HTTP_PAYPAL-AUTH-ALGO' => 'algo',
            'HTTP_PAYPAL-CERT-URL' => 'url',
            'HTTP_PAYPAL-TRANSMISSION-ID' => 'id',
            'HTTP_PAYPAL-TRANSMISSION-SIG' => 'sig',
            'HTTP_PAYPAL-TRANSMISSION-TIME' => 'time',
        ], json_encode(['event' => 'test']));

        $this->providerMock->shouldReceive('verifyWebHook')
            ->once()
            ->andReturn(['verification_status' => 'SUCCESS']);

        $result = $this->paypal->webhook($request);

        $this->assertInstanceOf(WebhookResult::class, $result);
        $this->assertEquals('AGR-123', $result->getTransactionId());
        $this->assertEquals('cancelled', $result->getStatus());
        $this->assertFalse($result->isSuccessful());
    }
}
