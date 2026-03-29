<?php

namespace Juzaweb\Modules\Subscription\Tests\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use InvalidArgumentException;
use Juzaweb\Modules\Core\Application;
use Juzaweb\Modules\Core\Models\Authenticatable;
use Juzaweb\Modules\Subscription\Contracts\Subscriptable;
use Juzaweb\Modules\Subscription\Contracts\SubscriptionMethod;
use Juzaweb\Modules\Subscription\Contracts\SubscriptionModule;
use Juzaweb\Modules\Subscription\Entities\Feature;
use Juzaweb\Modules\Subscription\Entities\SubscribeResult;
use Juzaweb\Modules\Subscription\Entities\SubscriptionReturnResult;
use Juzaweb\Modules\Subscription\Entities\WebhookResult;
use Juzaweb\Modules\Subscription\Enums\SubscriptionHistoryStatus;
use Juzaweb\Modules\Subscription\Models\Plan;
use Juzaweb\Modules\Subscription\Models\SubscriptionHistory;
use Juzaweb\Modules\Subscription\Models\SubscriptionMethod as SubscriptionMethodModel;
use Juzaweb\Modules\Subscription\Services\SubscriptionManager;
use Juzaweb\Modules\Subscription\Tests\TestCase;
use Mockery;

class SubscriptionManagerTest extends TestCase
{
    protected SubscriptionManager $subscriptionManager;

    protected function setUp(): void
    {
        parent::setUp();

        $appMock = Mockery::mock(Application::class);
        $this->subscriptionManager = new SubscriptionManager($appMock);

        Route::get('subscription/return/{module}/{plan}', function () {})->name('subscription.return');
        Route::get('subscription/cancel/{module}/{plan}', function () {})->name('subscription.cancel');
        $this->app['router']->getRoutes()->refreshNameLookups();
    }

    public function test_register_and_get_driver()
    {
        $driverMock = Mockery::mock(SubscriptionMethod::class);
        $this->subscriptionManager->registerDriver('test_driver', function () use ($driverMock) {
            return $driverMock;
        });

        $this->assertTrue($this->subscriptionManager->drivers()->has('test_driver'));
        $this->assertSame($driverMock, $this->subscriptionManager->driver('test_driver'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Payment driver [test_driver] already registered.');
        $this->subscriptionManager->registerDriver('test_driver', function () use ($driverMock) {
            return $driverMock;
        });
    }

    public function test_get_unregistered_driver()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Payment driver [unregistered_driver] is not registered.');
        $this->subscriptionManager->driver('unregistered_driver');
    }

    public function test_register_and_get_module()
    {
        $moduleMock = Mockery::mock(SubscriptionModule::class);
        $this->subscriptionManager->registerModule('test_module', function () use ($moduleMock) {
            return $moduleMock;
        });

        $this->assertTrue($this->subscriptionManager->hasModule('test_module'));
        $this->assertTrue($this->subscriptionManager->modules()->has('test_module'));
        $this->assertSame($moduleMock, $this->subscriptionManager->module('test_module'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Payment module [test_module] already registered.');
        $this->subscriptionManager->registerModule('test_module', function () use ($moduleMock) {
            return $moduleMock;
        });
    }

    public function test_get_unregistered_module()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Payment module [unregistered_module] is not registered.');
        $this->subscriptionManager->module('unregistered_module');
    }

    public function test_features()
    {
        $this->subscriptionManager->feature('test_feature', 'test_module', function () {
            return ['label' => 'Test Feature'];
        });

        $features = $this->subscriptionManager->features('test_module');
        $this->assertInstanceOf(Collection::class, $features);
        $this->assertCount(1, $features);

        $feature = $features->first();
        $this->assertInstanceOf(Feature::class, $feature);
        $this->assertEquals('test_feature', $feature->name);
        $this->assertEquals('Test Feature', $feature->label);
    }

    public function test_render_config_throws_exception_for_empty_configs()
    {
        $driverMock = Mockery::mock(SubscriptionMethod::class);
        $driverMock->shouldReceive('getConfigs')->once()->andReturn([]);
        $driverMock->shouldReceive('hasSandbox')->once()->andReturn(false);

        $this->subscriptionManager->registerDriver('test_driver', function () use ($driverMock) {
            return $driverMock;
        });

        if (! class_exists(\Juzaweb\Modules\Payment\Exceptions\PaymentException::class)) {
            $this->expectException(\Error::class);
            $this->expectExceptionMessage('Class "Juzaweb\Modules\Payment\Exceptions\PaymentException" not found');
        } else {
            $this->expectException(\Juzaweb\Modules\Payment\Exceptions\PaymentException::class);
            $this->expectExceptionMessage('Subscription driver [test_driver] has no configuration.');
        }

        $this->subscriptionManager->renderConfig('test_driver');
    }

    public function test_cancel()
    {
        $historyMock = Mockery::mock(SubscriptionHistory::class)->makePartial();
        $historyMock->shouldReceive('update')->once()->with(['status' => SubscriptionHistoryStatus::CANCELLED]);
        $historyMock->module = 'test_module';

        $moduleMock = Mockery::mock(SubscriptionModule::class);
        $moduleMock->shouldReceive('onCancel')->once()->with($historyMock, ['param1' => 'value1']);

        $this->subscriptionManager->registerModule('test_module', function () use ($moduleMock) {
            return $moduleMock;
        });

        $result = $this->subscriptionManager->cancel($historyMock, ['param1' => 'value1']);
        $this->assertTrue($result);
    }

    public function test_complete_failed()
    {
        setting()->set('subscription_sandbox', true);

        $methodConfig = ['key' => 'value'];
        $methodMock = new SubscriptionMethodModel;
        $methodMock->config = $methodConfig;

        $historyMock = Mockery::mock(SubscriptionHistory::class)->makePartial();
        $historyMock->driver = 'test_driver';
        $historyMock->method = $methodMock;
        $historyMock->shouldReceive('update')->once()->with(['status' => SubscriptionHistoryStatus::FAILED]);

        $subscriptionMethodMock = Mockery::mock(SubscriptionMethod::class);
        $subscriptionMethodMock->shouldReceive('setConfigs')->once()->with($methodConfig)->andReturnSelf();
        $subscriptionMethodMock->shouldReceive('sandbox')->once()->andReturnSelf();

        $returnResultMock = Mockery::mock(SubscriptionReturnResult::class);
        $returnResultMock->shouldReceive('isSuccessful')->once()->andReturn(false);

        $subscriptionMethodMock->shouldReceive('complete')->once()->with($historyMock, ['param1' => 'value1'])->andReturn($returnResultMock);

        $this->subscriptionManager->registerDriver('test_driver', function () use ($subscriptionMethodMock) {
            return $subscriptionMethodMock;
        });

        $result = $this->subscriptionManager->complete($historyMock, ['param1' => 'value1']);
        $this->assertSame($returnResultMock, $result);
    }

    public function test_create_successful()
    {
        setting()->set('subscription_sandbox', true);

        $userMock = Mockery::mock(Authenticatable::class)->makePartial();
        $userMock->name = 'Test User';
        $userMock->email = 'test@example.com';
        $userMock->shouldReceive('getAttribute')->with('name')->andReturn('Test User');
        $userMock->shouldReceive('getAttribute')->with('email')->andReturn('test@example.com');

        $subscriptableMock = Mockery::mock(Model::class.', '.Subscriptable::class)->makePartial();
        $subscriptableMock->shouldReceive('getKey')->andReturn(1);
        $subscriptableMock->shouldReceive('getMorphClass')->andReturn('App\Models\User');
        $subscriptableMock->id = 1;

        $planMock = new Plan;
        $planMock->id = 1;
        $planMock->price = 10.0;

        $methodConfig = ['key' => 'value'];
        $methodMock = new SubscriptionMethodModel;
        $methodMock->id = 1;
        $methodMock->driver = 'test_driver';
        $methodMock->config = $methodConfig;

        $subscriptionMethodMock = Mockery::mock(SubscriptionMethod::class);
        $subscriptionMethodMock->shouldReceive('setConfigs')->once()->with($methodConfig)->andReturnSelf();
        $subscriptionMethodMock->shouldReceive('sandbox')->once()->andReturnSelf();

        $subscribeResultMock = Mockery::mock(SubscribeResult::class);
        $subscribeResultMock->shouldReceive('getTransactionId')->once()->andReturn('trans_123');
        $subscribeResultMock->shouldReceive('setSubscriptionHistory')->twice();
        $subscribeResultMock->shouldReceive('isSuccessful')->once()->andReturn(true);

        $subscriptionMethodMock->shouldReceive('subscribe')->once()->with($planMock, Mockery::type('array'))->andReturn($subscribeResultMock);

        $this->subscriptionManager->registerDriver('test_driver', function () use ($subscriptionMethodMock) {
            return $subscriptionMethodMock;
        });

        $moduleMock = Mockery::mock(SubscriptionModule::class);
        $moduleMock->shouldReceive('getServiceName')->once()->andReturn('Test Service');
        $moduleMock->shouldReceive('getServiceDescription')->once()->andReturn('Test Description');
        $moduleMock->shouldReceive('onSuccess')->once()->with($subscribeResultMock, ['param' => 'value']);

        $this->subscriptionManager->registerModule('test_module', function () use ($moduleMock) {
            return $moduleMock;
        });

        $result = $this->subscriptionManager->create(
            $userMock,
            $subscriptableMock,
            'test_module',
            $planMock,
            $methodMock,
            ['param' => 'value']
        );

        $this->assertSame($subscribeResultMock, $result);

        $history = SubscriptionHistory::where('agreement_id', 'trans_123')->first();
        $this->assertNotNull($history);
        $this->assertEquals(SubscriptionHistoryStatus::SUCCESS, $history->status);
    }

    public function test_webhook_return_null()
    {
        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('all')->andReturn(['payload' => 'data']);

        $methodMock = new SubscriptionMethodModel;
        $methodMock->config = ['key' => 'value'];
        $methodMock->driver = 'test_driver';
        $methodMock->save();

        $subscriptionMethodMock = Mockery::mock(SubscriptionMethod::class);
        $subscriptionMethodMock->shouldReceive('setConfigs')->once()->andReturnSelf();
        $subscriptionMethodMock->shouldReceive('sandbox')->once()->andReturnSelf();
        $subscriptionMethodMock->shouldReceive('webhook')->once()->with($requestMock)->andReturn(null);

        $this->subscriptionManager->registerDriver('test_driver', function () use ($subscriptionMethodMock) {
            return $subscriptionMethodMock;
        });

        $this->subscriptionManager->webhook($requestMock, 'test_driver');
        $this->assertTrue(true);
    }

    public function test_webhook_return_false()
    {
        $requestMock = Mockery::mock(Request::class);
        $requestMock->shouldReceive('all')->andReturn(['payload' => 'data']);

        $methodMock = new SubscriptionMethodModel;
        $methodMock->config = ['key' => 'value'];
        $methodMock->driver = 'test_driver';
        $methodMock->save();

        $webhookResultMock = Mockery::mock(WebhookResult::class);
        $webhookResultMock->shouldReceive('isSuccessful')->once()->andReturn(false);
        $webhookResultMock->shouldReceive('getTransactionId')->once()->andReturn('trans_456');
        $webhookResultMock->shouldReceive('getStatus')->once()->andReturn('failed');

        $subscriptionMethodMock = Mockery::mock(SubscriptionMethod::class);
        $subscriptionMethodMock->shouldReceive('setConfigs')->once()->andReturnSelf();
        $subscriptionMethodMock->shouldReceive('sandbox')->once()->andReturnSelf();
        $subscriptionMethodMock->shouldReceive('webhook')->once()->with($requestMock)->andReturn($webhookResultMock);

        $this->subscriptionManager->registerDriver('test_driver', function () use ($subscriptionMethodMock) {
            return $subscriptionMethodMock;
        });

        $this->subscriptionManager->webhook($requestMock, 'test_driver');
        $this->assertTrue(true);
    }
}
