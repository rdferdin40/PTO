<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><?= __('pending_approvals') ?></h1>
    <a href="<?= $app->url('/manager/team-calendar') ?>" class="btn btn-outline-primary">
        <i class="bi bi-calendar3"></i> <?= __('team_calendar') ?>
    </a>
</div>

<?php if (empty($pendingLeaves)): ?>
    <div class="alert alert-success">
        <i class="bi bi-check-circle"></i> <?= __('no_pending_approvals') ?>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><?= __('employee') ?></th>
                            <th><?= __('leave_type') ?></th>
                            <th><?= __('dates') ?></th>
                            <th><?= __('days') ?></th>
                            <th><?= __('requested_on') ?></th>
                            <th><?= __('actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingLeaves as $leave): ?>
                            <tr>
                                <td>
                                    <strong><?= e($leave['user_name']) ?></strong><br>
                                    <small class="text-muted"><?= e($leave['user_email']) ?></small>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: <?= e($leave['leave_type_color'] ?? '#6c757d') ?>;">
                                        <?= e($leave['leave_type_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div>
                                        <strong><?= __('from') ?>:</strong> <?= date('M d, Y', strtotime($leave['start_date'])) ?>
                                        <?php if ($leave['start_half'] === 'am'): ?>
                                            <span class="badge bg-secondary"><?= __('am') ?></span>
                                        <?php elseif ($leave['start_half'] === 'pm'): ?>
                                            <span class="badge bg-secondary"><?= __('pm') ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <strong><?= __('to') ?>:</strong> <?= date('M d, Y', strtotime($leave['end_date'])) ?>
                                        <?php if ($leave['end_half'] === 'am'): ?>
                                            <span class="badge bg-secondary"><?= __('am') ?></span>
                                        <?php elseif ($leave['end_half'] === 'pm'): ?>
                                            <span class="badge bg-secondary"><?= __('pm') ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?= e($leave['days']) ?></td>
                                <td><?= date('M d, Y', strtotime($leave['request_date'])) ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal<?= $leave['id'] ?>">
                                        <i class="bi bi-check-circle"></i> <?= __('approve') ?>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $leave['id'] ?>">
                                        <i class="bi bi-x-circle"></i> <?= __('reject') ?>
                                    </button>
                                </td>
                            </tr>

                            <!-- Approve Modal -->
                            <div class="modal fade" id="approveModal<?= $leave['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"><?= __('approve_leave_request') ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST" action="<?= $app->url('/manager/approvals/' . $leave['id'] . '/approve') ?>">
                                            <?= csrf_field() ?>
                                            <div class="modal-body">
                                                <div class="alert alert-info">
                                                    <strong><?= e($leave['user_name']) ?></strong> -
                                                    <?= e($leave['leave_type_name']) ?><br>
                                                    <?= date('M d, Y', strtotime($leave['start_date'])) ?> -
                                                    <?= date('M d, Y', strtotime($leave['end_date'])) ?>
                                                    (<?= e($leave['days']) ?> <?= __('days') ?>)
                                                </div>

                                                <?php if ($leave['employee_comment']): ?>
                                                    <div class="mb-3">
                                                        <label class="form-label"><?= __('employee_comment') ?>:</label>
                                                        <div class="alert alert-light">
                                                            <?= nl2br(e($leave['employee_comment'])) ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="mb-3">
                                                    <label for="approve_comment<?= $leave['id'] ?>" class="form-label"><?= __('comment_optional') ?>:</label>
                                                    <textarea name="comment" id="approve_comment<?= $leave['id'] ?>" class="form-control" rows="3" placeholder="<?= __('approval_comment_placeholder') ?>"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('cancel') ?></button>
                                                <button type="submit" class="btn btn-success">
                                                    <i class="bi bi-check-circle"></i> <?= __('approve_leave') ?>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal<?= $leave['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title"><?= __('reject_leave_request') ?></h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST" action="<?= $app->url('/manager/approvals/' . $leave['id'] . '/reject') ?>">
                                            <?= csrf_field() ?>
                                            <div class="modal-body">
                                                <div class="alert alert-warning">
                                                    <strong><?= e($leave['user_name']) ?></strong> -
                                                    <?= e($leave['leave_type_name']) ?><br>
                                                    <?= date('M d, Y', strtotime($leave['start_date'])) ?> -
                                                    <?= date('M d, Y', strtotime($leave['end_date'])) ?>
                                                    (<?= e($leave['days']) ?> <?= __('days') ?>)
                                                </div>

                                                <?php if ($leave['employee_comment']): ?>
                                                    <div class="mb-3">
                                                        <label class="form-label"><?= __('employee_comment') ?>:</label>
                                                        <div class="alert alert-light">
                                                            <?= nl2br(e($leave['employee_comment'])) ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="mb-3">
                                                    <label for="reject_comment<?= $leave['id'] ?>" class="form-label"><?= __('rejection_reason') ?>: <span class="text-danger">*</span></label>
                                                    <textarea name="comment" id="reject_comment<?= $leave['id'] ?>" class="form-control" rows="3" required placeholder="<?= __('rejection_reason_placeholder') ?>"></textarea>
                                                    <small class="form-text text-muted"><?= __('rejection_reason_help') ?></small>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('cancel') ?></button>
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="bi bi-x-circle"></i> <?= __('reject_leave') ?>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>
