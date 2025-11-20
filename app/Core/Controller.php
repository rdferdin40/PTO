<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Base Controller
 *
 * Provides common functionality for all controllers
 */
abstract class Controller
{
    protected App $app;
    protected Auth $auth;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->auth = $app->auth();
    }

    /**
     * Render a view
     */
    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $viewRenderer = new View($this->app);
        $viewRenderer->render($view, $data, $layout);
    }

    /**
     * Redirect to URL
     */
    protected function redirect(string $path, int $code = 302): void
    {
        header('Location: ' . $this->app->url($path), true, $code);
        exit;
    }

    /**
     * Redirect back
     */
    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? $this->app->url('/');
        header('Location: ' . $referer);
        exit;
    }

    /**
     * Return JSON response
     */
    protected function json(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Require authentication
     */
    protected function requireAuth(): void
    {
        if (!$this->auth->check()) {
            Session::flash('error', __('auth_required'));
            $this->redirect('/login');
        }
    }

    /**
     * Require specific role
     */
    protected function requireRole(string $role): void
    {
        $this->requireAuth();

        $user = $this->auth->user();
        if ($user['role'] !== $role && $user['role'] !== 'admin') {
            Session::flash('error', __('access_denied'));
            $this->redirect('/dashboard');
        }
    }

    /**
     * Require admin role
     */
    protected function requireAdmin(): void
    {
        $this->requireAuth();

        if (!$this->auth->isAdmin()) {
            Session::flash('error', __('admin_access_required'));
            $this->redirect('/dashboard');
        }
    }

    /**
     * Require manager or admin role
     */
    protected function requireManager(): void
    {
        $this->requireAuth();

        if (!$this->auth->isManager() && !$this->auth->isAdmin()) {
            Session::flash('error', __('manager_access_required'));
            $this->redirect('/dashboard');
        }
    }

    /**
     * Flash message
     */
    protected function flash(string $type, string $message): void
    {
        Session::flash($type, $message);
    }

    /**
     * Validate CSRF token
     */
    protected function validateCSRF(): void
    {
        if (!CSRF::validate($_POST['_token'] ?? '')) {
            Session::flash('error', __('invalid_token'));
            $this->back();
        }
    }
}
