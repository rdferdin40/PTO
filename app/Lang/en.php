<?php
return [
    'app_name' => 'PTO Manager',
    'login' => 'Login',
    'login_title' => 'Sign in to your account',
    'email' => 'Email',
    'password' => 'Password',
    'logout' => 'Logout',
    'dashboard' => 'Dashboard',
    'admin' => 'Admin',
    'users' => 'Users',
    'forgot_password' => 'Forgot password?',
    'allowance_summary' => 'Allowance Summary',
    'entitled' => 'Entitled',
    'used' => 'Used',
    'remaining' => 'Remaining',

    // Messages
    'login_success' => 'Login successful!',
    'login_failed' => 'Invalid email or password.',
    'logout_success' => 'You have been logged out.',
    'auth_required' => 'You must be logged in.',
    'access_denied' => 'Access denied.',
    'admin_access_required' => 'Admin access required.',
    'validation_failed' => 'Validation failed.',
    'password_reset_sent' => 'If an account exists, a password reset link has been sent.',
    'invalid_reset_token' => 'Invalid or expired token.',
    'passwords_not_match' => 'Passwords do not match.',
    'password_min_length' => 'Password must be at least 8 characters.',
    'password_reset_success' => 'Password reset successful.',

    // Validation
    'validation_required' => '{field} is required.',
    'validation_email' => '{field} must be a valid email.',

    // Email
    'email_password_reset_subject' => 'Password Reset Request',
    'email_password_reset_body' => 'Hi {user_name},<br><br>Click the link to reset your password:<br><br><a href="{reset_url}">{reset_url}</a>',
];
