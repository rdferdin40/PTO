<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><?= __('department_management') ?></h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
        <i class="bi bi-plus-circle"></i> <?= __('add_department') ?>
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><?= __('name') ?></th>
                        <th><?= __('created_at') ?></th>
                        <th><?= __('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departments as $dept): ?>
                        <tr>
                            <td><strong><?= e($dept['name']) ?></strong></td>
                            <td><?= date('M d, Y', strtotime($dept['created_at'])) ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editDepartmentModal<?= $dept['id'] ?>">
                                    <i class="bi bi-pencil"></i> <?= __('edit') ?>
                                </button>
                                <form method="POST" action="<?= $app->url('/admin/departments/' . $dept['id'] . '/delete') ?>" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('<?= __('confirm_delete_department') ?>')">
                                        <i class="bi bi-trash"></i> <?= __('delete') ?>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editDepartmentModal<?= $dept['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"><?= __('edit_department') ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" action="<?= $app->url('/admin/departments/' . $dept['id']) ?>">
                                        <?= csrf_field() ?>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="name<?= $dept['id'] ?>" class="form-label"><?= __('department_name') ?> <span class="text-danger">*</span></label>
                                                <input type="text" name="name" id="name<?= $dept['id'] ?>" class="form-control" value="<?= e($dept['name']) ?>" required>
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
<div class="modal fade" id="addDepartmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= __('add_department') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= $app->url('/admin/departments') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label"><?= __('department_name') ?> <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('cancel') ?></button>
                    <button type="submit" class="btn btn-primary"><?= __('add_department') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
