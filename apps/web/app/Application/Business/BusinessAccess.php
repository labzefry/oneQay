<?php

declare(strict_types=1);

namespace App\Application\Business;

use App\Application\Authorization\DurableScopedAuthorizationPolicy;
use App\Application\Authorization\PermissionIdentifier;
use App\Application\Organization\OrganizationalContextStore;
use App\Application\Organization\VerifiedOrganizationalContext;

// Author by Lab | zefry
final readonly class BusinessAccess
{
    public const MODULES = [
        'suppliers' => ['Suppliers', 'business.suppliers.view'],
        'purchases' => ['Purchasing', 'business.purchases.view'],
        'customers' => ['Customers', 'business.customers.view'],
        'finance' => ['Finance', 'business.finance.view'],
        'people' => ['People', 'business.people.view'],
        'audit' => ['Activity log', 'business.audit.view'],
        'access' => ['Business access', 'authorization.policy.manage'],
    ];
    public const ACTIONS = [
        'supplier.save' => 'business.suppliers.manage', 'customer.save' => 'business.customers.manage',
        'customer.interact' => 'business.customers.manage', 'purchase.create' => 'business.purchases.manage',
        'purchase.submit' => 'business.purchases.manage', 'purchase.cancel' => 'business.purchases.manage',
        'purchase.approve' => 'business.purchases.approve', 'purchase.reject' => 'business.purchases.approve',
        'purchase.receive' => 'business.purchases.receive', 'invoice.create' => 'business.finance.post',
        'invoice.settle' => 'business.finance.settle', 'journal.post' => 'business.finance.post',
        'journal.reverse' => 'business.finance.post', 'employee.save' => 'business.people.manage',
        'attendance.record' => 'business.people.manage', 'leave.request' => 'business.people.manage',
        'leave.approve' => 'business.people.approve', 'leave.reject' => 'business.people.approve',
    ];

    public function __construct(private OrganizationalContextStore $contexts, private DurableScopedAuthorizationPolicy $policy) {}

    public function context(): VerifiedOrganizationalContext
    {
        $context = $this->contexts->current();
        if ($context === null || $context->outletId() === null || $context->deviceId() === null) throw new BusinessViolation('CONTEXT_REQUIRED', 403);
        return $context;
    }

    public function allows(string $permission): bool
    {
        return $this->policy->allows($this->contexts->current(), PermissionIdentifier::fromString($permission));
    }

    public function require(string $permission): VerifiedOrganizationalContext
    {
        $scope = $this->context();
        $this->policy->require($scope, PermissionIdentifier::fromString($permission));
        return $scope;
    }

    public function modules(): array
    {
        $result = [];
        foreach (self::MODULES as $key => [$title, $permission]) {
            if ($this->allows($permission)) $result[] = ['key' => 'business_'.$key, 'title' => $title, 'description' => $title, 'url' => '/pos/business/'.$key, 'category' => 'MANAGE'];
        }
        return $result;
    }

    public function actions(): array
    {
        return array_keys(array_filter(self::ACTIONS, fn (string $permission): bool => $this->allows($permission)));
    }
}
