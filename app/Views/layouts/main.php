<?php
use App\Core\Helpers;
$theme = Helpers::getUserTheme($user ?? null, null);
?>
<!DOCTYPE html>
<html lang="<?= App\Core\I18n::getLocale() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('app_name') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $app->asset('css/app.css') ?>">
    <link rel="stylesheet" href="<?= $app->asset('css/themes/theme-' . e($theme) . '.css') ?>">
</head>
<body class="theme-<?= e($theme) ?>">
    <?php require APP_PATH . '/Views/partials/nav.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $app->url('/dashboard') ?>">
                                <i class="bi bi-house-door"></i> <?= __('dashboard') ?>
                            </a>
                        </li>
                        <?php if ($auth->isAdmin()): ?>
                        <li class="nav-item mt-3">
                            <h6 class="sidebar-heading px-3 text-muted"><?= __('admin') ?></h6>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $app->url('/admin/users') ?>">
                                <i class="bi bi-person"></i> <?= __('users') ?>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <?php require APP_PATH . '/Views/partials/flash.php'; ?>

                <div class="py-4">
                    <?= $content ?>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $app->asset('js/app.js') ?>"></script>
</body>
</html>
