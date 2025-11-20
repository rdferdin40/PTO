<?php
declare(strict_types=1);

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Email Mailer
 *
 * Sends emails via SMTP using PHPMailer
 */
class Mailer
{
    private App $app;
    private PHPMailer $mailer;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->mailer = new PHPMailer(true);
        $this->configure();
    }

    /**
     * Configure PHPMailer
     */
    private function configure(): void
    {
        try {
            // SMTP settings
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->app->config('mail.host');
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->app->config('mail.username');
            $this->mailer->Password = $this->app->config('mail.password');
            $this->mailer->SMTPSecure = $this->app->config('mail.encryption', 'tls');
            $this->mailer->Port = $this->app->config('mail.port', 587);

            // From address
            $this->mailer->setFrom(
                $this->app->config('mail.from_address'),
                $this->app->config('mail.from_name')
            );

            // HTML emails
            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';
        } catch (Exception $e) {
            if ($this->app->config('debug')) {
                throw $e;
            }
        }
    }

    /**
     * Send leave request notification to manager
     */
    public function sendLeaveRequestNotification(array $manager, array $leave, array $employee): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($manager['email'], $manager['first_name'] . ' ' . $manager['last_name']);

            $this->mailer->Subject = __('email_leave_request_subject');

            $body = __('email_leave_request_body', [
                'manager_name' => $manager['first_name'],
                'employee_name' => $employee['first_name'] . ' ' . $employee['last_name'],
                'leave_type' => $leave['leave_type_name'],
                'start_date' => date('M d, Y', strtotime($leave['start_date'])),
                'end_date' => date('M d, Y', strtotime($leave['end_date'])),
                'days' => $leave['days'],
                'comment' => $leave['employee_comment'] ?? __('no_comment'),
            ]);

            $this->mailer->Body = $this->htmlTemplate($body);

            return $this->mailer->send();
        } catch (Exception $e) {
            if ($this->app->config('debug')) {
                error_log('Email error: ' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Send leave decision notification to employee
     */
    public function sendLeaveDecisionNotification(array $employee, array $leave, string $decision): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($employee['email'], $employee['first_name'] . ' ' . $employee['last_name']);

            $this->mailer->Subject = __('email_leave_decision_subject_' . $decision);

            $body = __('email_leave_decision_body_' . $decision, [
                'employee_name' => $employee['first_name'],
                'leave_type' => $leave['leave_type_name'],
                'start_date' => date('M d, Y', strtotime($leave['start_date'])),
                'end_date' => date('M d, Y', strtotime($leave['end_date'])),
                'days' => $leave['days'],
                'comment' => $leave['manager_comment'] ?? __('no_comment'),
            ]);

            $this->mailer->Body = $this->htmlTemplate($body);

            return $this->mailer->send();
        } catch (Exception $e) {
            if ($this->app->config('debug')) {
                error_log('Email error: ' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail(array $user, string $token): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($user['email'], $user['first_name'] . ' ' . $user['last_name']);

            $this->mailer->Subject = __('email_password_reset_subject');

            $resetUrl = $this->app->url('password/reset/' . $token);

            $body = __('email_password_reset_body', [
                'user_name' => $user['first_name'],
                'reset_url' => $resetUrl,
            ]);

            $this->mailer->Body = $this->htmlTemplate($body);

            return $this->mailer->send();
        } catch (Exception $e) {
            if ($this->app->config('debug')) {
                error_log('Email error: ' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Simple HTML email template
     */
    private function htmlTemplate(string $content): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #3490dc; color: white; padding: 20px; text-align: center; }
        .content { background: #f8f9fa; padding: 20px; margin: 20px 0; }
        .footer { text-align: center; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>PTO Manager</h2>
        </div>
        <div class="content">
            {$content}
        </div>
        <div class="footer">
            <p>This is an automated message from PTO Manager.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
