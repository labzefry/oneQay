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
            'branding'=>static function(){ $db=app('db')->connection();if(!config('database.oneqay_persistence_enabled')||!$db->getSchemaBuilder()->hasTable('oneqay_platform_branding'))return null;$id=$db->table('oneqay_platform_branding')->where('id',1)->value('media_id');return $id?['logo'=>'/site/media/'.$id]:null; },
            'business_profile'=>static function()use($request){$tenant=$request->session()->get(\App\Delivery\Http\Identity\FirstPartySessionKeys::TENANT);$p=is_string($tenant)?app(\App\Infrastructure\BusinessExperience\LaravelBusinessDomains::class)->profile($tenant):null;if($p)$p['can_manage_security']=$p['identity_id']===$request->session()->get(\App\Delivery\Http\Identity\FirstPartySessionKeys::IDENTITY)&&$tenant!==config('platform.owner_tenant');return $p;},
            'can_manage_business_security'=>static function()use($request):bool {
                $tenant=$request->session()->get(\App\Delivery\Http\Identity\FirstPartySessionKeys::TENANT);
                $identity=$request->session()->get(\App\Delivery\Http\Identity\FirstPartySessionKeys::IDENTITY);
                if(!is_string($tenant)||!is_string($identity)||$tenant===config('platform.owner_tenant')||!config('platform.enabled'))return false;
                $db=app('db')->connection();
                return $db->getSchemaBuilder()->hasTable('oneqay_platform_businesses')&&$db->table('oneqay_platform_businesses')->where('tenant_id',$tenant)->where('identity_id',$identity)->where('status','active')->exists();
            },
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
