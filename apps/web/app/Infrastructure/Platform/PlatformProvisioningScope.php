<?php
declare(strict_types=1);
namespace App\Infrastructure\Platform;
use App\Infrastructure\Runtime\ProductionBusinessRuntimeGate;
use App\Application\Platform\PlatformViolation;
// Author by Lab | zefry. Short-lived, exact invitation capability; never changes runtime class.
final class PlatformProvisioningScope
{
    private static ?string $tenant=null;
    public static function execute(array $business,callable $work):mixed
    {
        if(self::$tenant!==null || ($business['status']??null)!=='invited' || (int)($business['token_expires_at']??0)<=time()
            || !config('platform.enabled',false) || !ProductionBusinessRuntimeGate::allows((string)config('oneqay.runtime_class','')))throw new PlatformViolation('PROVISIONING_SCOPE_DENIED',403);
        self::$tenant=$business['tenant_id'];
        try{return $work();}finally{self::$tenant=null;}
    }
    public static function allows(string $runtime,?string $tenant=null):bool
    { return self::$tenant!==null && ($tenant===null || hash_equals(self::$tenant,$tenant)) && config('platform.enabled',false)===true && ProductionBusinessRuntimeGate::allows($runtime); }
}
