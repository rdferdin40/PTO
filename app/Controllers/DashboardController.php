<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\I18n;
use App\Core\Helpers;
use App\Models\User;
use App\Models\Allowance;
use App\Models\Leave;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $user = $this->auth->user();

        $userModel = new User($this->app);
        $userFull = $userModel->findWithRelations($user['id']);

        I18n::setLocale(Helpers::getUserLocale($userFull, null));

        $currentYear = (int) date('Y');
        $allowanceModel = new Allowance($this->app);
        $allowance = $allowanceModel->getOrCreate($user['id'], $currentYear);

        $leaveModel = new Leave($this->app);
        $usedDays = $leaveModel->getUsedDays($user['id'], $currentYear);

        $totalAllowance = $allowance['entitled_days']
                        + $allowance['carried_over_days']
                        + $allowance['manual_adjustment_days'];
        $remainingDays = $totalAllowance - $usedDays;

        $nextLeave = null;
        $approvedLeaves = $leaveModel->getByUser($user['id'], 'approved');
        $today = date('Y-m-d');

        foreach ($approvedLeaves as $leave) {
            if ($leave['start_date'] >= $today) {
                $nextLeave = $leave;
                break;
            }
        }

        $todayOff = [];
        if ($userFull['department_id']) {
            $todayOff = $leaveModel->getByDateRange($today, $today, $user['company_id'], $userFull['department_id']);
            $todayOff = array_filter($todayOff, fn($l) => $l['user_id'] != $user['id']);
        }

        $this->render('dashboard/index', [
            'user' => $userFull,
            'allowance' => [
                'entitled' => $totalAllowance,
                'used' => $usedDays,
                'remaining' => $remainingDays,
            ],
            'nextLeave' => $nextLeave,
            'todayOff' => $todayOff,
        ]);
    }
}
