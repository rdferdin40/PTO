<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class UserSchedule extends Model
{
    protected string $table = 'user_schedules';

    public function getByUser(int $userId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
