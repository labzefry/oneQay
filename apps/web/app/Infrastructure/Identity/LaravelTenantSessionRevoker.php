<?php
declare(strict_types=1);
namespace App\Infrastructure\Identity;
use Illuminate\Database\Connection;
// Author by Lab | zefry. Identity module owns session storage; called by the authorized platform lifecycle adapter.
final readonly class LaravelTenantSessionRevoker
{
    public function __construct(private Connection $db) {}
    public function revoke(string $tenant,int $now):void
    {
        if($tenant===''||$now<=0)throw new \InvalidArgumentException('Invalid tenant session revocation');
        $this->db->table('oneqay_identity_first_party_sessions')->where('tenant_id',$tenant)->whereNull('revoked_at_unix')->update(['revoked_at_unix'=>$now]);
    }
}
