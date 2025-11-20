<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= $app->url('/dashboard') ?>">
            <?= __('app_name') ?>
        </a>

        <?php if ($auth->check()): ?>
        <div class="d-flex align-items-center">
            <span class="text-light me-3">
                <?= e($user['first_name'] ?? '') ?> <?= e($user['last_name'] ?? '') ?>
            </span>
            <a href="<?= $app->url('/logout') ?>" class="btn btn-sm btn-outline-light">
                <?= __('logout') ?>
            </a>
        </div>
        <?php endif; ?>
    </div>
</nav>
