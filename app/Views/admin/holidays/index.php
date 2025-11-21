<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><?= __('holiday_management') ?></h1>
    <div>
        <div class="btn-group">
            <a href="<?= $app->url('/admin/holidays?year=' . ($year - 1)) ?>" class="btn btn-outline-secondary">
                <i class="bi bi-chevron-left"></i> <?= $year - 1 ?>
            </a>
            <button type="button" class="btn btn-outline-secondary" disabled><?= $year ?></button>
            <a href="<?= $app->url('/admin/holidays?year=' . ($year + 1)) ?>" class="btn btn-outline-secondary">
                <?= $year + 1 ?> <i class="bi bi-chevron-right"></i>
            </a>
        </div>
        <button type="button" class="btn btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#addHolidayModal">
            <i class="bi bi-plus-circle"></i> <?= __('add_holiday') ?>
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><?= __('name') ?></th>
                        <th><?= __('date') ?></th>
                        <th><?= __('day_of_week') ?></th>
                        <th><?= __('blackout_date') ?></th>
                        <th><?= __('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($holidays as $holiday): ?>
                        <tr>
                            <td><strong><?= e($holiday['name']) ?></strong></td>
                            <td><?= date('M d, Y', strtotime($holiday['date'])) ?></td>
                            <td><?= date('l', strtotime($holiday['date'])) ?></td>
                            <td>
                                <?php if ($holiday['is_blackout']): ?>
                                    <span class="badge bg-danger"><?= __('blackout') ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= __('normal') ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editHolidayModal<?= $holiday['id'] ?>">
                                    <i class="bi bi-pencil"></i> <?= __('edit') ?>
                                </button>
                                <form method="POST" action="<?= $app->url('/admin/holidays/' . $holiday['id'] . '/delete') ?>" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('<?= __('confirm_delete_holiday') ?>')">
                                        <i class="bi bi-trash"></i> <?= __('delete') ?>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editHolidayModal<?= $holiday['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"><?= __('edit_holiday') ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" action="<?= $app->url('/admin/holidays/' . $holiday['id']) ?>">
                                        <?= csrf_field() ?>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="name<?= $holiday['id'] ?>" class="form-label"><?= __('holiday_name') ?> <span class="text-danger">*</span></label>
                                                <input type="text" name="name" id="name<?= $holiday['id'] ?>" class="form-control" value="<?= e($holiday['name']) ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="date<?= $holiday['id'] ?>" class="form-label"><?= __('date') ?> <span class="text-danger">*</span></label>
                                                <input type="date" name="date" id="date<?= $holiday['id'] ?>" class="form-control" value="<?= e($holiday['date']) ?>" required>
                                            </div>

                                            <div class="mb-3 form-check">
                                                <input type="checkbox" name="is_blackout" id="is_blackout<?= $holiday['id'] ?>" class="form-check-input" <?= $holiday['is_blackout'] ? 'checked' : '' ?>>
                                                <label for="is_blackout<?= $holiday['id'] ?>" class="form-check-label"><?= __('blackout_date') ?></label>
                                                <small class="form-text text-muted d-block"><?= __('blackout_date_help') ?></small>
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
<div class="modal fade" id="addHolidayModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= __('add_holiday') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= $app->url('/admin/holidays') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label"><?= __('holiday_name') ?> <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="date" class="form-label"><?= __('date') ?> <span class="text-danger">*</span></label>
                        <input type="date" name="date" id="date" class="form-control" required>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_blackout" id="is_blackout" class="form-check-input">
                        <label for="is_blackout" class="form-check-label"><?= __('blackout_date') ?></label>
                        <small class="form-text text-muted d-block"><?= __('blackout_date_help') ?></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('cancel') ?></button>
                    <button type="submit" class="btn btn-primary"><?= __('add_holiday') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
