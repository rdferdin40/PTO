<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class LeaveType extends Model
{
    protected string $table = 'leave_types';

    public function getActiveByCompany(int $companyId): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE company_id = ? AND is_active = 1
                ORDER BY name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId]);
        return $stmt->fetchAll();
    }

    public function getByCompany(int $companyId): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE company_id = ?
                ORDER BY name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId]);
        return $stmt->fetchAll();
    }
}
