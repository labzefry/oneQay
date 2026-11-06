<?php
// Author by Lab | zefry. Disposable SQL; persistent native session, browser TTL and revocation.
declare(strict_types=1);
require __DIR__.'/desktop-enterprise.php';
(require __DIR__.'/../database/module-migrations/pos/2026_11_05_000001_add_persistent_desktop_session_kind.php')->up();
$contexts->setVerified($businessOwner);
$settings->cashier(array_replace($cashier,['version'=>2,'password'=>'','pin'=>'','enabled'=>true]));
$connected=$login->login(['login'=>$cashier['login'],'password'=>$cashier['password'],'totp'=>''],'persistent-native-login',true);
$desktop->authenticate($connected['token']);
$row=$db->table('oneqay_desktop_credentials')->where('token_hash',hash('sha256',$connected['token']))->first();
$a=json_decode($app->make('encrypter')->decryptString($row->authority),true,32,JSON_THROW_ON_ERROR);
$record=$db->table('oneqay_identity_first_party_sessions')->where('tenant_id',$a['tenant'])->where('authority_id',$a['session'])->first();
$assert($record->authority_kind==='desktop_persistent'&&(int)$record->expires_at_unix===PHP_INT_MAX&&(int)$row->expires_at===PHP_INT_MAX,'native grant has explicit persistent kind');
$clock=new class implements App\Application\Identity\FirstPartySessionAuthorityClock {public int $time;public function nowUnix():int{return $this->time;}};
$clock->time=(int)$record->issued_at_unix+370*86400;
$authority=new App\Application\Identity\FirstPartySessionAuthorityService(
 $app->make(App\Application\Identity\FirstPartySessionAuthorityRepository::class),$clock,
 $app->make(App\Application\Identity\FirstPartyCredentialEpochRepository::class),
 $app->make(App\Application\Identity\PrivilegedTotpFactorEpochRepository::class),
 $app->make(App\Application\Identity\PrivilegedTotpMfaService::class),
 (bool)config('oneqay.privileged_totp_mfa.enabled'),7200,43200
);
$tenant=App\Domain\Tenancy\TenantId::fromString($a['tenant']);$identity=App\Domain\Identity\PlatformIdentityId::fromString($a['identity']);
$args=[$tenant,$identity,$a['session'],$a['organization'],$a['outlet'],$a['device'],$a['credential_epoch'],$a['factor_epoch']];
$authority->assertPersistentDesktopCurrent(...$args);$assert(true,'persistent native grant survives browser idle and absolute TTL');
try{$authority->assertActiveCurrent(...$args);$assert(false,'native grant entered browser');}catch(App\Application\Identity\FirstPartySessionAuthorityViolation){$assert(true,'browser rejects native grant');}
$bad=$args;$bad[3]='different-org';
try{$authority->assertPersistentDesktopCurrent(...$bad);$assert(false,'cross-context grant accepted');}catch(App\Application\Identity\FirstPartySessionAuthorityViolation){$assert(true,'scope binding enforced');}
$q=$db->table('oneqay_identity_first_party_sessions')->where('tenant_id',$a['tenant'])->where('authority_id',$a['session']);
$q->update(['authority_kind'=>'unknown']);
try{$authority->assertPersistentDesktopCurrent(...$args);$assert(false,'unknown kind accepted');}catch(App\Application\Identity\FirstPartySessionAuthorityViolation){$assert(true,'unknown kind fails closed');}
$q->update(['authority_kind'=>'desktop_persistent']);
$browser=$authority->issue($tenant,$identity,$a['organization'],$a['outlet'],$a['device'],$a['credential_epoch'],$a['factor_epoch'],'persistent-inventory-browser');
$browserArgs=[$tenant,$identity,$browser->authorityId(),$a['organization'],$a['outlet'],$a['device'],$a['credential_epoch'],$a['factor_epoch']];
$items=array_map(fn($i)=>$i->toArray(),$authority->inventory(...$browserArgs));
$listed=array_values(array_filter($items,fn($i)=>($i['session_kind']??'')==='desktop_persistent'));
$assert(count($listed)===1&&$listed[0]['persistent_until_logout']===true,'persistent grant remains visible until logout');
$clock->time+=7201;
try{$authority->assertActiveCurrent(...$browserArgs);$assert(false,'browser idle expiry removed');}catch(App\Application\Identity\FirstPartySessionAuthorityViolation){$assert(true,'browser idle expiry unchanged');}
$absolute=$authority->issue($tenant,$identity,$a['organization'],$a['outlet'],$a['device'],$a['credential_epoch'],$a['factor_epoch'],'persistent-browser-absolute');
$absoluteArgs=$browserArgs;$absoluteArgs[2]=$absolute->authorityId();
$db->table('oneqay_identity_first_party_sessions')->where('tenant_id',$a['tenant'])->where('authority_id',$absolute->authorityId())->update(['expires_at_unix'=>PHP_INT_MAX]);
$clock->time+=43201;
try{$authority->assertActiveCurrent(...$absoluteArgs);$assert(false,'inflated browser expiry became persistent');}catch(App\Application\Identity\FirstPartySessionAuthorityViolation){$assert(true,'browser absolute TTL enforced even with inflated stored expiry');}
$browser=$authority->issue($tenant,$identity,$a['organization'],$a['outlet'],$a['device'],$a['credential_epoch'],$a['factor_epoch'],'persistent-revoke-browser');
$browserArgs[2]=$browser->authorityId();$authority->revokeOthers(...[...$browserArgs,'persistent-revoke-others']);
try{$desktop->authenticate($connected['token']);$assert(false,'revoked bearer accepted');}catch(Symfony\Component\HttpKernel\Exception\HttpException){$assert(true,'revoke others terminates native grant');}
$second=$login->login(['login'=>$cashier['login'],'password'=>$cashier['password'],'totp'=>''],'persistent-second-login',true);
$contexts->setVerified($businessOwner);$settings->cashier(array_replace($cashier,['version'=>3,'password'=>'','pin'=>'','enabled'=>false]));
$assert($desktop->logoutPersistent($second['token'],'persistent-disabled-logout')['logged_out']===true,'disabled cashier can revoke own bearer');
$assert($desktop->logoutPersistent($second['token'],'persistent-logout-retry')['logged_out']===true,'logout idempotent');
$assert($db->table('oneqay_desktop_credentials')->where('token_hash',hash('sha256',$second['token']))->value('revoked_at')!==null,'logout revokes bearer durably');
echo 'DESKTOP_PERSISTENT_SESSION_'.$engine.'_PASS assertions='.$n.PHP_EOL;
