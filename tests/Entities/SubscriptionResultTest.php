<?php

namespace Juzaweb\Modules\Subscription\Tests\Entities;

use Juzaweb\Modules\Subscription\Entities\SubscriptionResult;
use Juzaweb\Modules\Subscription\Models\SubscriptionHistory;
use PHPUnit\Framework\TestCase;

class MockSubscriptionResult extends SubscriptionResult {}

class SubscriptionResultTest extends TestCase
{
    public function test_set_and_is_successful()
    {
        $result = new MockSubscriptionResult;

        $this->assertFalse($result->isSuccessful()); // Default value

        $result->setSuccessful(true);
        $this->assertTrue($result->isSuccessful());

        $result->setSuccessful(false);
        $this->assertFalse($result->isSuccessful());
    }

    public function test_set_and_get_data()
    {
        $result = new MockSubscriptionResult;

        $this->assertEquals([], $result->getData()); // Default value

        $data = ['key' => 'value'];
        $result->setData($data);
        $this->assertEquals($data, $result->getData());
    }

    public function test_set_and_get_transaction_id()
    {
        $result = new MockSubscriptionResult;

        $this->assertNull($result->getTransactionId()); // Default value

        $transactionId = 'txn_12345';
        $result->setTransactionId($transactionId);
        $this->assertEquals($transactionId, $result->getTransactionId());
    }

    public function test_set_and_get_subscription_history()
    {
        $result = new MockSubscriptionResult;

        $history = new SubscriptionHistory;
        $result->setSubscriptionHistory($history);

        $this->assertSame($history, $result->getSubscriptionHistory());
    }
}
