<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Schedule extends Model
{
    protected string $table = 'schedules';

    public function getByCompany(int $companyId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE company_id = ? ORDER BY is_default DESC, name");
        $stmt->execute([$companyId]);
        return $stmt->fetchAll();
    }

    public function getDefault(int $companyId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE company_id = ? AND is_default = 1 LIMIT 1");
        $stmt->execute([$companyId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findWithDays(int $id): ?array
    {
        $schedule = $this->find($id);
        if (!$schedule) {
            return null;
        }

        $stmt = $this->db->prepare("SELECT * FROM schedule_days WHERE schedule_id = ? ORDER BY weekday");
        $stmt->execute([$id]);
        $schedule['days'] = $stmt->fetchAll();

        return $schedule;
    }
}
