<?php
declare(strict_types=1);
namespace App\Application\Pos;
use App\Application\Authorization\{AdministrationPermission,DurableScopedAuthorizationPolicy,PermissionIdentifier,PosPermission};
use App\Application\Organization\OrganizationalContextStore;
// Author by Lab | zefry. Viewing the workflow never grants mutation permissions.
final readonly class ViewShiftClosureWorkspace
{
    public function __construct(private OrganizationalContextStore $contexts,private DurableScopedAuthorizationPolicy $policy,private ShiftClosureWorkspaceRepository $reader) {}
    public function view(): array
    {
        $scope=$this->contexts->current();$context=PosExecutionContext::fromVerified($scope);
        $can=fn(string $permission):bool=>$this->policy->allows($scope,PermissionIdentifier::fromString($permission));
        $permissions=['record_cash'=>$can(PosPermission::recordShiftClosingCash()->value()),'close'=>$can(FinalShiftClosePermission::IDENTIFIER),'manage_access'=>$can(AdministrationPermission::MANAGE)];
        if(!in_array(true,$permissions,true))$this->policy->require($scope,FinalShiftClosePermission::identifier());
        return ['permissions'=>$permissions,'shift'=>$this->reader->read($context)];
    }
}
