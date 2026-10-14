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
            'workspace_preferences' => static function () use ($request): ?array {
                if (!$request->is('pos','pos/*')) return null;
                $context=app(\App\Application\Organization\OrganizationalContextStore::class)->current();
                if ($context===null) return null;
                if (!app('db')->connection()->getSchemaBuilder()->hasTable('oneqay_business_settings')) return null;
                return app(\App\Application\Business\Merchandising::class)->preferences();
            },
        ];
    }
}
