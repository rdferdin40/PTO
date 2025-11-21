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

                        <!-- Employee Section -->
                        <li class="nav-item mt-3">
                            <h6 class="sidebar-heading px-3 text-muted"><?= __('my_leave_requests') ?></h6>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $app->url('/leaves') ?>">
                                <i class="bi bi-calendar-check"></i> <?= __('my_leave_requests') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $app->url('/leaves/new') ?>">
                                <i class="bi bi-plus-circle"></i> <?= __('request_leave') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $app->url('/leaves/calendar') ?>">
                                <i class="bi bi-calendar3"></i> <?= __('my_leave_calendar') ?>
                            </a>
                        </li>

                        <!-- Manager Section -->
                        <?php if ($auth->isManager()): ?>
                        <li class="nav-item mt-3">
                            <h6 class="sidebar-heading px-3 text-muted"><?= __('manager') ?></h6>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $app->url('/manager/approvals') ?>">
                                <i class="bi bi-clipboard-check"></i> <?= __('pending_approvals') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $app->url('/manager/team-calendar') ?>">
                                <i class="bi bi-calendar3"></i> <?= __('team_calendar') ?>
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Admin Section -->
                        <?php if ($auth->isAdmin()): ?>
                        <li class="nav-item mt-3">
                            <h6 class="sidebar-heading px-3 text-muted"><?= __('admin') ?></h6>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $app->url('/admin/users') ?>">
                                <i class="bi bi-people"></i> <?= __('users') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $app->url('/admin/departments') ?>">
                                <i class="bi bi-building"></i> <?= __('department_management') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $app->url('/admin/leave-types') ?>">
                                <i class="bi bi-tag"></i> <?= __('leave_type_management') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $app->url('/admin/holidays') ?>">
                                <i class="bi bi-calendar-event"></i> <?= __('holiday_management') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $app->url('/admin/allowances') ?>">
                                <i class="bi bi-calendar-range"></i> <?= __('allowance_management') ?>
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
