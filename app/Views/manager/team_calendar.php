<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><?= __('team_calendar') ?></h1>
    <div>
        <a href="<?= $app->url('/manager/approvals') ?>" class="btn btn-outline-primary">
            <i class="bi bi-clipboard-check"></i> <?= __('pending_approvals') ?>
        </a>
    </div>
</div>

<?php
$monthNames = [
    1 => __('january'), 2 => __('february'), 3 => __('march'), 4 => __('april'),
    5 => __('may'), 6 => __('june'), 7 => __('july'), 8 => __('august'),
    9 => __('september'), 10 => __('october'), 11 => __('november'), 12 => __('december')
];

$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}

$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}

$firstDay = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDay);
$dayOfWeek = date('w', $firstDay);

// Create leave lookup by date and user
$leavesByDate = [];
foreach ($leaves as $leave) {
    $start = new DateTime($leave['start_date']);
    $end = new DateTime($leave['end_date']);
    $end->modify('+1 day');

    $period = new DatePeriod($start, new DateInterval('P1D'), $end);
    foreach ($period as $date) {
        $dateKey = $date->format('Y-m-d');
        if (!isset($leavesByDate[$dateKey])) {
            $leavesByDate[$dateKey] = [];
        }
        $leavesByDate[$dateKey][] = $leave;
    }
}
?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <a href="<?= $app->url('/manager/team-calendar?year=' . $prevYear . '&month=' . $prevMonth) ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-chevron-left"></i> <?= __('previous') ?>
        </a>
        <h4 class="mb-0"><?= $monthNames[$month] ?> <?= $year ?></h4>
        <a href="<?= $app->url('/manager/team-calendar?year=' . $nextYear . '&month=' . $nextMonth) ?>" class="btn btn-sm btn-outline-primary">
            <?= __('next') ?> <i class="bi bi-chevron-right"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div class="calendar-grid">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr>
                        <th class="text-center"><?= __('sunday') ?></th>
                        <th class="text-center"><?= __('monday') ?></th>
                        <th class="text-center"><?= __('tuesday') ?></th>
                        <th class="text-center"><?= __('wednesday') ?></th>
                        <th class="text-center"><?= __('thursday') ?></th>
                        <th class="text-center"><?= __('friday') ?></th>
                        <th class="text-center"><?= __('saturday') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $day = 1;
                    $currentDate = date('Y-m-d');

                    for ($week = 0; $week < 6; $week++):
                        if ($day > $daysInMonth) break;
                    ?>
                        <tr>
                            <?php for ($dow = 0; $dow < 7; $dow++): ?>
                                <td class="calendar-day" style="height: 120px; vertical-align: top; position: relative;">
                                    <?php
                                    if (($week == 0 && $dow < $dayOfWeek) || $day > $daysInMonth):
                                    ?>
                                        &nbsp;
                                    <?php else:
                                        $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                        $isToday = ($dateStr === $currentDate);
                                        $isWeekend = ($dow == 0 || $dow == 6);
                                        $hasLeaves = isset($leavesByDate[$dateStr]);
                                    ?>
                                        <div class="p-2 <?= $isToday ? 'bg-primary text-white' : '' ?> <?= $isWeekend ? 'bg-light' : '' ?>" style="min-height: 120px;">
                                            <div class="fw-bold <?= $isToday ? 'text-white' : '' ?>">
                                                <?= $day ?>
                                            </div>
                                            <?php if ($hasLeaves): ?>
                                                <div class="mt-1" style="max-height: 90px; overflow-y: auto;">
                                                    <?php
                                                    // Group leaves by user
                                                    $userLeaves = [];
                                                    foreach ($leavesByDate[$dateStr] as $leave) {
                                                        $userLeaves[$leave['user_id']] = $leave;
                                                    }
                                                    foreach ($userLeaves as $leave):
                                                    ?>
                                                        <div class="mb-1">
                                                            <small class="badge" style="background-color: <?= e($leave['leave_type_color'] ?? '#6c757d') ?>; font-size: 0.65rem;" title="<?= e($leave['user_name']) ?> - <?= e($leave['leave_type_name']) ?>">
                                                                <?= e(substr($leave['user_name'], 0, 12)) ?>
                                                            </small>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php
                                        $day++;
                                    endif;
                                    ?>
                                </td>
                            <?php endfor; ?>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><?= __('upcoming_team_leaves') ?></h5>
            </div>
            <div class="card-body">
                <?php
                $upcomingLeaves = array_filter($leaves, function($leave) {
                    return $leave['start_date'] >= date('Y-m-d');
                });
                usort($upcomingLeaves, function($a, $b) {
                    return strcmp($a['start_date'], $b['start_date']);
                });
                $upcomingLeaves = array_slice($upcomingLeaves, 0, 10);
                ?>

                <?php if (empty($upcomingLeaves)): ?>
                    <p class="text-muted"><?= __('no_upcoming_leaves') ?></p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th><?= __('employee') ?></th>
                                    <th><?= __('leave_type') ?></th>
                                    <th><?= __('dates') ?></th>
                                    <th><?= __('days') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($upcomingLeaves as $leave): ?>
                                    <tr>
                                        <td><?= e($leave['user_name']) ?></td>
                                        <td>
                                            <span class="badge" style="background-color: <?= e($leave['leave_type_color'] ?? '#6c757d') ?>;">
                                                <?= e($leave['leave_type_name']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= date('M d', strtotime($leave['start_date'])) ?> -
                                            <?= date('M d, Y', strtotime($leave['end_date'])) ?>
                                        </td>
                                        <td><?= e($leave['days']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><?= __('team_members') ?></h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <?php foreach ($teamMembers as $member): ?>
                        <li class="mb-2">
                            <i class="bi bi-person-circle"></i>
                            <?= e($member['first_name'] . ' ' . $member['last_name']) ?>
                            <?php if ($member['is_manager']): ?>
                                <span class="badge bg-info"><?= __('manager') ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.calendar-day {
    padding: 0 !important;
}

.calendar-grid table {
    table-layout: fixed;
}

.calendar-grid th,
.calendar-grid td {
    width: 14.28%; /* 100% / 7 days */
}
</style>
