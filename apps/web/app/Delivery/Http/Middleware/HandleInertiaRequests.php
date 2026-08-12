<?php

namespace App\Delivery\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

final class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'application' => [
                'name' => 'oneQay',
                'milestone' => 'M7.1',
            ],
        ];
    }
}
