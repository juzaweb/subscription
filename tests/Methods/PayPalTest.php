<?php

namespace Juzaweb\Modules\Subscription\Tests\Methods;

use Illuminate\Http\Request;
use Juzaweb\Modules\Subscription\Entities\WebhookResult;
use Juzaweb\Modules\Subscription\Exceptions\SubscriptionException;
use Juzaweb\Modules\Subscription\Methods\PayPal;
use Mockery;
use PHPUnit\Framework\TestCase;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class TestPayPal extends PayPal
{
    public $mockProvider;

    public $mockLogger;

    protected function getProvider(): PayPalClient
    {
        return $this->mockProvider;
    }

    protected function getLogger()
    {
        return $this->mockLogger;
    }
}

class PayPalTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_webhook_invalid_signature()
    {
        $paypal = new TestPayPal;
        $paypal->setConfigs(['webhook_id' => 'webhook_123', 'sandbox' => 0]);

        $provider = Mockery::mock(PayPalClient::class);
        $provider->shouldReceive('verifyWebHook')->once()->andReturn(['verification_status' => 'FAILED']);
        $paypal->mockProvider = $provider;

        $logger = Mockery::mock();
        $logger->shouldReceive('error')->once();
        $paypal->mockLogger = $logger;

        $request = Request::create('/webhook', 'POST', [], [], [], [], json_encode(['event' => 'test']));
        $request->headers->set('PAYPAL-AUTH-ALGO', 'algo');
        $request->headers->set('PAYPAL-CERT-URL', 'url');
        $request->headers->set('PAYPAL-TRANSMISSION-ID', 'id');
        $request->headers->set('PAYPAL-TRANSMISSION-SIG', 'sig');
        $request->headers->set('PAYPAL-TRANSMISSION-TIME', 'time');

        $this->expectException(SubscriptionException::class);
        $this->expectExceptionMessage('Invalid webhook signature');

        $paypal->webhook($request);
    }

    public function test_webhook_payment_sale_completed_success()
    {
        $paypal = new TestPayPal;
        $paypal->setConfigs(['webhook_id' => 'webhook_123', 'sandbox' => 0]);

        $provider = Mockery::mock(PayPalClient::class);
        $provider->shouldReceive('verifyWebHook')->once()->andReturn(['verification_status' => 'SUCCESS']);
        $paypal->mockProvider = $provider;

        $content = [
            'event_type' => 'PAYMENT.SALE.COMPLETED',
            'resource' => [
                'state' => 'completed',
                'billing_agreement_id' => 'sub_123',
                'id' => 'sale_123',
            ],
        ];

        $request = Request::create('/webhook', 'POST', $content, [], [], [], json_encode($content));

        $result = $paypal->webhook($request);

        $this->assertInstanceOf(WebhookResult::class, $result);
        $this->assertTrue($result->isSuccessful());
        $this->assertEquals('completed', $result->getStatus());
        $this->assertEquals('sub_123', $result->getTransactionId());
        $this->assertEquals($content, $result->getData());
    }

    public function test_webhook_payment_sale_completed_suspended()
    {
        $paypal = new TestPayPal;
        $paypal->setConfigs(['webhook_id' => 'webhook_123', 'sandbox' => 0]);

        $provider = Mockery::mock(PayPalClient::class);
        $provider->shouldReceive('verifyWebHook')->once()->andReturn(['verification_status' => 'SUCCESS']);
        $paypal->mockProvider = $provider;

        $content = [
            'event_type' => 'PAYMENT.SALE.COMPLETED',
            'resource' => [
                'state' => 'suspended',
                'id' => 'sale_123',
            ],
        ];

        $request = Request::create('/webhook', 'POST', $content, [], [], [], json_encode($content));

        $result = $paypal->webhook($request);

        $this->assertInstanceOf(WebhookResult::class, $result);
        $this->assertFalse($result->isSuccessful());
        $this->assertEquals('suspended', $result->getStatus());
        $this->assertEquals('sale_123', $result->getTransactionId());
    }

    public function test_webhook_payment_sale_completed_cancelled()
    {
        $paypal = new TestPayPal;
        $paypal->setConfigs(['webhook_id' => 'webhook_123', 'sandbox' => 0]);

        $provider = Mockery::mock(PayPalClient::class);
        $provider->shouldReceive('verifyWebHook')->once()->andReturn(['verification_status' => 'SUCCESS']);
        $paypal->mockProvider = $provider;

        $content = [
            'event_type' => 'PAYMENT.SALE.COMPLETED',
            'resource' => [
                'state' => 'cancelled',
                'id' => 'sale_123',
            ],
        ];

        $request = Request::create('/webhook', 'POST', $content, [], [], [], json_encode($content));

        $result = $paypal->webhook($request);

        $this->assertInstanceOf(WebhookResult::class, $result);
        $this->assertFalse($result->isSuccessful());
        $this->assertEquals('cancelled', $result->getStatus());
        $this->assertTrue($result->isCancel());
    }

    public function test_webhook_payment_sale_completed_pending()
    {
        $paypal = new TestPayPal;
        $paypal->setConfigs(['webhook_id' => 'webhook_123', 'sandbox' => 0]);

        $provider = Mockery::mock(PayPalClient::class);
        $provider->shouldReceive('verifyWebHook')->once()->andReturn(['verification_status' => 'SUCCESS']);
        $paypal->mockProvider = $provider;

        $content = [
            'event_type' => 'PAYMENT.SALE.COMPLETED',
            'resource' => [
                'state' => 'pending_state',
                'id' => 'sale_123',
            ],
        ];

        $request = Request::create('/webhook', 'POST', $content, [], [], [], json_encode($content));

        $result = $paypal->webhook($request);

        $this->assertInstanceOf(WebhookResult::class, $result);
        $this->assertFalse($result->isSuccessful());
        $this->assertEquals('pending', $result->getStatus());
    }

    public function test_webhook_billing_subscription_cancelled()
    {
        $paypal = new TestPayPal;
        $paypal->setConfigs(['webhook_id' => 'webhook_123', 'sandbox' => 0]);

        $provider = Mockery::mock(PayPalClient::class);
        $provider->shouldReceive('verifyWebHook')->once()->andReturn(['verification_status' => 'SUCCESS']);
        $paypal->mockProvider = $provider;

        $content = [
            'event_type' => 'BILLING.SUBSCRIPTION.CANCELLED',
            'resource' => [
                'id' => 'sub_123',
            ],
        ];

        $request = Request::create('/webhook', 'POST', $content, [], [], [], json_encode($content));

        $result = $paypal->webhook($request);

        $this->assertInstanceOf(WebhookResult::class, $result);
        $this->assertFalse($result->isSuccessful());
        $this->assertEquals('cancelled', $result->getStatus());
        $this->assertEquals('sub_123', $result->getTransactionId());
        $this->assertTrue($result->isCancel());
    }

    public function test_webhook_billing_subscription_suspended()
    {
        $paypal = new TestPayPal;
        $paypal->setConfigs(['webhook_id' => 'webhook_123', 'sandbox' => 0]);

        $provider = Mockery::mock(PayPalClient::class);
        $provider->shouldReceive('verifyWebHook')->once()->andReturn(['verification_status' => 'SUCCESS']);
        $paypal->mockProvider = $provider;

        $content = [
            'event_type' => 'BILLING.SUBSCRIPTION.SUSPENDED',
            'resource' => [
                'id' => 'sub_123',
            ],
        ];

        $request = Request::create('/webhook', 'POST', $content, [], [], [], json_encode($content));

        $result = $paypal->webhook($request);

        $this->assertInstanceOf(WebhookResult::class, $result);
        $this->assertFalse($result->isSuccessful());
        $this->assertEquals('suspended', $result->getStatus());
        $this->assertEquals('sub_123', $result->getTransactionId());
        $this->assertTrue($result->isSuspended());
    }

    public function test_webhook_unknown_event()
    {
        $paypal = new TestPayPal;
        $paypal->setConfigs(['webhook_id' => 'webhook_123', 'sandbox' => 0]);

        $provider = Mockery::mock(PayPalClient::class);
        $provider->shouldReceive('verifyWebHook')->once()->andReturn(['verification_status' => 'SUCCESS']);
        $paypal->mockProvider = $provider;

        $content = [
            'event_type' => 'UNKNOWN.EVENT',
            'resource' => [
                'id' => 'sub_123',
            ],
        ];

        $request = Request::create('/webhook', 'POST', $content, [], [], [], json_encode($content));

        $result = $paypal->webhook($request);

        $this->assertNull($result);
    }
}
