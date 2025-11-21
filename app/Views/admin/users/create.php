<div class="mb-4">
    <h1><?= __('add_user') ?></h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $app->url('/admin/users') ?>"><?= __('users') ?></a></li>
            <li class="breadcrumb-item active"><?= __('add_new') ?></li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?= $app->url('/admin/users') ?>">
                    <?= csrf_field() ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label"><?= __('first_name') ?> <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" id="first_name" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label"><?= __('last_name') ?> <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" id="last_name" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label"><?= __('email') ?> <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label"><?= __('password') ?> <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password" class="form-control" required minlength="8">
                        <small class="form-text text-muted"><?= __('password_min_length') ?></small>
                    </div>

                    <div class="mb-3">
                        <label for="department_id" class="form-label"><?= __('department') ?></label>
                        <select name="department_id" id="department_id" class="form-select">
                            <option value=""><?= __('no_department') ?></option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>"><?= e($dept['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label"><?= __('role') ?> <span class="text-danger">*</span></label>
                        <select name="role" id="role" class="form-select" required>
                            <option value="employee"><?= __('role_employee') ?></option>
                            <option value="manager"><?= __('role_manager') ?></option>
                            <option value="admin"><?= __('role_admin') ?></option>
                        </select>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_manager" id="is_manager" class="form-check-input">
                        <label for="is_manager" class="form-check-label"><?= __('is_manager') ?></label>
                        <small class="form-text text-muted d-block"><?= __('is_manager_help') ?></small>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= $app->url('/admin/users') ?>" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> <?= __('back') ?>
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> <?= __('create_user') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
