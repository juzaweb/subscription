<?php

namespace Juzaweb\Modules\Subscription\Tests\Methods;

use Juzaweb\Modules\Subscription\Methods\SubscriptionDriver;
use PHPUnit\Framework\TestCase;

class MockSubscriptionDriver extends SubscriptionDriver
{
    protected string $name = 'MockDriver';
    protected string $description = 'Mock Driver Description';

    public function getConfigs(): array
    {
        return [
            'key1' => 'Key 1',
            'key2' => 'Key 2',
        ];
    }

    public function callGetConfigInMode(string $key): array|int|string|null
    {
        return $this->getConfigInMode($key);
    }
}

class SubscriptionDriverTest extends TestCase
{
    public function testConfig()
    {
        $driver = new MockSubscriptionDriver();
        $driver->setConfigs(['key1' => 'value1', 'key2' => 2]);

        $this->assertEquals('value1', $driver->config('key1'));
        $this->assertEquals(2, $driver->config('key2'));
        $this->assertNull($driver->config('key3'));
    }

    public function testSetConfigs()
    {
        $driver = new MockSubscriptionDriver();
        $configs = ['foo' => 'bar'];
        $result = $driver->setConfigs($configs);

        $this->assertSame($driver, $result);
        $this->assertEquals('bar', $driver->config('foo'));
    }

    public function testGetName()
    {
        $driver = new MockSubscriptionDriver();
        $this->assertEquals('MockDriver', $driver->getName());
    }

    public function testGetDescription()
    {
        $driver = new MockSubscriptionDriver();
        $this->assertEquals('Mock Driver Description', $driver->getDescription());
    }

    public function testHasSandbox()
    {
        $driver = new MockSubscriptionDriver();
        $this->assertTrue($driver->hasSandbox());
    }

    public function testIsReturnInEmbed()
    {
        $driver = new MockSubscriptionDriver();
        $this->assertFalse($driver->isReturnInEmbed());
    }

    public function testGetConfigInMode()
    {
        $driver = new MockSubscriptionDriver();

        // Test live mode (default)
        $driver->setConfigs([
            'api_key' => 'live_key',
            'sandbox_api_key' => 'sandbox_key',
            'sandbox' => 0,
        ]);
        $this->assertEquals('live_key', $driver->callGetConfigInMode('api_key'));

        // Test sandbox mode
        $driver->setConfigs([
            'api_key' => 'live_key',
            'sandbox_api_key' => 'sandbox_key',
            'sandbox' => 1,
        ]);
        $this->assertEquals('sandbox_key', $driver->callGetConfigInMode('api_key'));
    }
}
