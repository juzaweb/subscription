<?php

namespace Juzaweb\Modules\Subscription\Tests\Methods;

use Illuminate\Http\Request;
use Juzaweb\Modules\Subscription\Exceptions\SubscriptionException;
use Juzaweb\Modules\Subscription\Methods\PayPal;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class MockPayPalDriver extends PayPal
{
    protected PayPalClient $mockProvider;

    protected LoggerInterface $mockLogger;

    public function setMockProvider(PayPalClient $provider)
    {
        $this->mockProvider = $provider;
    }

    public function setMockLogger(LoggerInterface $logger)
    {
        $this->mockLogger = $logger;
    }

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
    public function test_webhook_throws_exception_on_invalid_signature()
    {
        // Setup
        $driver = new MockPayPalDriver;
        $driver->setConfigs(['webhook_id' => 'test_webhook_id']);

        $mockProvider = $this->createMock(PayPalClient::class);
        $mockProvider->expects($this->once())
            ->method('verifyWebHook')
            ->willReturn(['verification_status' => 'FAILURE']); // Or any array without SUCCESS

        $driver->setMockProvider($mockProvider);

        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockLogger->expects($this->once())
            ->method('error')
            ->with('Invalid webhook signature', $this->anything());

        $driver->setMockLogger($mockLogger);

        $request = Request::create('/webhook', 'POST', [], [], [], [
            'HTTP_PAYPAL-AUTH-ALGO' => 'algo',
            'HTTP_PAYPAL-CERT-URL' => 'url',
            'HTTP_PAYPAL-TRANSMISSION-ID' => 'id',
            'HTTP_PAYPAL-TRANSMISSION-SIG' => 'sig',
            'HTTP_PAYPAL-TRANSMISSION-TIME' => 'time',
        ], json_encode(['event_type' => 'PAYMENT.SALE.COMPLETED']));

        $this->expectException(SubscriptionException::class);
        $this->expectExceptionMessage('Invalid webhook signature');

        // Execute
        $driver->webhook($request);
    }
}
