<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Modules\Subscription\Http\Controllers;

use Juzaweb\Core\Http\Controllers\AdminController;
use Juzaweb\Modules\Subscription\Http\DataTables\PlansDataTable;

class PlanController extends AdminController
{
    public function index(PlansDataTable $dataTable, string $module)
    {


        return view(
            'subscription::plan.index',
            compact('dataTable', 'module')
        );
    }
}
