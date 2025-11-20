<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

/**
 * Application Container
 *
 * Central registry for application-wide services and state
 */
class App
{
    private array $config;
    private ?PDO $db = null;
    private ?Auth $auth = null;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get configuration value
     */
    public function config(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Get database connection (singleton)
     */
    public function db(): PDO
    {
        if ($this->db === null) {
            try {
                $dsn = sprintf(
                    'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                    $this->config('db.host'),
                    $this->config('db.port', 3306),
                    $this->config('db.database')
                );

                $this->db = new PDO(
                    $dsn,
                    $this->config('db.username'),
                    $this->config('db.password'),
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                if ($this->config('debug')) {
                    die('Database connection failed: ' . $e->getMessage());
                }
                die('Database connection failed. Please contact the administrator.');
            }
        }

        return $this->db;
    }

    /**
     * Get Auth instance (singleton)
     */
    public function auth(): Auth
    {
        if ($this->auth === null) {
            $this->auth = new Auth($this);
        }
        return $this->auth;
    }

    /**
     * Get base URL
     */
    public function url(string $path = ''): string
    {
        return rtrim($this->config('app.url'), '/') . '/' . ltrim($path, '/');
    }

    /**
     * Get asset URL
     */
    public function asset(string $path): string
    {
        return $this->url('assets/' . ltrim($path, '/'));
    }
}
