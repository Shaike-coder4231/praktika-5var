<div class="card">
    <div class="card-header">Карточка клиента</div>
    <div class="card-body">
        <h3><?= h($client['last_name']) ?> <?= h($client['first_name']) ?></h3>
        <dl class="row">
            <dt class="col-sm-3">Телефон:</dt><dd class="col-sm-9"><?= h($client['phone']) ?></dd>
            <dt class="col-sm-3">Email:</dt><dd class="col-sm-9"><?= h($client['email'] ?? '—') ?></dd>
            <dt class="col-sm-3">Всего записей:</dt><dd class="col-sm-9"><?= $appointmentsCount ?></dd>
        </dl>
    </div>
    <div class="card-footer">
        <a href="<?= BASE_URL ?>/index.php?entity=client&action=edit&id=<?= $client['id'] ?>" class="btn btn-sm btn-warning">Редактировать</a>
        <a href="<?= BASE_URL ?>/index.php?entity=client&action=index" class="btn btn-sm btn-secondary">Назад</a>
    </div>
</div>