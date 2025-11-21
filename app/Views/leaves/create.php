<div class="mb-4">
    <h1><?= __('request_leave') ?></h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $app->url('/leaves') ?>"><?= __('my_leave_requests') ?></a></li>
            <li class="breadcrumb-item active"><?= __('new_request') ?></li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?= $app->url('/leaves') ?>" id="leaveRequestForm">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="leave_type_id" class="form-label"><?= __('leave_type') ?> <span class="text-danger">*</span></label>
                        <select name="leave_type_id" id="leave_type_id" class="form-select" required>
                            <option value=""><?= __('select_leave_type') ?></option>
                            <?php foreach ($leaveTypes as $type): ?>
                                <option value="<?= $type['id'] ?>" data-color="<?= e($type['color']) ?>" data-requires-allowance="<?= $type['requires_allowance'] ? '1' : '0' ?>">
                                    <?= e($type['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted"><?= __('leave_type_help') ?></small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label"><?= __('start_date') ?> <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="start_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="start_half" class="form-label"><?= __('period') ?></label>
                            <select name="start_half" id="start_half" class="form-select">
                                <option value="full"><?= __('full_day') ?></option>
                                <option value="am"><?= __('morning_only') ?></option>
                                <option value="pm"><?= __('afternoon_only') ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label"><?= __('end_date') ?> <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="end_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="end_half" class="form-label"><?= __('period') ?></label>
                            <select name="end_half" id="end_half" class="form-select">
                                <option value="full"><?= __('full_day') ?></option>
                                <option value="am"><?= __('morning_only') ?></option>
                                <option value="pm"><?= __('afternoon_only') ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="comment" class="form-label"><?= __('comment_optional') ?></label>
                        <textarea name="comment" id="comment" class="form-control" rows="3" placeholder="<?= __('leave_comment_placeholder') ?>"></textarea>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <span id="workingDaysInfo"><?= __('select_dates_to_calculate') ?></span>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= $app->url('/leaves') ?>" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> <?= __('back') ?>
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> <?= __('submit_request') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0"><?= __('your_allowance') ?></h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span><?= __('remaining_days') ?>:</span>
                    <strong class="text-primary"><?= number_format($remainingDays, 1) ?></strong>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> <?= __('tips') ?></h5>
            </div>
            <div class="card-body">
                <ul class="small mb-0">
                    <li><?= __('tip_select_dates') ?></li>
                    <li><?= __('tip_half_days') ?></li>
                    <li><?= __('tip_manager_approval') ?></li>
                    <li><?= __('tip_advance_notice') ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const startHalfSelect = document.getElementById('start_half');
    const endHalfSelect = document.getElementById('end_half');
    const workingDaysInfo = document.getElementById('workingDaysInfo');

    function updateEndDateMin() {
        if (startDateInput.value) {
            endDateInput.min = startDateInput.value;
            if (endDateInput.value && endDateInput.value < startDateInput.value) {
                endDateInput.value = startDateInput.value;
            }
        }
    }

    function calculateEstimatedDays() {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);

        if (startDateInput.value && endDateInput.value && startDate <= endDate) {
            // Simple estimation (excludes weekends)
            let days = 0;
            let current = new Date(startDate);

            while (current <= endDate) {
                const dayOfWeek = current.getDay();
                if (dayOfWeek !== 0 && dayOfWeek !== 6) { // Not Sunday or Saturday
                    days++;
                }
                current.setDate(current.getDate() + 1);
            }

            // Adjust for half days
            if (startHalfSelect.value !== 'full') {
                days -= 0.5;
            }
            if (endHalfSelect.value !== 'full' && startDate.getTime() !== endDate.getTime()) {
                days -= 0.5;
            }

            workingDaysInfo.innerHTML = `<strong><?= __('estimated_working_days') ?>: ${days.toFixed(1)}</strong> <?= __('excluding_weekends_note') ?>`;
        } else {
            workingDaysInfo.textContent = '<?= __('select_dates_to_calculate') ?>';
        }
    }

    startDateInput.addEventListener('change', function() {
        updateEndDateMin();
        calculateEstimatedDays();
    });

    endDateInput.addEventListener('change', calculateEstimatedDays);
    startHalfSelect.addEventListener('change', calculateEstimatedDays);
    endHalfSelect.addEventListener('change', calculateEstimatedDays);

    // Initialize
    updateEndDateMin();
});
</script>
