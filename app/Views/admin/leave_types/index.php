<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><?= __('leave_type_management') ?></h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeaveTypeModal">
        <i class="bi bi-plus-circle"></i> <?= __('add_leave_type') ?>
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><?= __('name') ?></th>
                        <th><?= __('color') ?></th>
                        <th><?= __('requires_allowance') ?></th>
                        <th><?= __('deducts_allowance') ?></th>
                        <th><?= __('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaveTypes as $type): ?>
                        <tr>
                            <td>
                                <span class="badge" style="background-color: <?= e($type['color']) ?>;">
                                    <?= e($type['name']) ?>
                                </span>
                            </td>
                            <td><?= e($type['color']) ?></td>
                            <td>
                                <?php if ($type['requires_allowance']): ?>
                                    <i class="bi bi-check-circle text-success"></i> <?= __('yes') ?>
                                <?php else: ?>
                                    <i class="bi bi-x-circle text-muted"></i> <?= __('no') ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($type['deducts_allowance']): ?>
                                    <i class="bi bi-check-circle text-success"></i> <?= __('yes') ?>
                                <?php else: ?>
                                    <i class="bi bi-x-circle text-muted"></i> <?= __('no') ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editLeaveTypeModal<?= $type['id'] ?>">
                                    <i class="bi bi-pencil"></i> <?= __('edit') ?>
                                </button>
                                <form method="POST" action="<?= $app->url('/admin/leave-types/' . $type['id'] . '/delete') ?>" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('<?= __('confirm_delete_leave_type') ?>')">
                                        <i class="bi bi-trash"></i> <?= __('delete') ?>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editLeaveTypeModal<?= $type['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"><?= __('edit_leave_type') ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" action="<?= $app->url('/admin/leave-types/' . $type['id']) ?>">
                                        <?= csrf_field() ?>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="name<?= $type['id'] ?>" class="form-label"><?= __('leave_type_name') ?> <span class="text-danger">*</span></label>
                                                <input type="text" name="name" id="name<?= $type['id'] ?>" class="form-control" value="<?= e($type['name']) ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="color<?= $type['id'] ?>" class="form-label"><?= __('color') ?> <span class="text-danger">*</span></label>
                                                <input type="color" name="color" id="color<?= $type['id'] ?>" class="form-control form-control-color" value="<?= e($type['color']) ?>" required>
                                            </div>

                                            <div class="mb-3 form-check">
                                                <input type="checkbox" name="requires_allowance" id="requires_allowance<?= $type['id'] ?>" class="form-check-input" <?= $type['requires_allowance'] ? 'checked' : '' ?>>
                                                <label for="requires_allowance<?= $type['id'] ?>" class="form-check-label"><?= __('requires_allowance') ?></label>
                                                <small class="form-text text-muted d-block"><?= __('requires_allowance_help') ?></small>
                                            </div>

                                            <div class="mb-3 form-check">
                                                <input type="checkbox" name="deducts_allowance" id="deducts_allowance<?= $type['id'] ?>" class="form-check-input" <?= $type['deducts_allowance'] ? 'checked' : '' ?>>
                                                <label for="deducts_allowance<?= $type['id'] ?>" class="form-check-label"><?= __('deducts_allowance') ?></label>
                                                <small class="form-text text-muted d-block"><?= __('deducts_allowance_help') ?></small>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('cancel') ?></button>
                                            <button type="submit" class="btn btn-primary"><?= __('save_changes') ?></button>
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

<!-- Add Modal -->
<div class="modal fade" id="addLeaveTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= __('add_leave_type') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= $app->url('/admin/leave-types') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label"><?= __('leave_type_name') ?> <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="color" class="form-label"><?= __('color') ?> <span class="text-danger">*</span></label>
                        <input type="color" name="color" id="color" class="form-control form-control-color" value="#6c757d" required>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="requires_allowance" id="requires_allowance" class="form-check-input" checked>
                        <label for="requires_allowance" class="form-check-label"><?= __('requires_allowance') ?></label>
                        <small class="form-text text-muted d-block"><?= __('requires_allowance_help') ?></small>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="deducts_allowance" id="deducts_allowance" class="form-check-input" checked>
                        <label for="deducts_allowance" class="form-check-label"><?= __('deducts_allowance') ?></label>
                        <small class="form-text text-muted d-block"><?= __('deducts_allowance_help') ?></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('cancel') ?></button>
                    <button type="submit" class="btn btn-primary"><?= __('add_leave_type') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
