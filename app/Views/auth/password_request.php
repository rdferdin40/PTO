<?php use App\Core\CSRF; ?>

<div class="card shadow">
    <div class="card-body p-5">
        <div class="text-center mb-4">
            <h1 class="h3"><?= __('forgot_password') ?></h1>
            <p class="text-muted">Enter your email to receive a reset link</p>
        </div>

        <form method="POST" action="<?= $app->url('/password/request') ?>">
            <?= CSRF::field() ?>

            <div class="mb-3">
                <label for="email" class="form-label"><?= __('email') ?></label>
                <input type="email" class="form-control" id="email" name="email" required autofocus>
            </div>

            <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
        </form>

        <div class="text-center mt-3">
            <a href="<?= $app->url('/login') ?>" class="text-muted small">
                Back to login
            </a>
        </div>
    </div>
</div>
