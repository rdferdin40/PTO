<?php
declare(strict_types=1);

/**
 * PTO Manager - Front Controller
 *
 * Entry point for all requests. Bootstraps the application,
 * initializes core services, and dispatches requests.
 */

// Define base paths
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');

// Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// Manual autoloader for our app classes
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = APP_PATH . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Load configuration
$config = require APP_PATH . '/Config/config.php';

// Error reporting based on environment
if ($config['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Start session
App\Core\Session::start();

// Initialize application
$app = new App\Core\App($config);

// Initialize router
$router = new App\Core\Router($app);

// ========================================
// ROUTE DEFINITIONS
// ========================================

// Public routes
$router->get('/', 'DashboardController@index');
$router->get('/login', 'AuthController@showLoginForm');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');
$router->get('/password/request', 'AuthController@showPasswordRequestForm');
$router->post('/password/request', 'AuthController@sendPasswordResetLink');
$router->get('/password/reset/{token}', 'AuthController@showPasswordResetForm');
$router->post('/password/reset/{token}', 'AuthController@resetPassword');

// Dashboard
$router->get('/dashboard', 'DashboardController@index');

// Leave management
$router->get('/leaves/calendar', 'LeaveController@myCalendar');
$router->get('/leaves/request', 'LeaveController@showRequestForm');
$router->post('/leaves/request', 'LeaveController@submitRequest');
$router->get('/leaves/history', 'LeaveController@history');
$router->post('/leaves/{id}/cancel', 'LeaveController@cancel');

// Manager routes
$router->get('/manager/approvals', 'ManagerController@approvals');
$router->post('/manager/approvals/{id}/approve', 'ManagerController@approve');
$router->post('/manager/approvals/{id}/reject', 'ManagerController@reject');
$router->get('/manager/team-calendar', 'ManagerController@teamCalendar');

// Admin routes
$router->get('/admin/dashboard', 'AdminController@dashboard');
$router->get('/admin/companies', 'AdminController@companies');
$router->post('/admin/companies', 'AdminController@saveCompany');
$router->post('/admin/companies/{id}/delete', 'AdminController@deleteCompany');

$router->get('/admin/departments', 'AdminController@departments');
$router->post('/admin/departments', 'AdminController@saveDepartment');
$router->post('/admin/departments/{id}/delete', 'AdminController@deleteDepartment');

$router->get('/admin/users', 'AdminController@users');
$router->get('/admin/users/create', 'AdminController@createUser');
$router->post('/admin/users/create', 'AdminController@storeUser');
$router->get('/admin/users/{id}/edit', 'AdminController@editUser');
$router->post('/admin/users/{id}/edit', 'AdminController@updateUser');
$router->post('/admin/users/{id}/deactivate', 'AdminController@deactivateUser');
$router->post('/admin/users/{id}/reset-password', 'AdminController@resetUserPassword');

$router->get('/admin/leave-types', 'AdminController@leaveTypes');
$router->post('/admin/leave-types', 'AdminController@saveLeaveType');
$router->post('/admin/leave-types/{id}/delete', 'AdminController@deleteLeaveType');

$router->get('/admin/holidays', 'AdminController@holidays');
$router->post('/admin/holidays', 'AdminController@saveHoliday');
$router->post('/admin/holidays/{id}/delete', 'AdminController@deleteHoliday');

$router->get('/admin/schedules', 'AdminController@schedules');
$router->post('/admin/schedules', 'AdminController@saveSchedule');
$router->post('/admin/schedules/{id}/delete', 'AdminController@deleteSchedule');

$router->get('/admin/allowances', 'AdminController@allowances');
$router->post('/admin/allowances', 'AdminController@saveAllowance');
$router->post('/admin/allowances/import', 'AdminController@importAllowances');

$router->get('/admin/settings', 'AdminController@settings');
$router->post('/admin/settings', 'AdminController@saveSettings');

// Reports
$router->get('/reports/leaves', 'ReportController@leavesReport');
$router->get('/reports/leaves/export', 'ReportController@exportLeaves');
$router->get('/reports/allowances', 'ReportController@allowancesReport');
$router->get('/reports/allowances/export', 'ReportController@exportAllowances');

// User settings
$router->get('/settings/profile', 'SettingsController@profile');
$router->post('/settings/profile', 'SettingsController@updateProfile');
$router->get('/settings/preferences', 'SettingsController@preferences');
$router->post('/settings/preferences', 'SettingsController@updatePreferences');
$router->post('/settings/change-password', 'SettingsController@changePassword');

// iCal feeds
$router->get('/ical/user/{token}', 'IcalController@userFeed');

// Dispatch
try {
    $router->dispatch();
} catch (Exception $e) {
    if ($config['debug']) {
        echo "<pre>Error: " . htmlspecialchars($e->getMessage()) . "\n\n";
        echo $e->getTraceAsString() . "</pre>";
    } else {
        http_response_code(500);
        echo "An error occurred. Please contact the administrator.";
    }
}
