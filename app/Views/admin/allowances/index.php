<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><?= __('allowance_management') ?></h1>
    <div class="btn-group">
        <a href="<?= $app->url('/admin/allowances?year=' . ($year - 1)) ?>" class="btn btn-outline-secondary">
            <i class="bi bi-chevron-left"></i> <?= $year - 1 ?>
        </a>
        <button type="button" class="btn btn-outline-secondary" disabled><?= $year ?></button>
        <a href="<?= $app->url('/admin/allowances?year=' . ($year + 1)) ?>" class="btn btn-outline-secondary">
            <?= $year + 1 ?> <i class="bi bi-chevron-right"></i>
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><?= __('employee') ?></th>
                        <th><?= __('entitled_days') ?></th>
                        <th><?= __('carried_over') ?></th>
                        <th><?= __('manual_adjustment') ?></th>
                        <th><?= __('total_allowance') ?></th>
                        <th><?= __('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <?php
                        $allowance = $allowances[$u['id']] ?? null;
                        $entitled = $allowance['entitled_days'] ?? 0;
                        $carriedOver = $allowance['carried_over_days'] ?? 0;
                        $adjustment = $allowance['manual_adjustment_days'] ?? 0;
                        $total = $entitled + $carriedOver + $adjustment;
                        ?>
                        <tr>
                            <td><strong><?= e($u['first_name'] . ' ' . $u['last_name']) ?></strong></td>
                            <td><?= number_format($entitled, 1) ?></td>
                            <td><?= number_format($carriedOver, 1) ?></td>
                            <td><?= number_format($adjustment, 1) ?></td>
                            <td><strong><?= number_format($total, 1) ?></strong></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editAllowanceModal<?= $u['id'] ?>">
                                    <i class="bi bi-pencil"></i> <?= __('edit') ?>
                                </button>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editAllowanceModal<?= $u['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"><?= __('edit_allowance') ?> - <?= e($u['first_name'] . ' ' . $u['last_name']) ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" action="<?= $app->url('/admin/allowances') ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <input type="hidden" name="year" value="<?= $year ?>">

                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="entitled_days<?= $u['id'] ?>" class="form-label"><?= __('entitled_days') ?></label>
                                                <input type="number" step="0.5" name="entitled_days" id="entitled_days<?= $u['id'] ?>" class="form-control" value="<?= $entitled ?>" required>
                                                <small class="form-text text-muted"><?= __('entitled_days_help') ?></small>
                                            </div>

                                            <div class="mb-3">
                                                <label for="carried_over_days<?= $u['id'] ?>" class="form-label"><?= __('carried_over_days') ?></label>
                                                <input type="number" step="0.5" name="carried_over_days" id="carried_over_days<?= $u['id'] ?>" class="form-control" value="<?= $carriedOver ?>" required>
                                                <small class="form-text text-muted"><?= __('carried_over_days_help') ?></small>
                                            </div>

                                            <div class="mb-3">
                                                <label for="manual_adjustment_days<?= $u['id'] ?>" class="form-label"><?= __('manual_adjustment_days') ?></label>
                                                <input type="number" step="0.5" name="manual_adjustment_days" id="manual_adjustment_days<?= $u['id'] ?>" class="form-control" value="<?= $adjustment ?>" required>
                                                <small class="form-text text-muted"><?= __('manual_adjustment_days_help') ?></small>
                                            </div>

                                            <div class="alert alert-info">
                                                <strong><?= __('total_allowance') ?>:</strong>
                                                <span id="total<?= $u['id'] ?>"><?= number_format($total, 1) ?></span> <?= __('days') ?>
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

                        <script>
                        (function() {
                            const entitled = document.getElementById('entitled_days<?= $u['id'] ?>');
                            const carriedOver = document.getElementById('carried_over_days<?= $u['id'] ?>');
                            const adjustment = document.getElementById('manual_adjustment_days<?= $u['id'] ?>');
                            const total = document.getElementById('total<?= $u['id'] ?>');

                            function updateTotal() {
                                const sum = parseFloat(entitled.value || 0) + parseFloat(carriedOver.value || 0) + parseFloat(adjustment.value || 0);
                                total.textContent = sum.toFixed(1);
                            }

                            entitled.addEventListener('input', updateTotal);
                            carriedOver.addEventListener('input', updateTotal);
                            adjustment.addEventListener('input', updateTotal);
                        })();
                        </script>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
