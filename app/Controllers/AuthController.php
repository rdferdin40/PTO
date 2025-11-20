<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\CSRF;
use App\Core\Validator;
use App\Core\Helpers;
use App\Core\Mailer;
use App\Models\User;
use App\Models\PasswordReset;

class AuthController extends Controller
{
    public function showLoginForm(): void
    {
        if ($this->auth->check()) {
            $this->redirect('/dashboard');
        }

        $this->render('auth/login', [], 'auth');
    }

    public function login(): void
    {
        $this->validateCSRF();

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $validator = new Validator(['email' => $email, 'password' => $password]);

        if (!$validator->validate(['email' => 'required|email', 'password' => 'required'])) {
            Session::flash('error', __('validation_failed'));
            $this->redirect('/login');
        }

        if ($this->auth->attempt($email, $password)) {
            Session::flash('success', __('login_success'));
            $this->redirect('/dashboard');
        } else {
            Session::flash('error', __('login_failed'));
            $this->redirect('/login');
        }
    }

    public function logout(): void
    {
        $this->auth->logout();
        Session::flash('success', __('logout_success'));
        $this->redirect('/login');
    }

    public function showPasswordRequestForm(): void
    {
        $this->render('auth/password_request', [], 'auth');
    }

    public function sendPasswordResetLink(): void
    {
        $this->validateCSRF();

        $email = $_POST['email'] ?? '';

        $validator = new Validator(['email' => $email]);
        if (!$validator->validate(['email' => 'required|email'])) {
            Session::flash('error', __('validation_failed'));
            $this->redirect('/password/request');
        }

        $userModel = new User($this->app);
        $user = $userModel->findByEmail($email);

        if ($user) {
            $token = Helpers::generateToken(32);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $resetModel = new PasswordReset($this->app);
            $resetModel->insert([
                'user_id' => $user['id'],
                'token' => $token,
                'expires_at' => $expiresAt,
                'used' => 0,
            ]);

            $mailer = new Mailer($this->app);
            $mailer->sendPasswordResetEmail($user, $token);
        }

        Session::flash('success', __('password_reset_sent'));
        $this->redirect('/login');
    }

    public function showPasswordResetForm(string $token): void
    {
        $resetModel = new PasswordReset($this->app);
        $reset = $resetModel->findByToken($token);

        if (!$reset || $reset['used'] || strtotime($reset['expires_at']) < time()) {
            Session::flash('error', __('invalid_reset_token'));
            $this->redirect('/login');
        }

        $this->render('auth/password_reset', ['token' => $token], 'auth');
    }

    public function resetPassword(string $token): void
    {
        $this->validateCSRF();

        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        if ($password !== $passwordConfirm) {
            Session::flash('error', __('passwords_not_match'));
            $this->redirect('/password/reset/' . $token);
        }

        if (strlen($password) < 8) {
            Session::flash('error', __('password_min_length'));
            $this->redirect('/password/reset/' . $token);
        }

        $resetModel = new PasswordReset($this->app);
        $reset = $resetModel->findByToken($token);

        if (!$reset || $reset['used'] || strtotime($reset['expires_at']) < time()) {
            Session::flash('error', __('invalid_reset_token'));
            $this->redirect('/login');
        }

        $userModel = new User($this->app);
        $userModel->updatePassword($reset['user_id'], $password);

        $resetModel->markAsUsed($reset['id']);

        Session::flash('success', __('password_reset_success'));
        $this->redirect('/login');
    }
}
