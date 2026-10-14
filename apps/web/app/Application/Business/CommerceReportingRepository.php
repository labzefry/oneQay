<?php
declare(strict_types=1);
namespace App\Application\Business;
use App\Application\Organization\VerifiedOrganizationalContext;
// Author by Lab | zefry.
interface CommerceReportingRepository { public function sales(VerifiedOrganizationalContext $scope,string $from,string $to):array; }
