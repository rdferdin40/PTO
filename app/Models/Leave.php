<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Leave extends Model
{
    protected string $table = 'leaves';

    public function getByUser(int $userId, ?string $status = null): array
    {
        $sql = "SELECT l.*, lt.name as leave_type_name, lt.color as leave_type_color,
                CONCAT(m.first_name, ' ', m.last_name) as manager_name
                FROM {$this->table} l
                LEFT JOIN leave_types lt ON l.leave_type_id = lt.id
                LEFT JOIN users m ON l.manager_id = m.id
                WHERE l.user_id = ?";

        $params = [$userId];

        if ($status) {
            $sql .= " AND l.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY l.request_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getPendingForManager(int $managerId): array
    {
        $managerStmt = $this->db->prepare("SELECT department_id FROM users WHERE id = ?");
        $managerStmt->execute([$managerId]);
        $manager = $managerStmt->fetch();

        if (!$manager || !$manager['department_id']) {
            return [];
        }

        $sql = "SELECT l.*, lt.name as leave_type_name, lt.color as leave_type_color,
                CONCAT(u.first_name, ' ', u.last_name) as user_name, u.email as user_email
                FROM {$this->table} l
                LEFT JOIN leave_types lt ON l.leave_type_id = lt.id
                LEFT JOIN users u ON l.user_id = u.id
                WHERE l.status = 'pending'
                AND u.department_id = ?
                AND u.id != ?
                ORDER BY l.request_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$manager['department_id'], $managerId]);
        return $stmt->fetchAll();
    }

    public function getByDateRange(string $startDate, string $endDate, ?int $companyId = null, ?int $departmentId = null): array
    {
        $sql = "SELECT l.*, lt.name as leave_type_name, lt.color as leave_type_color,
                CONCAT(u.first_name, ' ', u.last_name) as user_name, d.name as department_name
                FROM {$this->table} l
                LEFT JOIN leave_types lt ON l.leave_type_id = lt.id
                LEFT JOIN users u ON l.user_id = u.id
                LEFT JOIN departments d ON u.department_id = d.id
                WHERE l.status = 'approved'
                AND l.start_date <= ?
                AND l.end_date >= ?";

        $params = [$endDate, $startDate];

        if ($companyId) {
            $sql .= " AND l.company_id = ?";
            $params[] = $companyId;
        }

        if ($departmentId) {
            $sql .= " AND u.department_id = ?";
            $params[] = $departmentId;
        }

        $sql .= " ORDER BY l.start_date";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getUsedDays(int $userId, int $year): float
    {
        $sql = "SELECT COALESCE(SUM(days), 0) as used_days
                FROM {$this->table} l
                LEFT JOIN leave_types lt ON l.leave_type_id = lt.id
                WHERE l.user_id = ?
                AND l.status = 'approved'
                AND lt.deducts_allowance = 1
                AND YEAR(l.start_date) = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $year]);
        $result = $stmt->fetch();

        return (float) $result['used_days'];
    }

    public function approve(int $id, int $managerId, ?string $comment = null): bool
    {
        $sql = "UPDATE {$this->table}
                SET status = 'approved', manager_id = ?, manager_comment = ?, updated_at = NOW()
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$managerId, $comment, $id]);
    }

    public function reject(int $id, int $managerId, ?string $comment = null): bool
    {
        $sql = "UPDATE {$this->table}
                SET status = 'rejected', manager_id = ?, manager_comment = ?, updated_at = NOW()
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$managerId, $comment, $id]);
    }

    public function cancel(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET status = 'cancelled', updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function getByUserDateRange(int $userId, string $startDate, string $endDate): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE user_id = ?
                AND status != 'cancelled'
                AND status != 'rejected'
                AND (
                    (start_date <= ? AND end_date >= ?)
                    OR (start_date <= ? AND end_date >= ?)
                    OR (start_date >= ? AND end_date <= ?)
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $endDate, $startDate, $startDate, $startDate, $endDate, $endDate, $startDate, $endDate]);
        return $stmt->fetchAll();
    }

    public function findWithRelations(int $id): ?array
    {
        $sql = "SELECT l.*, lt.name as leave_type_name, lt.color as leave_type_color,
                CONCAT(u.first_name, ' ', u.last_name) as user_name,
                CONCAT(m.first_name, ' ', m.last_name) as reviewed_by_name,
                l.manager_id as reviewed_by,
                l.updated_at as reviewed_at
                FROM {$this->table} l
                LEFT JOIN leave_types lt ON l.leave_type_id = lt.id
                LEFT JOIN users u ON l.user_id = u.id
                LEFT JOIN users m ON l.manager_id = m.id
                WHERE l.id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch();

        return $result ?: null;
    }
}
