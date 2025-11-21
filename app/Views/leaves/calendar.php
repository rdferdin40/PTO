<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><?= __('my_leave_calendar') ?></h1>
    <div>
        <a href="<?= $app->url('/leaves') ?>" class="btn btn-outline-primary">
            <i class="bi bi-list"></i> <?= __('list_view') ?>
        </a>
        <a href="<?= $app->url('/leaves/new') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> <?= __('request_leave') ?>
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

// Create leave lookup by date
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

// Create holiday lookup by date
$holidaysByDate = [];
foreach ($holidays as $holiday) {
    $holidaysByDate[$holiday['date']] = $holiday;
}
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <a href="<?= $app->url('/leaves/calendar?year=' . $prevYear . '&month=' . $prevMonth) ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-chevron-left"></i> <?= __('previous') ?>
        </a>
        <h4 class="mb-0"><?= $monthNames[$month] ?> <?= $year ?></h4>
        <a href="<?= $app->url('/leaves/calendar?year=' . $nextYear . '&month=' . $nextMonth) ?>" class="btn btn-sm btn-outline-primary">
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
                                <td class="calendar-day" style="height: 100px; vertical-align: top; position: relative;">
                                    <?php
                                    if (($week == 0 && $dow < $dayOfWeek) || $day > $daysInMonth):
                                        // Empty cell
                                    ?>
                                        &nbsp;
                                    <?php else:
                                        $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                        $isToday = ($dateStr === $currentDate);
                                        $isWeekend = ($dow == 0 || $dow == 6);
                                        $hasLeave = isset($leavesByDate[$dateStr]);
                                        $hasHoliday = isset($holidaysByDate[$dateStr]);
                                    ?>
                                        <div class="p-2 <?= $isToday ? 'bg-primary text-white' : '' ?> <?= $isWeekend ? 'bg-light' : '' ?>" style="min-height: 100px;">
                                            <div class="fw-bold <?= $isToday ? 'text-white' : '' ?>">
                                                <?= $day ?>
                                            </div>
                                            <?php if ($hasHoliday): ?>
                                                <div class="mt-1">
                                                    <small class="badge bg-info text-dark" title="<?= e($holidaysByDate[$dateStr]['name']) ?>">
                                                        <?= e(substr($holidaysByDate[$dateStr]['name'], 0, 15)) ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($hasLeave): ?>
                                                <?php foreach ($leavesByDate[$dateStr] as $leave): ?>
                                                    <div class="mt-1">
                                                        <small class="badge" style="background-color: <?= e($leave['leave_type_color'] ?? '#6c757d') ?>; font-size: 0.7rem;">
                                                            <?= e(substr($leave['leave_type_name'], 0, 10)) ?>
                                                        </small>
                                                    </div>
                                                <?php endforeach; ?>
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

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><?= __('legend') ?></h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <span class="badge bg-primary"><?= __('today') ?></span>
                    <span class="ms-2"><?= __('current_day') ?></span>
                </div>
                <div class="mb-2">
                    <span class="badge bg-light text-dark"><?= __('weekend') ?></span>
                    <span class="ms-2"><?= __('weekend_day') ?></span>
                </div>
                <div class="mb-2">
                    <span class="badge bg-info text-dark"><?= __('holiday') ?></span>
                    <span class="ms-2"><?= __('company_holiday') ?></span>
                </div>
                <?php foreach ($leaves as $leave): ?>
                    <div class="mb-2">
                        <span class="badge" style="background-color: <?= e($leave['leave_type_color'] ?? '#6c757d') ?>;">
                            <?= e($leave['leave_type_name']) ?>
                        </span>
                        <span class="ms-2"><?= __('your_leave') ?></span>
                    </div>
                <?php endforeach; ?>
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
