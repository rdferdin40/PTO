<div class="mb-4">
    <h1><?= __('leave_request_details') ?></h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $app->url('/leaves') ?>"><?= __('my_leave_requests') ?></a></li>
            <li class="breadcrumb-item active"><?= __('request') ?> #<?= $leave['id'] ?></li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?= __('request_information') ?></h5>
                <?php
                $statusClass = match($leave['status']) {
                    'pending' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                    'cancelled' => 'secondary',
                    default => 'secondary',
                };
                ?>
                <span class="badge bg-<?= $statusClass ?>">
                    <?= __(match($leave['status']) {
                        'pending' => 'status_pending',
                        'approved' => 'status_approved',
                        'rejected' => 'status_rejected',
                        'cancelled' => 'status_cancelled',
                        default => 'status_unknown',
                    }) ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><?= __('leave_type') ?>:</strong>
                    </div>
                    <div class="col-md-8">
                        <span class="badge" style="background-color: <?= e($leave['leave_type_color'] ?? '#6c757d') ?>;">
                            <?= e($leave['leave_type_name']) ?>
                        </span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><?= __('start_date') ?>:</strong>
                    </div>
                    <div class="col-md-8">
                        <?= date('l, F j, Y', strtotime($leave['start_date'])) ?>
                        <?php if ($leave['start_half'] === 'am'): ?>
                            <span class="badge bg-secondary"><?= __('morning_only') ?></span>
                        <?php elseif ($leave['start_half'] === 'pm'): ?>
                            <span class="badge bg-secondary"><?= __('afternoon_only') ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><?= __('end_date') ?>:</strong>
                    </div>
                    <div class="col-md-8">
                        <?= date('l, F j, Y', strtotime($leave['end_date'])) ?>
                        <?php if ($leave['end_half'] === 'am'): ?>
                            <span class="badge bg-secondary"><?= __('morning_only') ?></span>
                        <?php elseif ($leave['end_half'] === 'pm'): ?>
                            <span class="badge bg-secondary"><?= __('afternoon_only') ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><?= __('working_days') ?>:</strong>
                    </div>
                    <div class="col-md-8">
                        <?= e($leave['days']) ?> <?= __('days') ?>
                    </div>
                </div>

                <?php if ($leave['employee_comment']): ?>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><?= __('your_comment') ?>:</strong>
                        </div>
                        <div class="col-md-8">
                            <p class="mb-0"><?= nl2br(e($leave['employee_comment'])) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <hr>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong><?= __('requested_on') ?>:</strong>
                    </div>
                    <div class="col-md-8">
                        <?= date('M d, Y \a\t g:i A', strtotime($leave['created_at'])) ?>
                    </div>
                </div>

                <?php if ($leave['reviewed_by']): ?>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><?= __('reviewed_by') ?>:</strong>
                        </div>
                        <div class="col-md-8">
                            <?= e($leave['reviewed_by_name'] ?? __('unknown')) ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><?= __('reviewed_on') ?>:</strong>
                        </div>
                        <div class="col-md-8">
                            <?= date('M d, Y \a\t g:i A', strtotime($leave['reviewed_at'])) ?>
                        </div>
                    </div>

                    <?php if ($leave['manager_comment']): ?>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong><?= __('manager_comment') ?>:</strong>
                            </div>
                            <div class="col-md-8">
                                <div class="alert alert-<?= $leave['status'] === 'rejected' ? 'danger' : 'info' ?> mb-0">
                                    <?= nl2br(e($leave['manager_comment'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <a href="<?= $app->url('/leaves') ?>" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> <?= __('back_to_list') ?>
                    </a>
                    <?php if ($leave['status'] === 'pending' || $leave['status'] === 'approved'): ?>
                        <form method="POST" action="<?= $app->url('/leaves/' . $leave['id'] . '/cancel') ?>" style="display: inline;">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger" onclick="return confirm('<?= __('confirm_cancel_leave') ?>')">
                                <i class="bi bi-x-circle"></i> <?= __('cancel_request') ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <?php if ($leave['status'] === 'pending'): ?>
            <div class="alert alert-warning">
                <i class="bi bi-clock"></i>
                <strong><?= __('pending_approval') ?></strong>
                <p class="mb-0 mt-2 small"><?= __('pending_approval_text') ?></p>
            </div>
        <?php elseif ($leave['status'] === 'approved'): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i>
                <strong><?= __('approved') ?></strong>
                <p class="mb-0 mt-2 small"><?= __('approved_text') ?></p>
            </div>
        <?php elseif ($leave['status'] === 'rejected'): ?>
            <div class="alert alert-danger">
                <i class="bi bi-x-circle"></i>
                <strong><?= __('rejected') ?></strong>
                <p class="mb-0 mt-2 small"><?= __('rejected_text') ?></p>
            </div>
        <?php elseif ($leave['status'] === 'cancelled'): ?>
            <div class="alert alert-secondary">
                <i class="bi bi-slash-circle"></i>
                <strong><?= __('cancelled') ?></strong>
                <p class="mb-0 mt-2 small"><?= __('cancelled_text') ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>
