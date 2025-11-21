<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\I18n;
use App\Core\Helpers;
use App\Models\User;
use App\Models\Company;
use App\Models\Department;
use App\Models\LeaveType;
use App\Models\Holiday;
use App\Models\Allowance;

class AdminController extends Controller
{
    // ========================================
    // USERS
    // ========================================

    public function users(): void
    {
        $this->requireAuth();
        $this->requireAdmin();

        $user = $this->auth->user();
        $userModel = new User($this->app);
        $userFull = $userModel->findWithRelations($user['id']);

        I18n::setLocale(Helpers::getUserLocale($userFull, null));

        $users = $userModel->getByCompany($user['company_id']);

        $this->render('admin/users/index', [
            'user' => $userFull,
            'users' => $users,
        ]);
    }

    public function createUser(): void
    {
        $this->requireAuth();
        $this->requireAdmin();

        $user = $this->auth->user();
        $userModel = new User($this->app);
        $userFull = $userModel->findWithRelations($user['id']);

        I18n::setLocale(Helpers::getUserLocale($userFull, null));

        $departmentModel = new Department($this->app);
        $departments = $departmentModel->getByCompany($user['company_id']);

        $this->render('admin/users/create', [
            'user' => $userFull,
            'departments' => $departments,
        ]);
    }

    public function storeUser(): void
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->validateCSRF();

        $user = $this->auth->user();

        $email = trim($_POST['email'] ?? '');
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $departmentId = !empty($_POST['department_id']) ? (int) $_POST['department_id'] : null;
        $role = $_POST['role'] ?? 'employee';
        $isManager = isset($_POST['is_manager']) ? 1 : 0;
        $managerId = !empty($_POST['manager_id']) ? (int) $_POST['manager_id'] : null;
        $password = $_POST['password'] ?? '';

        // Validation
        $errors = [];

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = __('valid_email_required');
        }

        if (!$firstName) {
            $errors[] = __('first_name_required');
        }

        if (!$lastName) {
            $errors[] = __('last_name_required');
        }

        if (strlen($password) < 8) {
            $errors[] = __('password_min_length');
        }

        if ($errors) {
            $this->app->session()->flash('error', implode('<br>', $errors));
            $this->redirect('/admin/users/new');
            return;
        }

        // Check if email exists
        $userModel = new User($this->app);
        if ($userModel->findByEmail($email)) {
            $this->app->session()->flash('error', __('email_already_exists'));
            $this->redirect('/admin/users/new');
            return;
        }

        // Create user
        $userModel->create([
            'company_id' => $user['company_id'],
            'department_id' => $departmentId,
            'email' => $email,
            'password' => $password,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => $role,
            'is_manager' => $isManager,
            'manager_id' => $managerId,
            'is_active' => 1,
        ]);

        $this->app->session()->flash('success', __('user_created_success'));
        $this->redirect('/admin/users');
    }

    public function editUser(int $id): void
    {
        $this->requireAuth();
        $this->requireAdmin();

        $user = $this->auth->user();
        $userModel = new User($this->app);
        $userFull = $userModel->findWithRelations($user['id']);

        I18n::setLocale(Helpers::getUserLocale($userFull, null));

        $editUser = $userModel->findWithRelations($id);

        if (!$editUser || $editUser['company_id'] != $user['company_id']) {
            $this->app->session()->flash('error', __('user_not_found'));
            $this->redirect('/admin/users');
            return;
        }

        $departmentModel = new Department($this->app);
        $departments = $departmentModel->getByCompany($user['company_id']);

        $this->render('admin/users/edit', [
            'user' => $userFull,
            'editUser' => $editUser,
            'departments' => $departments,
        ]);
    }

    public function updateUser(int $id): void
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->validateCSRF();

        $user = $this->auth->user();
        $userModel = new User($this->app);

        $editUser = $userModel->find($id);

        if (!$editUser || $editUser['company_id'] != $user['company_id']) {
            $this->app->session()->flash('error', __('user_not_found'));
            $this->redirect('/admin/users');
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $departmentId = !empty($_POST['department_id']) ? (int) $_POST['department_id'] : null;
        $role = $_POST['role'] ?? 'employee';
        $isManager = isset($_POST['is_manager']) ? 1 : 0;
        $managerId = !empty($_POST['manager_id']) ? (int) $_POST['manager_id'] : null;
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        // Validation
        $errors = [];

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = __('valid_email_required');
        }

        if (!$firstName) {
            $errors[] = __('first_name_required');
        }

        if (!$lastName) {
            $errors[] = __('last_name_required');
        }

        if ($errors) {
            $this->app->session()->flash('error', implode('<br>', $errors));
            $this->redirect('/admin/users/' . $id . '/edit');
            return;
        }

        // Check if email exists (excluding current user)
        $existingUser = $userModel->findByEmail($email);
        if ($existingUser && $existingUser['id'] != $id) {
            $this->app->session()->flash('error', __('email_already_exists'));
            $this->redirect('/admin/users/' . $id . '/edit');
            return;
        }

        // Update user
        $userModel->update($id, [
            'department_id' => $departmentId,
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => $role,
            'is_manager' => $isManager,
            'manager_id' => $managerId,
            'is_active' => $isActive,
        ]);

        $this->app->session()->flash('success', __('user_updated_success'));
        $this->redirect('/admin/users');
    }

    public function deleteUser(int $id): void
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->validateCSRF();

        $user = $this->auth->user();
        $userModel = new User($this->app);

        $deleteUser = $userModel->find($id);

        if (!$deleteUser || $deleteUser['company_id'] != $user['company_id']) {
            $this->app->session()->flash('error', __('user_not_found'));
            $this->redirect('/admin/users');
            return;
        }

        if ($deleteUser['id'] == $user['id']) {
            $this->app->session()->flash('error', __('cannot_delete_yourself'));
            $this->redirect('/admin/users');
            return;
        }

        $userModel->delete($id);

        $this->app->session()->flash('success', __('user_deleted_success'));
        $this->redirect('/admin/users');
    }

    // ========================================
    // DEPARTMENTS
    // ========================================

    public function departments(): void
    {
        $this->requireAuth();
        $this->requireAdmin();

        $user = $this->auth->user();
        $userModel = new User($this->app);
        $userFull = $userModel->findWithRelations($user['id']);

        I18n::setLocale(Helpers::getUserLocale($userFull, null));

        $departmentModel = new Department($this->app);
        $departments = $departmentModel->getByCompany($user['company_id']);

        $this->render('admin/departments/index', [
            'user' => $userFull,
            'departments' => $departments,
        ]);
    }

    public function storeDepartment(): void
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->validateCSRF();

        $user = $this->auth->user();
        $name = trim($_POST['name'] ?? '');

        if (!$name) {
            $this->app->session()->flash('error', __('department_name_required'));
            $this->redirect('/admin/departments');
            return;
        }

        $departmentModel = new Department($this->app);
        $departmentModel->create([
            'company_id' => $user['company_id'],
            'name' => $name,
        ]);

        $this->app->session()->flash('success', __('department_created_success'));
        $this->redirect('/admin/departments');
    }

    public function updateDepartment(int $id): void
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->validateCSRF();

        $user = $this->auth->user();
        $name = trim($_POST['name'] ?? '');

        if (!$name) {
            $this->app->session()->flash('error', __('department_name_required'));
            $this->redirect('/admin/departments');
            return;
        }

        $departmentModel = new Department($this->app);
        $department = $departmentModel->find($id);

        if (!$department || $department['company_id'] != $user['company_id']) {
            $this->app->session()->flash('error', __('department_not_found'));
            $this->redirect('/admin/departments');
            return;
        }

        $departmentModel->update($id, ['name' => $name]);

        $this->app->session()->flash('success', __('department_updated_success'));
        $this->redirect('/admin/departments');
    }

    public function deleteDepartment(int $id): void
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->validateCSRF();

        $user = $this->auth->user();
        $departmentModel = new Department($this->app);
        $department = $departmentModel->find($id);

        if (!$department || $department['company_id'] != $user['company_id']) {
            $this->app->session()->flash('error', __('department_not_found'));
            $this->redirect('/admin/departments');
            return;
        }

        $departmentModel->delete($id);

        $this->app->session()->flash('success', __('department_deleted_success'));
        $this->redirect('/admin/departments');
    }

    // ========================================
    // LEAVE TYPES
    // ========================================

    public function leaveTypes(): void
    {
        $this->requireAuth();
        $this->requireAdmin();

        $user = $this->auth->user();
        $userModel = new User($this->app);
        $userFull = $userModel->findWithRelations($user['id']);

        I18n::setLocale(Helpers::getUserLocale($userFull, null));

        $leaveTypeModel = new LeaveType($this->app);
        $leaveTypes = $leaveTypeModel->getByCompany($user['company_id']);

        $this->render('admin/leave_types/index', [
            'user' => $userFull,
            'leaveTypes' => $leaveTypes,
        ]);
    }

    public function storeLeaveType(): void
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->validateCSRF();

        $user = $this->auth->user();
        $name = trim($_POST['name'] ?? '');
        $color = trim($_POST['color'] ?? '#6c757d');
        $requiresAllowance = isset($_POST['requires_allowance']) ? 1 : 0;
        $deductsAllowance = isset($_POST['deducts_allowance']) ? 1 : 0;

        if (!$name) {
            $this->app->session()->flash('error', __('leave_type_name_required'));
            $this->redirect('/admin/leave-types');
            return;
        }

        $leaveTypeModel = new LeaveType($this->app);
        $leaveTypeModel->create([
            'company_id' => $user['company_id'],
            'name' => $name,
            'color' => $color,
            'requires_allowance' => $requiresAllowance,
            'deducts_allowance' => $deductsAllowance,
        ]);

        $this->app->session()->flash('success', __('leave_type_created_success'));
        $this->redirect('/admin/leave-types');
    }

    public function updateLeaveType(int $id): void
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->validateCSRF();

        $user = $this->auth->user();
        $name = trim($_POST['name'] ?? '');
        $color = trim($_POST['color'] ?? '#6c757d');
        $requiresAllowance = isset($_POST['requires_allowance']) ? 1 : 0;
        $deductsAllowance = isset($_POST['deducts_allowance']) ? 1 : 0;

        if (!$name) {
            $this->app->session()->flash('error', __('leave_type_name_required'));
            $this->redirect('/admin/leave-types');
            return;
        }

        $leaveTypeModel = new LeaveType($this->app);
        $leaveType = $leaveTypeModel->find($id);

        if (!$leaveType || $leaveType['company_id'] != $user['company_id']) {
            $this->app->session()->flash('error', __('leave_type_not_found'));
            $this->redirect('/admin/leave-types');
            return;
        }

        $leaveTypeModel->update($id, [
            'name' => $name,
            'color' => $color,
            'requires_allowance' => $requiresAllowance,
            'deducts_allowance' => $deductsAllowance,
        ]);

        $this->app->session()->flash('success', __('leave_type_updated_success'));
        $this->redirect('/admin/leave-types');
    }

    public function deleteLeaveType(int $id): void
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->validateCSRF();

        $user = $this->auth->user();
        $leaveTypeModel = new LeaveType($this->app);
        $leaveType = $leaveTypeModel->find($id);

        if (!$leaveType || $leaveType['company_id'] != $user['company_id']) {
            $this->app->session()->flash('error', __('leave_type_not_found'));
            $this->redirect('/admin/leave-types');
            return;
        }

        $leaveTypeModel->delete($id);

        $this->app->session()->flash('success', __('leave_type_deleted_success'));
        $this->redirect('/admin/leave-types');
    }

    // ========================================
    // HOLIDAYS
    // ========================================

    public function holidays(): void
    {
        $this->requireAuth();
        $this->requireAdmin();

        $user = $this->auth->user();
        $userModel = new User($this->app);
        $userFull = $userModel->findWithRelations($user['id']);

        I18n::setLocale(Helpers::getUserLocale($userFull, null));

        $year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');

        $holidayModel = new Holiday($this->app);
        $holidays = $holidayModel->getByYear($user['company_id'], $year);

        $this->render('admin/holidays/index', [
            'user' => $userFull,
            'holidays' => $holidays,
            'year' => $year,
        ]);
    }

    public function storeHoliday(): void
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->validateCSRF();

        $user = $this->auth->user();
        $name = trim($_POST['name'] ?? '');
        $date = $_POST['date'] ?? '';
        $isBlackout = isset($_POST['is_blackout']) ? 1 : 0;

        if (!$name || !$date) {
            $this->app->session()->flash('error', __('holiday_name_date_required'));
            $this->redirect('/admin/holidays');
            return;
        }

        $holidayModel = new Holiday($this->app);
        $holidayModel->create([
            'company_id' => $user['company_id'],
            'name' => $name,
            'date' => $date,
            'is_blackout' => $isBlackout,
        ]);

        $this->app->session()->flash('success', __('holiday_created_success'));
        $this->redirect('/admin/holidays?year=' . date('Y', strtotime($date)));
    }

    public function updateHoliday(int $id): void
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->validateCSRF();

        $user = $this->auth->user();
        $name = trim($_POST['name'] ?? '');
        $date = $_POST['date'] ?? '';
        $isBlackout = isset($_POST['is_blackout']) ? 1 : 0;

        if (!$name || !$date) {
            $this->app->session()->flash('error', __('holiday_name_date_required'));
            $this->redirect('/admin/holidays');
            return;
        }

        $holidayModel = new Holiday($this->app);
        $holiday = $holidayModel->find($id);

        if (!$holiday || $holiday['company_id'] != $user['company_id']) {
            $this->app->session()->flash('error', __('holiday_not_found'));
            $this->redirect('/admin/holidays');
            return;
        }

        $holidayModel->update($id, [
            'name' => $name,
            'date' => $date,
            'is_blackout' => $isBlackout,
        ]);

        $this->app->session()->flash('success', __('holiday_updated_success'));
        $this->redirect('/admin/holidays?year=' . date('Y', strtotime($date)));
    }

    public function deleteHoliday(int $id): void
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->validateCSRF();

        $user = $this->auth->user();
        $holidayModel = new Holiday($this->app);
        $holiday = $holidayModel->find($id);

        if (!$holiday || $holiday['company_id'] != $user['company_id']) {
            $this->app->session()->flash('error', __('holiday_not_found'));
            $this->redirect('/admin/holidays');
            return;
        }

        $holidayModel->delete($id);

        $this->app->session()->flash('success', __('holiday_deleted_success'));
        $this->redirect('/admin/holidays');
    }

    // ========================================
    // ALLOWANCES
    // ========================================

    public function allowances(): void
    {
        $this->requireAuth();
        $this->requireAdmin();

        $user = $this->auth->user();
        $userModel = new User($this->app);
        $userFull = $userModel->findWithRelations($user['id']);

        I18n::setLocale(Helpers::getUserLocale($userFull, null));

        $year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');

        $users = $userModel->getByCompany($user['company_id']);

        $allowanceModel = new Allowance($this->app);
        $allowances = [];

        foreach ($users as $u) {
            $allowances[$u['id']] = $allowanceModel->getOrCreate($u['id'], $year);
        }

        $this->render('admin/allowances/index', [
            'user' => $userFull,
            'users' => $users,
            'allowances' => $allowances,
            'year' => $year,
        ]);
    }

    public function updateAllowance(): void
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->validateCSRF();

        $userId = (int) ($_POST['user_id'] ?? 0);
        $year = (int) ($_POST['year'] ?? date('Y'));
        $entitledDays = (float) ($_POST['entitled_days'] ?? 0);
        $carriedOverDays = (float) ($_POST['carried_over_days'] ?? 0);
        $manualAdjustmentDays = (float) ($_POST['manual_adjustment_days'] ?? 0);

        $allowanceModel = new Allowance($this->app);
        $allowance = $allowanceModel->getByUserAndYear($userId, $year);

        if ($allowance) {
            $allowanceModel->update($allowance['id'], [
                'entitled_days' => $entitledDays,
                'carried_over_days' => $carriedOverDays,
                'manual_adjustment_days' => $manualAdjustmentDays,
            ]);
        } else {
            $allowanceModel->create([
                'user_id' => $userId,
                'year' => $year,
                'entitled_days' => $entitledDays,
                'carried_over_days' => $carriedOverDays,
                'manual_adjustment_days' => $manualAdjustmentDays,
            ]);
        }

        $this->app->session()->flash('success', __('allowance_updated_success'));
        $this->redirect('/admin/allowances?year=' . $year);
    }
}
