<?php
/**
 * JUZAWEB CMS - Laravel CMS for Your Project
 *
 * @package    juzaweb/cms
 * @author     The Anh Dang
 * @link       https://cms.juzaweb.com
 * @license    GNU V2
 */

namespace Juzaweb\Modules\Subscription\Http\Middleware;

use Illuminate\Http\Request;
use Juzaweb\Modules\Subscription\Facades\Subscription;

class ModuleValid
{
    public function handle(Request $request, \Closure $next)
    {
        $module = $request->route('module');

        abort_if(!$module || !Subscription::hasModule($module), 404);

        return $next($request);
    }
}
