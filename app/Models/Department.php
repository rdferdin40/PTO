<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Department extends Model
{
    protected string $table = 'departments';

    public function getByCompany(int $companyId): array
    {
        $sql = "SELECT d.*,
                CONCAT(u.first_name, ' ', u.last_name) as manager_name
                FROM {$this->table} d
                LEFT JOIN users u ON d.manager_user_id = u.id
                WHERE d.company_id = ?
                ORDER BY d.name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId]);
        return $stmt->fetchAll();
    }
}
