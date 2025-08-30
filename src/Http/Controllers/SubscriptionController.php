<?php

namespace Juzaweb\Modules\Subscription\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SubscriptionController extends Controller
{
    public function subscribe(Request $request, string $module)
    {
        
    }
    
    public function return(Request $request, string $module)
    {
        dd($request->all());
    }

    public function cancel(Request $request, string $module)
    {
        dd($request->all());
    }
}
