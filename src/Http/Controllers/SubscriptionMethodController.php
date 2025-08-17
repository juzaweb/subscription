<?php

namespace Juzaweb\Modules\Subscription\Http\Controllers;

use Illuminate\Routing\Controller;

class SubscriptionMethodController extends Controller
{
    public function index()
    {
        return view('subscription::index');
    }

    public function create()
    {
        return view('subscription::form');
    }

    public function edit(int $id)
    {
        return view('subscription::form');
    }
}
