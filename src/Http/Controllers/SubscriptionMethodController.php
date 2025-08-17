<?php

namespace Juzaweb\Modules\Subscription\Http\Controllers;

use Juzaweb\Core\Facades\Breadcrumb;
use Juzaweb\Core\Http\Controllers\AdminController;
use Juzaweb\Modules\Subscription\Http\DataTables\SubscriptionMethodsDataTable;
use Juzaweb\Modules\Subscription\Models\SubscriptionMethod;

class SubscriptionMethodController extends AdminController
{
    public function index(SubscriptionMethodsDataTable $dataTable)
    {
        Breadcrumb::add(__('Subscription Methods'));

        return $dataTable->render('subscription::method.index');
    }

    public function create()
    {
        Breadcrumb::add(__('Subscription Methods'), admin_url('subscription-methods'));

        Breadcrumb::add(__('Create Subscription Method'));

        $locale = $this->getFormLanguage();

        return view(
            'subscription::method.form',
            [
                'model' => new SubscriptionMethod(),
                'action' => action([static::class, 'store']),
                'locale' => $locale,
            ]
        );
    }

    public function edit(int $id)
    {
        return view('subscription::method.form');
    }

    public function store()
    {
        // Logic to store the subscription method
    }

    public function update(int $id)
    {
        // Logic to update the subscription method
    }

    public function destroy(int $id)
    {
        // Logic to delete the subscription method
    }
}
