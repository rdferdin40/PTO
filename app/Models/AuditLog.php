<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class AuditLog extends Model
{
    protected string $table = 'audit_logs';

    public function log(?int $companyId, ?int $userId, string $action, ?string $details = null): int
    {
        return $this->insert([
            'company_id' => $companyId,
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
        ]);
    }
}
