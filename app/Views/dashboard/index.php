<h1 class="mb-4"><?= __('dashboard') ?></h1>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?= __('allowance_summary') ?></h5>
                <div class="row text-center mt-3">
                    <div class="col-4">
                        <div class="h2 text-primary"><?= number_format($allowance['entitled'], 1) ?></div>
                        <small class="text-muted"><?= __('entitled') ?></small>
                    </div>
                    <div class="col-4">
                        <div class="h2 text-danger"><?= number_format($allowance['used'], 1) ?></div>
                        <small class="text-muted"><?= __('used') ?></small>
                    </div>
                    <div class="col-4">
                        <div class="h2 text-success"><?= number_format($allowance['remaining'], 1) ?></div>
                        <small class="text-muted"><?= __('remaining') ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Welcome to PTO Manager!</h5>
                <p>This is your dashboard. Use the sidebar to navigate.</p>
                <?php if ($nextLeave): ?>
                    <hr>
                    <h6>Next Leave:</h6>
                    <p>
                        <?= e($nextLeave['leave_type_name']) ?>: 
                        <?= date('M d', strtotime($nextLeave['start_date'])) ?> - 
                        <?= date('M d, Y', strtotime($nextLeave['end_date'])) ?>
                        (<?= $nextLeave['days'] ?> days)
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
