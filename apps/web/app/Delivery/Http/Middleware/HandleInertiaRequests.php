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
            'communications' => static function () use ($request): ?array {
                if (!$request->is('pos','pos/*') || !config('platform.enabled')) return null;
                $context=app(\App\Application\Organization\OrganizationalContextStore::class)->current();
                if ($context===null) return null;
                $db=app('db')->connection();
                if (!$db->getSchemaBuilder()->hasTable('oneqay_platform_campaigns')) return null;
                $campaigns=app(\App\Infrastructure\Platform\ControlPlaneCampaigns::class);
                $business=$db->table('oneqay_platform_businesses')->where('tenant_id',$context->tenantId()->value())->first();
                return ['announcements'=>$campaigns->announcements($context->tenantId()->value()),'can_manage'=>$business!==null && app(\App\Application\Business\BusinessAccess::class)->allows('authorization.policy.manage'),'subscribed'=>$business!==null && $campaigns->subscribed($business->id)];
            },
            'workspace_preferences'  => static function () use ($request): ?array {
                if (!$request->is('pos','pos/*')) return null;
                $context=app(\App\Application\Organization\OrganizationalContextStore::class)->current();
                if ($context===null) return null;
                if (!app('db')->connection()->getSchemaBuilder()->hasTable('oneqay_business_settings')) return null;
                return app(\App\Application\Business\Merchandising::class)->preferences();
            },
        ];
    }
}
