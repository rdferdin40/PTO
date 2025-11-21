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
$router->get('/leaves', 'LeaveController@index');
$router->get('/leaves/new', 'LeaveController@create');
$router->post('/leaves', 'LeaveController@store');
$router->get('/leaves/calendar', 'LeaveController@calendar');
$router->get('/leaves/{id}', 'LeaveController@show');
$router->post('/leaves/{id}/cancel', 'LeaveController@cancel');

// Manager routes
$router->get('/manager/approvals', 'ManagerController@approvals');
$router->post('/manager/approvals/{id}/approve', 'ManagerController@approve');
$router->post('/manager/approvals/{id}/reject', 'ManagerController@reject');
$router->get('/manager/team-calendar', 'ManagerController@teamCalendar');

// Admin routes - Users
$router->get('/admin/users', 'AdminController@users');
$router->get('/admin/users/new', 'AdminController@createUser');
$router->post('/admin/users', 'AdminController@storeUser');
$router->get('/admin/users/{id}/edit', 'AdminController@editUser');
$router->post('/admin/users/{id}', 'AdminController@updateUser');
$router->post('/admin/users/{id}/delete', 'AdminController@deleteUser');

// Admin routes - Departments
$router->get('/admin/departments', 'AdminController@departments');
$router->post('/admin/departments', 'AdminController@storeDepartment');
$router->post('/admin/departments/{id}', 'AdminController@updateDepartment');
$router->post('/admin/departments/{id}/delete', 'AdminController@deleteDepartment');

// Admin routes - Leave Types
$router->get('/admin/leave-types', 'AdminController@leaveTypes');
$router->post('/admin/leave-types', 'AdminController@storeLeaveType');
$router->post('/admin/leave-types/{id}', 'AdminController@updateLeaveType');
$router->post('/admin/leave-types/{id}/delete', 'AdminController@deleteLeaveType');

// Admin routes - Holidays
$router->get('/admin/holidays', 'AdminController@holidays');
$router->post('/admin/holidays', 'AdminController@storeHoliday');
$router->post('/admin/holidays/{id}', 'AdminController@updateHoliday');
$router->post('/admin/holidays/{id}/delete', 'AdminController@deleteHoliday');

// Admin routes - Allowances
$router->get('/admin/allowances', 'AdminController@allowances');
$router->post('/admin/allowances', 'AdminController@updateAllowance');

// TODO: Add Reports, Settings, and iCal controllers in future updates

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
