<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Holiday extends Model
{
    protected string $table = 'holidays';

    public function getByCompanyAndYear(int $companyId, int $year): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE company_id = ? AND YEAR(date) = ?
                ORDER BY date";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId, $year]);
        return $stmt->fetchAll();
    }

    public function getByDateRange(int $companyId, string $startDate, string $endDate): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE company_id = ?
                AND date BETWEEN ? AND ?
                ORDER BY date";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId, $startDate, $endDate]);
        return $stmt->fetchAll();
    }
}
