<?php use App\Core\CSRF; ?>

<div class="card shadow">
    <div class="card-body p-5">
        <div class="text-center mb-4">
            <h1 class="h3"><?= __('app_name') ?></h1>
            <p class="text-muted"><?= __('login_title') ?></p>
        </div>

        <form method="POST" action="<?= $app->url('/login') ?>">
            <?= CSRF::field() ?>

            <div class="mb-3">
                <label for="email" class="form-label"><?= __('email') ?></label>
                <input type="email" class="form-control" id="email" name="email" required autofocus>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label"><?= __('password') ?></label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100"><?= __('login') ?></button>
        </form>

        <div class="text-center mt-3">
            <a href="<?= $app->url('/password/request') ?>" class="text-muted small">
                <?= __('forgot_password') ?>
            </a>
        </div>
    </div>
</div>
