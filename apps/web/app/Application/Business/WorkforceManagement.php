<?php

declare(strict_types=1);

namespace App\Application\Business;

use App\Application\Organization\VerifiedOrganizationalContext;

// Author by Lab | zefry
final readonly class WorkforceManagement
{
    public function __construct(private BusinessRepository $records) {}

    public function save(VerifiedOrganizationalContext $scope, array $input): array
    {
        BusinessInput::fields($input, ['code', 'name', 'department', 'job_title', 'start_date', 'active'], ['id', 'version']);
        $data = ['code' => BusinessInput::text($input['code'], 48), 'name' => BusinessInput::text($input['name']), 'department' => BusinessInput::text($input['department'], 120), 'job_title' => BusinessInput::text($input['job_title'], 120), 'start_date' => BusinessInput::date($input['start_date']), 'active' => BusinessInput::boolean($input['active'])];
        if (isset($input['id'])) {
            $existing = $this->records->find($scope, 'employees', BusinessInput::id($input['id']), true);
            return $this->records->update($scope, 'employees', $existing['id'], BusinessInput::integer($input['version'] ?? null, 1), $data);
        }
        if (isset($input['version'])) throw new BusinessViolation('INVALID_FIELDS');
        return $this->records->create($scope, 'employees', $data);
    }

    public function attendance(VerifiedOrganizationalContext $scope, array $input): array
    {
        BusinessInput::fields($input, ['employee_id', 'work_date', 'clock_in', 'clock_out', 'note'], ['id', 'version']);
        $employee = $this->records->find($scope, 'employees', BusinessInput::id($input['employee_id']), true);
        if (!$employee['active']) throw new BusinessViolation('EMPLOYEE_NOT_ACTIVE');
        $date = BusinessInput::date($input['work_date']);
        $in = BusinessInput::integer($input['clock_in'], 1, time() + 86400);
        $out = $input['clock_out'] === '' ? null : BusinessInput::integer($input['clock_out'], 1, time() + 86400);
        $localDay = (new \DateTimeImmutable('@'.$in))->setTimezone(new \DateTimeZone('Asia/Jakarta'))->format('Y-m-d');
        if ($date !== $localDay || $date < $employee['start_date'] || ($out !== null && ($out <= $in || $out - $in > 86400))) throw new BusinessViolation('INVALID_ATTENDANCE_TIME');
        $data = ['employee_id' => $employee['id'], 'work_date' => $date, 'clock_in' => $in, 'clock_out' => $out, 'note' => BusinessInput::text($input['note'], 1000)];
        if (isset($input['id'])) {
            $existing = $this->records->find($scope, 'attendance', BusinessInput::id($input['id']), true);
            if ($existing['employee_id'] !== $employee['id'] || $existing['work_date'] !== $date) throw new BusinessViolation('ATTENDANCE_IDENTITY_CHANGED');
            return $this->records->update($scope, 'attendance', $existing['id'], BusinessInput::integer($input['version'] ?? null, 1), $data);
        }
        return $this->records->create($scope, 'attendance', $data);
    }

    public function leave(VerifiedOrganizationalContext $scope, array $input): array
    {
        BusinessInput::fields($input, ['employee_id', 'start_date', 'end_date', 'reason']);
        // Locking the employee also serializes overlapping leave submissions.
        $employee = $this->records->find($scope, 'employees', BusinessInput::id($input['employee_id']), true);
        if (!$employee['active']) throw new BusinessViolation('EMPLOYEE_NOT_ACTIVE');
        $from = BusinessInput::date($input['start_date']); $to = BusinessInput::date($input['end_date']);
        if ($to < $from || $from < $employee['start_date'] || (new \DateTimeImmutable($from))->diff(new \DateTimeImmutable($to))->days > 366) throw new BusinessViolation('INVALID_LEAVE_DATES');
        if ($this->records->leaveOverlaps($scope, $employee['id'], $from, $to)) throw new BusinessViolation('LEAVE_OVERLAP', 409);
        return $this->records->create($scope, 'leave', ['employee_id' => $employee['id'], 'start_date' => $from, 'end_date' => $to, 'reason' => BusinessInput::text($input['reason'], 1000), 'status' => 'PENDING', 'approved_by' => null]);
    }

    public function decide(VerifiedOrganizationalContext $scope, array $input, bool $approve): array
    {
        BusinessInput::fields($input, ['id', 'version']);
        $leave = $this->records->find($scope, 'leave', BusinessInput::id($input['id']), true);
        if ($leave['status'] !== 'PENDING') throw new BusinessViolation('LEAVE_ALREADY_DECIDED', 409);
        return $this->records->update($scope, 'leave', $leave['id'], BusinessInput::integer($input['version'], 1), ['status' => $approve ? 'APPROVED' : 'REJECTED', 'approved_by' => $scope->identityId()->value()]);
    }
}
