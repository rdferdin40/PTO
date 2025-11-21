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

class ManagerController extends Controller
{
    public function approvals(): void
    {
        $this->requireAuth();
        $this->requireManager();

        $user = $this->auth->user();
        $userModel = new User($this->app);
        $userFull = $userModel->findWithRelations($user['id']);

        I18n::setLocale(Helpers::getUserLocale($userFull, null));

        $leaveModel = new Leave($this->app);
        $pendingLeaves = $leaveModel->getPendingForManager($user['id']);

        $this->render('manager/approvals', [
            'user' => $userFull,
            'pendingLeaves' => $pendingLeaves,
        ]);
    }

    public function approve(int $id): void
    {
        $this->requireAuth();
        $this->requireManager();
        $this->validateCSRF();

        $user = $this->auth->user();
        $comment = trim($_POST['comment'] ?? '');

        $leaveModel = new Leave($this->app);
        $leave = $leaveModel->findWithRelations($id);

        if (!$leave) {
            $this->app->session()->flash('error', __('leave_not_found'));
            $this->redirect('/manager/approvals');
            return;
        }

        // Verify manager has permission (same department)
        $userModel = new User($this->app);
        $manager = $userModel->find($user['id']);
        $employee = $userModel->find($leave['user_id']);

        if (!$manager || !$employee || $manager['department_id'] != $employee['department_id']) {
            $this->app->session()->flash('error', __('unauthorized_action'));
            $this->redirect('/manager/approvals');
            return;
        }

        // Approve leave
        $leaveModel->approve($id, $user['id'], $comment ?: null);

        // Send notification to employee
        $mailer = new Mailer($this->app);
        $mailer->send(
            $employee['email'],
            __('email_leave_approved_subject'),
            __('email_leave_approved_body', [
                'employee_name' => $employee['first_name'] . ' ' . $employee['last_name'],
                'leave_type' => $leave['leave_type_name'],
                'start_date' => date('M d, Y', strtotime($leave['start_date'])),
                'end_date' => date('M d, Y', strtotime($leave['end_date'])),
                'manager_name' => $user['first_name'] . ' ' . $user['last_name'],
                'comment' => $comment ?: '',
                'url' => $this->app->url('/leaves/' . $id),
            ])
        );

        $this->app->session()->flash('success', __('leave_approved_success'));
        $this->redirect('/manager/approvals');
    }

    public function reject(int $id): void
    {
        $this->requireAuth();
        $this->requireManager();
        $this->validateCSRF();

        $user = $this->auth->user();
        $comment = trim($_POST['comment'] ?? '');

        if (!$comment) {
            $this->app->session()->flash('error', __('rejection_reason_required'));
            $this->redirect('/manager/approvals');
            return;
        }

        $leaveModel = new Leave($this->app);
        $leave = $leaveModel->findWithRelations($id);

        if (!$leave) {
            $this->app->session()->flash('error', __('leave_not_found'));
            $this->redirect('/manager/approvals');
            return;
        }

        // Verify manager has permission (same department)
        $userModel = new User($this->app);
        $manager = $userModel->find($user['id']);
        $employee = $userModel->find($leave['user_id']);

        if (!$manager || !$employee || $manager['department_id'] != $employee['department_id']) {
            $this->app->session()->flash('error', __('unauthorized_action'));
            $this->redirect('/manager/approvals');
            return;
        }

        // Reject leave
        $leaveModel->reject($id, $user['id'], $comment);

        // Send notification to employee
        $mailer = new Mailer($this->app);
        $mailer->send(
            $employee['email'],
            __('email_leave_rejected_subject'),
            __('email_leave_rejected_body', [
                'employee_name' => $employee['first_name'] . ' ' . $employee['last_name'],
                'leave_type' => $leave['leave_type_name'],
                'start_date' => date('M d, Y', strtotime($leave['start_date'])),
                'end_date' => date('M d, Y', strtotime($leave['end_date'])),
                'manager_name' => $user['first_name'] . ' ' . $user['last_name'],
                'reason' => $comment,
                'url' => $this->app->url('/leaves/' . $id),
            ])
        );

        $this->app->session()->flash('success', __('leave_rejected_success'));
        $this->redirect('/manager/approvals');
    }

    public function teamCalendar(): void
    {
        $this->requireAuth();
        $this->requireManager();

        $user = $this->auth->user();
        $userModel = new User($this->app);
        $userFull = $userModel->findWithRelations($user['id']);

        I18n::setLocale(Helpers::getUserLocale($userFull, null));

        if (!$userFull['department_id']) {
            $this->app->session()->flash('error', __('no_department_assigned'));
            $this->redirect('/dashboard');
            return;
        }

        $year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');
        $month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('m');

        // Get leaves for the department
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $leaveModel = new Leave($this->app);
        $leaves = $leaveModel->getByDateRange($startDate, $endDate, $user['company_id'], $userFull['department_id']);

        // Get team members
        $teamMembers = $userModel->getByDepartment($userFull['department_id']);

        $this->render('manager/team_calendar', [
            'user' => $userFull,
            'year' => $year,
            'month' => $month,
            'leaves' => $leaves,
            'teamMembers' => $teamMembers,
        ]);
    }
}
