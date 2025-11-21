<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><?= __('my_leave_requests') ?></h1>
    <div>
        <a href="<?= $app->url('/leaves/calendar') ?>" class="btn btn-outline-primary">
            <i class="bi bi-calendar"></i> <?= __('calendar_view') ?>
        </a>
        <a href="<?= $app->url('/leaves/new') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> <?= __('request_leave') ?>
        </a>
    </div>
</div>

<?php if (empty($leaves)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> <?= __('no_leave_requests_yet') ?>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><?= __('leave_type') ?></th>
                            <th><?= __('start_date') ?></th>
                            <th><?= __('end_date') ?></th>
                            <th><?= __('days') ?></th>
                            <th><?= __('status') ?></th>
                            <th><?= __('actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leaves as $leave): ?>
                            <tr>
                                <td>
                                    <span class="badge" style="background-color: <?= e($leave['leave_type_color'] ?? '#6c757d') ?>;">
                                        <?= e($leave['leave_type_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= date('M d, Y', strtotime($leave['start_date'])) ?>
                                    <?php if ($leave['start_half'] === 'am'): ?>
                                        <small class="text-muted">(<?= __('morning') ?>)</small>
                                    <?php elseif ($leave['start_half'] === 'pm'): ?>
                                        <small class="text-muted">(<?= __('afternoon') ?>)</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= date('M d, Y', strtotime($leave['end_date'])) ?>
                                    <?php if ($leave['end_half'] === 'am'): ?>
                                        <small class="text-muted">(<?= __('morning') ?>)</small>
                                    <?php elseif ($leave['end_half'] === 'pm'): ?>
                                        <small class="text-muted">(<?= __('afternoon') ?>)</small>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($leave['days']) ?></td>
                                <td>
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
                                </td>
                                <td>
                                    <a href="<?= $app->url('/leaves/' . $leave['id']) ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> <?= __('view') ?>
                                    </a>
                                    <?php if ($leave['status'] === 'pending' || $leave['status'] === 'approved'): ?>
                                        <form method="POST" action="<?= $app->url('/leaves/' . $leave['id'] . '/cancel') ?>" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('<?= __('confirm_cancel_leave') ?>')">
                                                <i class="bi bi-x-circle"></i> <?= __('cancel') ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>
