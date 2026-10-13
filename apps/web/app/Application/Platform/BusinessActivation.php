<?php
declare(strict_types=1);
namespace App\Application\Platform;
use App\Application\Notification\MailStore;
// Author by Lab | zefry
final readonly class BusinessActivation
{
    public function __construct(private PlatformOwnerAccess $access, private PlatformStore $store, private BusinessAccountProvisioner $accounts, private MailStore $mail, private string $publicUrl) {}

    public function command(array $input): array
    {
        $owner = $this->access->require();
        $actor = $owner->tenantId()->value().'/'.$owner->identityId()->value();
        $action = self::text($input['action'] ?? null, 24);
        $operation = self::text($input['operation_id'] ?? null, 128);
        if (!preg_match('/\A[a-zA-Z0-9_-]{8,128}\z/', $operation)) throw new PlatformViolation('INVALID_OPERATION');
        $reason = self::text($input['reason'] ?? null, 300);
        $allowed = $action === 'create' ? ['action','operation_id','reason','name','email','locale'] : ['action','operation_id','reason','id','version'];
        if (array_diff(array_keys($input), $allowed)) throw new PlatformViolation('UNKNOWN_FIELDS');
        ksort($input);
        return $this->store->once($actor, $operation, $input, function () use ($input, $action, $actor, $reason): array {
            $now = time();
            if ($action === 'create') {
                $email = self::email($input['email'] ?? null);
                $locale = $input['locale'] ?? 'id';
                if (!in_array($locale, ['id','en'], true)) throw new PlatformViolation('INVALID_LOCALE');
                $business = $this->store->create([
                    'id' => bin2hex(random_bytes(16)), 'name' => self::text($input['name'] ?? null, 160),
                    'email' => strtolower($email), 'locale' => $locale, 'status' => 'pending', 'version' => 1,
                    'tenant_id' => 'business-'.bin2hex(random_bytes(12)),
                    'identity_id' => 'owner-'.bin2hex(random_bytes(12)),
                    'organization_id' => 'org-'.bin2hex(random_bytes(12)),
                    'outlet_id' => 'outlet-'.bin2hex(random_bytes(12)),
                    'device_id' => 'device-'.bin2hex(random_bytes(12)),
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            } else {
                $business = $this->store->business(self::text($input['id'] ?? null, 32), true);
                if (!is_int($input['version'] ?? null) || $input['version'] !== (int)$business['version']) throw new PlatformViolation('STALE_VERSION', 409);
                if ($business['tenant_id'] === $this->access->require()->tenantId()->value()) throw new PlatformViolation('OWNER_TENANT_PROTECTED', 403);
                $changes = ['updated_at' => $now];
                if ($action === 'invite' && in_array($business['status'], ['pending','invited'], true)) {
                    if (!str_starts_with($this->publicUrl, 'https://')) throw new PlatformViolation('HTTPS_PUBLIC_URL_REQUIRED', 503);
                    $token = bin2hex(random_bytes(32));
                    $changes += ['status' => 'invited', 'token_hash' => hash('sha256', $token), 'token_expires_at' => $now + 86400];
                    // Fragment is read by the first-party page; proxies and referrers never see it.
                    $url = rtrim($this->publicUrl, '/').'/activate-business#token='.$token;
                    $subject = $business['locale'] === 'id' ? 'Aktivasi akun bisnis oneQay' : 'Activate your oneQay business account';
                    $body = $business['locale'] === 'id'
                        ? "Anda diundang mengaktifkan bisnis {$business['name']}.\nBuka tautan ini dalam 24 jam untuk menetapkan kata sandi:\n$url\nJika Anda tidak mengenali undangan ini, abaikan email ini.\nAuthor by Lab | zefry"
                        : "You are invited to activate {$business['name']}.\nOpen this link within 24 hours to set your password:\n$url\nIf you do not recognize this invitation, ignore this email.\nAuthor by Lab | zefry";
                    $this->mail->enqueue($business['tenant_id'], 'activation-'.$business['id'].'-'.$business['version'], $business['email'], $subject, $body, $now);
                } elseif ($action === 'suspend' && $business['status'] === 'active') {
                    $changes['status'] = 'suspended';
                    $this->accounts->revokeTenantSessions($business['tenant_id'], $now);
                } elseif ($action === 'resume' && $business['status'] === 'suspended') {
                    // Burn any sessions raced with suspension before allowing fresh login.
                    $this->accounts->revokeTenantSessions($business['tenant_id'], $now);
                    $changes['status'] = 'active';
                } elseif ($action === 'cancel' && in_array($business['status'], ['pending','invited'], true)) {
                    $changes += ['status' => 'cancelled', 'token_hash' => null, 'token_expires_at' => null];
                } else throw new PlatformViolation('INVALID_BUSINESS_TRANSITION', 409);
                $business = $this->store->update($business['id'], (int)$business['version'], $changes);
            }
            $this->store->audit($actor, $action, $business['id'], $reason, $now);
            return ['id' => $business['id'], 'status' => $business['status'], 'version' => (int)$business['version']];
        });
    }

    public function activate(#[\SensitiveParameter] string $token, #[\SensitiveParameter] string $password): array
    {
        if (!preg_match('/\A[a-f0-9]{64}\z/', $token) || strlen($password) < 12 || strlen($password) > 72) throw new PlatformViolation('INVALID_ACTIVATION');
        return $this->store->transaction(function () use ($token, $password): array {
            $business = $this->store->byToken(hash('sha256', $token));
            if ($business['status'] === 'active' && (int)$business['token_expires_at'] > time() && $this->accounts->verifyPassword($business, $password)) return ['business_id'=>$business['id']]+array_intersect_key($business, array_flip(['tenant_id','identity_id','organization_id','outlet_id','device_id']));
            if ($business['status'] !== 'invited' || (int)$business['token_expires_at'] <= time()) throw new PlatformViolation('INVALID_ACTIVATION');
            $this->accounts->provision($business, $password);
            $this->store->update($business['id'], (int)$business['version'], ['status' => 'active', 'token_expires_at' => time() + 300, 'updated_at' => time()]);
            $this->store->audit('invitation-recipient', 'activated', $business['id'], 'Recipient set initial password', time());
            return ['business_id'=>$business['id']]+array_intersect_key($business, array_flip(['tenant_id','identity_id','organization_id','outlet_id','device_id']));
        });
    }
    public static function text(mixed $value, int $max): string
    {
        if (!is_string($value) || trim($value) === '' || strlen($value) > $max || preg_match('/[\x00-\x1f\x7f]/', $value)) throw new PlatformViolation('INVALID_FIELDS');
        return trim($value);
    }
    public static function email(mixed $value): string
    {
        $value = self::text($value, 254);
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) throw new PlatformViolation('INVALID_EMAIL');
        // Preserve case in delivery address. Suppression uses a normalized fingerprint.
        return $value;
    }
}
