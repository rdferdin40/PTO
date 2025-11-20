<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class PasswordReset extends Model
{
    protected string $table = 'password_resets';

    public function findByToken(string $token): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE token = ? LIMIT 1");
        $stmt->execute([$token]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function markAsUsed(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET used = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
