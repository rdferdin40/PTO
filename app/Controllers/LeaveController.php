<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\I18n;
use App\Core\Helpers;
use App\Core\Mailer;
use App\Models\User;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Allowance;
use App\Models\Holiday;
use App\Models\Schedule;
use App\Models\UserSchedule;

class LeaveController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $user = $this->auth->user();
        $userModel = new User($this->app);
        $userFull = $userModel->findWithRelations($user['id']);

        I18n::setLocale(Helpers::getUserLocale($userFull, null));

        $leaveModel = new Leave($this->app);
        $leaves = $leaveModel->getByUser($user['id']);

        $this->render('leaves/index', [
            'user' => $userFull,
            'leaves' => $leaves,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        $user = $this->auth->user();
        $userModel = new User($this->app);
        $userFull = $userModel->findWithRelations($user['id']);

        I18n::setLocale(Helpers::getUserLocale($userFull, null));

        $leaveTypeModel = new LeaveType($this->app);
        $leaveTypes = $leaveTypeModel->getByCompany($user['company_id']);

        $currentYear = (int) date('Y');
        $allowanceModel = new Allowance($this->app);
        $allowance = $allowanceModel->getOrCreate($user['id'], $currentYear);

        $leaveModel = new Leave($this->app);
        $usedDays = $leaveModel->getUsedDays($user['id'], $currentYear);

        $totalAllowance = $allowance['entitled_days']
                        + $allowance['carried_over_days']
                        + $allowance['manual_adjustment_days'];
        $remainingDays = $totalAllowance - $usedDays;

        $this->render('leaves/create', [
            'user' => $userFull,
            'leaveTypes' => $leaveTypes,
            'remainingDays' => $remainingDays,
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->validateCSRF();

        $user = $this->auth->user();

        $leaveTypeId = (int) ($_POST['leave_type_id'] ?? 0);
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $startHalf = $_POST['start_half'] ?? 'full';
        $endHalf = $_POST['end_half'] ?? 'full';
        $comment = trim($_POST['comment'] ?? '');

        // Validation
        $errors = [];

        if (!$leaveTypeId) {
            $errors[] = __('leave_type_required');
        }

        if (!$startDate) {
            $errors[] = __('start_date_required');
        }

        if (!$endDate) {
            $errors[] = __('end_date_required');
        }

        if ($startDate && $endDate && $startDate > $endDate) {
            $errors[] = __('end_date_before_start');
        }

        // Check if dates are in the past
        if ($startDate && $startDate < date('Y-m-d')) {
            $errors[] = __('start_date_past');
        }

        if ($errors) {
            $this->app->session()->flash('error', implode('<br>', $errors));
            $this->redirect('/leaves/new');
            return;
        }

        // Calculate working days
        $scheduleModel = new Schedule($this->app);
        $userScheduleModel = new UserSchedule($this->app);
        $holidayModel = new Holiday($this->app);

        $scheduleId = $userScheduleModel->getScheduleForUser($user['id'], $startDate);
        $schedule = $scheduleId ? $scheduleModel->findWithDays($scheduleId) : null;

        if (!$schedule) {
            $this->app->session()->flash('error', __('no_schedule_assigned'));
            $this->redirect('/leaves/new');
            return;
        }

        $workingDays = Helpers::calculateWorkingDays(
            $startDate,
            $endDate,
            $startHalf,
            $endHalf,
            $schedule,
            $holidayModel->getByCompany($user['company_id'])
        );

        if ($workingDays <= 0) {
            $this->app->session()->flash('error', __('no_working_days_selected'));
            $this->redirect('/leaves/new');
            return;
        }

        // Check allowance
        $currentYear = (int) date('Y');
        $allowanceModel = new Allowance($this->app);
        $allowance = $allowanceModel->getOrCreate($user['id'], $currentYear);

        $leaveModel = new Leave($this->app);
        $usedDays = $leaveModel->getUsedDays($user['id'], $currentYear);

        $totalAllowance = $allowance['entitled_days']
                        + $allowance['carried_over_days']
                        + $allowance['manual_adjustment_days'];
        $remainingDays = $totalAllowance - $usedDays;

        // Check leave type to see if it requires allowance
        $leaveTypeModel = new LeaveType($this->app);
        $leaveType = $leaveTypeModel->find($leaveTypeId);

        if ($leaveType && $leaveType['requires_allowance'] && $workingDays > $remainingDays) {
            $this->app->session()->flash('error', __('insufficient_allowance'));
            $this->redirect('/leaves/new');
            return;
        }

        // Check for conflicts
        $conflicts = $leaveModel->getByUserDateRange($user['id'], $startDate, $endDate);
        if ($conflicts) {
            $this->app->session()->flash('error', __('leave_conflict_exists'));
            $this->redirect('/leaves/new');
            return;
        }

        // Create leave request
        $leaveId = $leaveModel->create([
            'company_id' => $user['company_id'],
            'user_id' => $user['id'],
            'leave_type_id' => $leaveTypeId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start_half' => $startHalf,
            'end_half' => $endHalf,
            'days' => $workingDays,
            'status' => 'pending',
            'request_date' => date('Y-m-d H:i:s'),
            'employee_comment' => $comment ?: null,
        ]);

        // Send notification to manager
        $userModel = new User($this->app);
        $userFull = $userModel->findWithRelations($user['id']);

        if ($userFull && $userFull['manager_id']) {
            $manager = $userModel->find($userFull['manager_id']);
            if ($manager) {
                $mailer = new Mailer($this->app);
                $mailer->send(
                    $manager['email'],
                    __('email_leave_request_subject'),
                    __('email_leave_request_body', [
                        'employee_name' => $userFull['full_name'],
                        'leave_type' => $leaveType['name'],
                        'start_date' => date('M d, Y', strtotime($startDate)),
                        'end_date' => date('M d, Y', strtotime($endDate)),
                        'working_days' => $workingDays,
                        'url' => $this->app->url('/manager/approvals'),
                    ])
                );
            }
        }

        $this->app->session()->flash('success', __('leave_request_submitted'));
        $this->redirect('/leaves');
    }

    public function show(int $id): void
    {
        $this->requireAuth();

        $user = $this->auth->user();
        $leaveModel = new Leave($this->app);
        $leave = $leaveModel->findWithRelations($id);

        if (!$leave || $leave['user_id'] != $user['id']) {
            $this->app->session()->flash('error', __('leave_not_found'));
            $this->redirect('/leaves');
            return;
        }

        $userModel = new User($this->app);
        $userFull = $userModel->findWithRelations($user['id']);

        I18n::setLocale(Helpers::getUserLocale($userFull, null));

        $this->render('leaves/show', [
            'user' => $userFull,
            'leave' => $leave,
        ]);
    }

    public function cancel(int $id): void
    {
        $this->requireAuth();
        $this->validateCSRF();

        $user = $this->auth->user();
        $leaveModel = new Leave($this->app);
        $leave = $leaveModel->find($id);

        if (!$leave || $leave['user_id'] != $user['id']) {
            $this->app->session()->flash('error', __('leave_not_found'));
            $this->redirect('/leaves');
            return;
        }

        if ($leave['status'] !== 'pending' && $leave['status'] !== 'approved') {
            $this->app->session()->flash('error', __('cannot_cancel_leave'));
            $this->redirect('/leaves');
            return;
        }

        $leaveModel->cancel($id);

        $this->app->session()->flash('success', __('leave_cancelled'));
        $this->redirect('/leaves');
    }

    public function calendar(): void
    {
        $this->requireAuth();

        $user = $this->auth->user();
        $userModel = new User($this->app);
        $userFull = $userModel->findWithRelations($user['id']);

        I18n::setLocale(Helpers::getUserLocale($userFull, null));

        $year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');
        $month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('m');

        // Get leaves for the month
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $leaveModel = new Leave($this->app);
        $leaves = $leaveModel->getByUser($user['id'], 'approved');

        // Filter leaves that overlap with this month
        $monthLeaves = array_filter($leaves, function($leave) use ($startDate, $endDate) {
            return $leave['start_date'] <= $endDate && $leave['end_date'] >= $startDate;
        });

        // Get holidays
        $holidayModel = new Holiday($this->app);
        $holidays = $holidayModel->getByDateRange($user['company_id'], $startDate, $endDate);

        $this->render('leaves/calendar', [
            'user' => $userFull,
            'year' => $year,
            'month' => $month,
            'leaves' => $monthLeaves,
            'holidays' => $holidays,
        ]);
    }
}
