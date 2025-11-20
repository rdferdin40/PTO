<?php use App\Core\CSRF; ?>

<div class="card shadow">
    <div class="card-body p-5">
        <div class="text-center mb-4">
            <h1 class="h3">Set New Password</h1>
        </div>

        <form method="POST" action="<?= $app->url('/password/reset/' . e($token)) ?>">
            <?= CSRF::field() ?>

            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="password" name="password" required autofocus>
            </div>

            <div class="mb-3">
                <label for="password_confirm" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
        </form>
    </div>
</div>
