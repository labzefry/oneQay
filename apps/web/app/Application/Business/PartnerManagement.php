<?php

declare(strict_types=1);

namespace App\Application\Business;

use App\Application\Organization\VerifiedOrganizationalContext;

// Author by Lab | zefry
final readonly class PartnerManagement
{
    public function __construct(private BusinessRepository $records) {}

    public function save(VerifiedOrganizationalContext $scope, string $kind, array $input): array
    {
        BusinessInput::fields($input, ['code', 'name', 'email', 'phone', 'address', 'active', 'marketing_consent', 'consent_source'], ['id', 'version']);
        $email = BusinessInput::text($input['email'], 254, true);
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) throw new BusinessViolation('INVALID_EMAIL');
        $consent = BusinessInput::boolean($input['marketing_consent']);
        $source = BusinessInput::text($input['consent_source'], 160, !$consent);
        if ($kind === 'SUPPLIER' && $consent) throw new BusinessViolation('INVALID_CONSENT');
        $data = ['kind' => $kind, 'code' => BusinessInput::text($input['code'], 48), 'name' => BusinessInput::text($input['name']), 'email' => $email, 'phone' => BusinessInput::text($input['phone'], 48, true), 'address' => BusinessInput::text($input['address'], 1000, true), 'active' => BusinessInput::boolean($input['active']), 'marketing_consent' => $consent, 'consent_source' => $source, 'consent_at' => time()];
        if (isset($input['id'])) {
            $existing = $this->records->find($scope, 'partners', BusinessInput::id($input['id']), true);
            if ($existing['kind'] !== $kind) throw new BusinessViolation('RECORD_NOT_FOUND', 404);
            if ($existing['marketing_consent'] === $consent && $existing['consent_source'] === $source) $data['consent_at'] = $existing['consent_at'];
            return $this->records->update($scope, 'partners', $existing['id'], BusinessInput::integer($input['version'] ?? null, 1), $data);
        }
        if (isset($input['version'])) throw new BusinessViolation('INVALID_FIELDS');
        return $this->records->create($scope, 'partners', $data);
    }

    public function interaction(VerifiedOrganizationalContext $scope, array $input): array
    {
        BusinessInput::fields($input, ['customer_id', 'channel', 'note', 'follow_up_date']);
        $customer = $this->records->find($scope, 'partners', BusinessInput::id($input['customer_id']), true);
        if ($customer['kind'] !== 'CUSTOMER' || !$customer['active']) throw new BusinessViolation('CUSTOMER_NOT_ACTIVE');
        $channel = BusinessInput::text($input['channel'], 24);
        if (!in_array($channel, ['IN_PERSON', 'PHONE', 'EMAIL', 'CHAT', 'NOTE'], true)) throw new BusinessViolation('INVALID_CHANNEL');
        // Recording a contact does not send anything or imply marketing permission.
        return $this->records->create($scope, 'interactions', ['customer_id' => $customer['id'], 'channel' => $channel, 'note' => BusinessInput::text($input['note'], 2000), 'follow_up_date' => $input['follow_up_date'] === '' ? null : BusinessInput::date($input['follow_up_date'])]);
    }
}
