<div class="card">
    <div class="card-header">Профиль мастера</div>
    <div class="card-body">
        <h3><?= h($master['last_name']) ?> <?= h($master['first_name']) ?></h3>
        <dl class="row">
            <dt class="col-sm-3">Специализация:</dt><dd class="col-sm-9"><?= h($master['specialization'] ?? '—') ?></dd>
            <dt class="col-sm-3">Телефон:</dt><dd class="col-sm-9"><?= h($master['phone']) ?></dd>
            <dt class="col-sm-3">Будущих записей:</dt><dd class="col-sm-9"><?= $futureAppointmentsCount ?></dd>
        </dl>
    </div>
    <div class="card-footer"><a href="<?= BASE_URL ?>/index.php?entity=master&action=index" class="btn btn-secondary">Назад</a></div>
</div>