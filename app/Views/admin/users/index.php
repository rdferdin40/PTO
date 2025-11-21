<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><?= __('user_management') ?></h1>
    <a href="<?= $app->url('/admin/users/new') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> <?= __('add_user') ?>
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><?= __('name') ?></th>
                        <th><?= __('email') ?></th>
                        <th><?= __('department') ?></th>
                        <th><?= __('role') ?></th>
                        <th><?= __('status') ?></th>
                        <th><?= __('actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>
                                <strong><?= e($u['first_name'] . ' ' . $u['last_name']) ?></strong>
                                <?php if ($u['is_manager']): ?>
                                    <span class="badge bg-info"><?= __('manager') ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($u['email']) ?></td>
                            <td><?= e($u['department_name'] ?? __('no_department')) ?></td>
                            <td>
                                <?php
                                $roleBadge = match($u['role']) {
                                    'admin' => 'danger',
                                    'manager' => 'warning',
                                    default => 'secondary',
                                };
                                ?>
                                <span class="badge bg-<?= $roleBadge ?>">
                                    <?= __(match($u['role']) {
                                        'admin' => 'role_admin',
                                        'manager' => 'role_manager',
                                        default => 'role_employee',
                                    }) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($u['is_active']): ?>
                                    <span class="badge bg-success"><?= __('active') ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= __('inactive') ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= $app->url('/admin/users/' . $u['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i> <?= __('edit') ?>
                                </a>
                                <?php if ($u['id'] != $user['id']): ?>
                                    <form method="POST" action="<?= $app->url('/admin/users/' . $u['id'] . '/delete') ?>" style="display: inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('<?= __('confirm_delete_user') ?>')">
                                            <i class="bi bi-trash"></i> <?= __('delete') ?>
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
