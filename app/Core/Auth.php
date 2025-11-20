<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\User;

/**
 * Authentication
 *
 * Handles user authentication and authorization
 */
class Auth
{
    private App $app;
    private ?array $user = null;
    private bool $loaded = false;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Attempt to authenticate user
     */
    public function attempt(string $email, string $password): bool
    {
        $userModel = new User($this->app);
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        if (!$user['is_active']) {
            return false;
        }

        // Store user ID in session
        Session::set('user_id', $user['id']);
        $this->user = $user;
        $this->loaded = true;

        return true;
    }

    /**
     * Check if user is authenticated
     */
    public function check(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get authenticated user
     */
    public function user(): ?array
    {
        if (!$this->loaded) {
            $userId = Session::get('user_id');

            if ($userId) {
                $userModel = new User($this->app);
                $this->user = $userModel->find((int) $userId);
            }

            $this->loaded = true;
        }

        return $this->user;
    }

    /**
     * Get user ID
     */
    public function id(): ?int
    {
        $user = $this->user();
        return $user ? (int) $user['id'] : null;
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        Session::destroy();
        $this->user = null;
        $this->loaded = false;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        $user = $this->user();
        return $user && $user['role'] === 'admin';
    }

    /**
     * Check if user is manager or admin
     */
    public function isManager(): bool
    {
        $user = $this->user();
        return $user && in_array($user['role'], ['manager', 'admin']);
    }
}
