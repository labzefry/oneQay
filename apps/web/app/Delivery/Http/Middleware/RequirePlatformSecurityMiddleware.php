<?php
declare(strict_types=1);
namespace App\Delivery\Http\Middleware;
use App\Application\Platform\PlatformOwnerAccess;
use Illuminate\Http\Request;
use Closure;
// Author by Lab | zefry. Writes also run canonical policy step-up middleware.
final readonly class RequirePlatformSecurityMiddleware
{
    public function __construct(private PlatformOwnerAccess $access) {}
    public function handle(Request $r,Closure $next):mixed
    {
        abort_unless($this->access->allows(),403);
        if(!$r->isMethod('GET'))abort_unless(config('oneqay.privileged_totp_mfa.enabled')===true && config('oneqay.privileged_step_up.enabled')===true && (int)config('oneqay.privileged_step_up.freshness_seconds')===300,403);
        return $next($r);
    }
}
