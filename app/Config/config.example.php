<?php
/**
 * PTO Manager Configuration Example
 *
 * Copy this file to config.php and update with your settings
 */

return [
    // Application
    'app' => [
        'name' => 'PTO Manager',
        'url' => 'http://localhost/pto/public',
        'timezone' => 'America/New_York',
    ],

    // Database
    'db' => [
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'pto_db',
        'username' => 'root',
        'password' => '',
    ],

    // Mail (SMTP)
    'mail' => [
        'host' => 'smtp.mailtrap.io',
        'port' => 2525,
        'username' => 'your-username',
        'password' => 'your-password',
        'encryption' => 'tls', // tls or ssl
        'from_address' => 'noreply@example.com',
        'from_name' => 'PTO Manager',
    ],

    // Security
    'debug' => true, // Set to false in production
];
