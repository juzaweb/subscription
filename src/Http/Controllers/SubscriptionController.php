<?php

/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    larabizcom/larabiz
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 */

namespace Juzaweb\Modules\Subscription\Http\Controllers;

use Juzaweb\Modules\Core\Facades\Breadcrumb;
use Juzaweb\Modules\Core\Http\Controllers\AdminController;
use Juzaweb\Modules\Subscription\Http\DataTables\SubscriptionsDatatable;

class SubscriptionController extends AdminController
{
    public function index(SubscriptionsDatatable $dataTable, string $module)
    {
        Breadcrumb::add(__('Subscriptions'));

        return $dataTable->render(
            'subscription::subscription.index'
        );
    }
}
