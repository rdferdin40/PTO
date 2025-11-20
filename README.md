# PTO Manager

A complete, self-hosted employee leave management system built for **XAMPP 8.2** (PHP 8.2, Apache, MySQL/MariaDB).

## Features

- ✅ Multi-company & multi-department support
- ✅ Role-based access control (Employee, Manager, Admin)
- ✅ Leave request workflow with approvals
- ✅ Allowance tracking & management
- ✅ Email notifications (SMTP via PHPMailer)
- ✅ Multi-language support (English & Spanish)
- ✅ Theming (Light, Dark, Blue)
- ✅ Secure authentication & CSRF protection
- ✅ Bootstrap 5 responsive UI

## Requirements

- XAMPP 8.2 (or higher)
  - PHP 8.2+
  - Apache 2.4+
  - MySQL 5.7+ or MariaDB 10.3+
- Composer (for dependency management)

## Installation

### 1. Clone or Download

This repository is already cloned to your system.

### 2. Install Dependencies

```bash
cd /path/to/PTO
composer install
```

### 3. Create Database

1. Start XAMPP (Apache and MySQL)
2. Open phpMyAdmin: http://localhost/phpmyadmin
3. Create new database: `pto_db` (utf8mb4_unicode_ci)
4. Import schema: `sql/schema.sql`
5. Import demo data: `sql/seed_demo.sql` (optional)

### 4. Configure Application

1. Copy config template:
   ```bash
   cp app/Config/config.example.php app/Config/config.php
   ```

2. Edit `app/Config/config.php`:
   ```php
   'db' => [
       'host' => 'localhost',
       'database' => 'pto_db',
       'username' => 'root',
       'password' => '', // Default XAMPP password is empty
   ],
   ```

3. Update `app.url` to match your setup:
   - For subdirectory: `http://localhost/pto/public`
   - For virtual host: `http://pto.local`

### 5. Configure Apache (Optional - Virtual Host)

For cleaner URLs, add to `httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    ServerName pto.local
    DocumentRoot "C:/xampp/htdocs/PTO/public"

    <Directory "C:/xampp/htdocs/PTO/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Add to `hosts` file (C:\Windows\System32\drivers\etc\hosts):
```
127.0.0.1    pto.local
```

Restart Apache and access: http://pto.local

### 6. Configure Email (Optional)

Update SMTP settings in `app/Config/config.php`:

```php
'mail' => [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'username' => 'your-email@gmail.com',
    'password' => 'your-app-password',
    'encryption' => 'tls',
    'from_address' => 'noreply@yourcompany.com',
    'from_name' => 'PTO Manager',
],
```

## First Login

If you imported demo data:

**Admin Account:**
- Email: `admin@acme.test`
- Password: `Password123!`

**⚠️ Change this password immediately after first login!**

## Usage

### For Employees
- View dashboard with leave allowance
- Request time off
- View leave history

### For Managers
- Approve/reject leave requests
- View team calendar

### For Admins
- Manage users, departments, leave types
- Configure allowances and holidays
- Access all reports

## Project Structure

```
PTO/
├── app/
│   ├── Config/         # Application configuration
│   ├── Controllers/    # Request handlers
│   ├── Core/           # Framework core (Router, Auth, etc.)
│   ├── Lang/           # Language files (i18n)
│   ├── Models/         # Database models
│   └── Views/          # HTML templates
├── public/             # Web-accessible directory
│   ├── assets/         # CSS, JS, images
│   └── index.php       # Front controller
├── sql/                # Database schema & seeds
├── vendor/             # Composer dependencies
└── composer.json       # Dependency definitions
```

## Development

### Adding New Features

1. **Routes:** Edit `public/index.php`
2. **Controllers:** Create in `app/Controllers/`
3. **Models:** Create in `app/Models/`
4. **Views:** Create in `app/Views/`

### Database Migrations

1. Backup database first
2. Create SQL migration file
3. Apply via phpMyAdmin

### Adding Languages

1. Create `app/Lang/{locale}.php`
2. Copy structure from `en.php`
3. Translate all strings

## Security

- Passwords hashed with `password_hash()` (bcrypt)
- CSRF protection on all forms
- SQL injection prevention (PDO prepared statements)
- XSS protection (htmlspecialchars)
- Session security (httponly, samesite)

## Production Deployment

1. Set `debug => false` in config
2. Use strong database password
3. Enable HTTPS
4. Secure file permissions
5. Regular backups

## Troubleshooting

### Database connection failed
- Check MySQL is running
- Verify credentials in `config.php`
- Ensure database exists

### 404 errors
- Check `mod_rewrite` is enabled
- Verify `.htaccess` in `public/`
- Check `app.url` in config

### Emails not sending
- Verify SMTP settings
- Test with Mailtrap.io first
- Check debug mode for errors

## Support

For issues and questions:
- Check logs: `C:\xampp\apache\logs\error.log`
- Review this README
- Check configuration

## License

This project is provided as-is for educational and commercial use.

## Credits

Inspired by TimeOff.Management, built from scratch for XAMPP 8.2 with PHP 8.2, MySQL, and Bootstrap 5.
