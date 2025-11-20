<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Core\Helpers;

class User extends Model
{
    protected string $table = 'users';

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Find user by email and company
     */
    public function findByEmailAndCompany(string $email, int $companyId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ? AND company_id = ? LIMIT 1");
        $stmt->execute([$email, $companyId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Find user by iCal token
     */
    public function findByIcalToken(string $token): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE ical_token = ? LIMIT 1");
        $stmt->execute([$token]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get all users for company
     */
    public function getByCompany(int $companyId): array
    {
        $sql = "SELECT u.*, d.name as department_name
                FROM {$this->table} u
                LEFT JOIN departments d ON u.department_id = d.id
                WHERE u.company_id = ?
                ORDER BY u.last_name, u.first_name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId]);
        return $stmt->fetchAll();
    }

    /**
     * Get users by department
     */
    public function getByDepartment(int $departmentId): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE department_id = ?
                AND is_active = 1
                ORDER BY last_name, first_name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$departmentId]);
        return $stmt->fetchAll();
    }

    /**
     * Create user with hashed password
     */
    public function create(array $data): int
    {
        // Hash password
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }

        // Generate iCal token
        if (!isset($data['ical_token'])) {
            $data['ical_token'] = hash('sha256', $data['email'] . time() . random_bytes(16));
        }

        return $this->insert($data);
    }

    /**
     * Update user password
     */
    public function updatePassword(int $id, string $newPassword): bool
    {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("UPDATE {$this->table} SET password_hash = ? WHERE id = ?");
        return $stmt->execute([$passwordHash, $id]);
    }

    /**
     * Regenerate iCal token
     */
    public function regenerateIcalToken(int $id): string
    {
        $user = $this->find($id);
        $token = hash('sha256', $user['email'] . time() . random_bytes(16));

        $stmt = $this->db->prepare("UPDATE {$this->table} SET ical_token = ? WHERE id = ?");
        $stmt->execute([$token, $id]);

        return $token;
    }

    /**
     * Get user with company and department info
     */
    public function findWithRelations(int $id): ?array
    {
        $sql = "SELECT u.*, c.name as company_name, c.timezone, c.default_locale as company_locale,
                c.default_theme as company_theme, d.name as department_name
                FROM {$this->table} u
                LEFT JOIN companies c ON u.company_id = c.id
                LEFT JOIN departments d ON u.department_id = d.id
                WHERE u.id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
