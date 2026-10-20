<?php
// Author by Lab | zefry
declare(strict_types=1);
namespace App\Application\Business;
use App\Application\Organization\VerifiedOrganizationalContext;
interface WorkforceAccounts {
 public function jobs(VerifiedOrganizationalContext $scope):array;
 public function saveJob(VerifiedOrganizationalContext $scope,array $input):array;
 public function accounts(VerifiedOrganizationalContext $scope):array;
 public function sync(VerifiedOrganizationalContext $scope,array $employee,?array $account):void;
}
