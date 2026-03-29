<?php

namespace Juzaweb\Modules\Subscription\Tests\Methods;

use Juzaweb\Modules\Subscription\Methods\SubscriptionDriver;
use Juzaweb\Modules\Subscription\Tests\TestCase;

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
    public function test_config()
    {
        $driver = new MockSubscriptionDriver;
        $driver->setConfigs(['key1' => 'value1', 'key2' => 2]);

        $this->assertEquals('value1', $driver->config('key1'));
        $this->assertEquals(2, $driver->config('key2'));
        $this->assertNull($driver->config('key3'));
    }

    public function test_set_configs()
    {
        $driver = new MockSubscriptionDriver;
        $configs = ['foo' => 'bar'];
        $result = $driver->setConfigs($configs);

        $this->assertSame($driver, $result);
        $this->assertEquals('bar', $driver->config('foo'));
    }

    public function test_get_name()
    {
        $driver = new MockSubscriptionDriver;
        $this->assertEquals('MockDriver', $driver->getName());
    }

    public function test_get_description()
    {
        $driver = new MockSubscriptionDriver;
        $this->assertEquals('Mock Driver Description', $driver->getDescription());
    }

    public function test_has_sandbox()
    {
        $driver = new MockSubscriptionDriver;
        $this->assertTrue($driver->hasSandbox());
    }

    public function test_is_return_in_embed()
    {
        $driver = new MockSubscriptionDriver;
        $this->assertFalse($driver->isReturnInEmbed());
    }

    public function test_get_config_in_mode()
    {
        $driver = new MockSubscriptionDriver;

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
