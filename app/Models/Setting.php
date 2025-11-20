<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Setting extends Model
{
    protected string $table = 'settings';

    public function getValue(?int $companyId, string $key, ?string $default = null): ?string
    {
        $sql = "SELECT value FROM {$this->table} WHERE company_id ";
        $sql .= $companyId ? "= ?" : "IS NULL";
        $sql .= " AND `key` = ? LIMIT 1";

        $params = $companyId ? [$companyId, $key] : [$key];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        return $result ? $result['value'] : $default;
    }

    public function setValue(?int $companyId, string $key, string $value): void
    {
        $existing = $this->getValue($companyId, $key);

        if ($existing !== null) {
            $sql = "UPDATE {$this->table} SET value = ? WHERE company_id ";
            $sql .= $companyId ? "= ?" : "IS NULL";
            $sql .= " AND `key` = ?";

            $params = $companyId ? [$value, $companyId, $key] : [$value, $key];

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        } else {
            $this->insert([
                'company_id' => $companyId,
                'key' => $key,
                'value' => $value,
            ]);
        }
    }
}
