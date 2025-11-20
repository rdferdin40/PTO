<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Allowance extends Model
{
    protected string $table = 'allowances';

    public function getByUserAndYear(int $userId, int $year): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = ? AND year = ? LIMIT 1");
        $stmt->execute([$userId, $year]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getOrCreate(int $userId, int $year, float $defaultEntitled = 15.0): array
    {
        $allowance = $this->getByUserAndYear($userId, $year);

        if (!$allowance) {
            $id = $this->insert([
                'user_id' => $userId,
                'year' => $year,
                'entitled_days' => $defaultEntitled,
                'carried_over_days' => 0.0,
                'manual_adjustment_days' => 0.0,
            ]);

            $allowance = $this->find($id);
        }

        return $allowance;
    }

    public function calculateAvailable(array $allowance, float $usedDays): float
    {
        $total = $allowance['entitled_days']
               + $allowance['carried_over_days']
               + $allowance['manual_adjustment_days'];

        return max(0, $total - $usedDays);
    }
}
