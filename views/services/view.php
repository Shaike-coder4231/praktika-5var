<div class="card">
    <div class="card-header">Услуга: <?= h($service['name']) ?></div>
    <div class="card-body">
        <p class="lead"><?= formatPrice($service['price']) ?></p>
        <dl class="row">
            <dt class="col-sm-3">Длительность:</dt><dd class="col-sm-9"><?= $service['duration_minutes'] ?> мин</dd>
            <dt class="col-sm-3">Консультация:</dt><dd class="col-sm-9"><?= $service['requires_consultation'] ? 'Обязательна' : 'Не нужна' ?></dd>
            <dt class="col-sm-3">Необходимые продукты:</dt>
            <dd class="col-sm-9">
                <?php if (!empty($products)): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($products as $prod): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= h($prod['name']) ?>
                            <span class="badge bg-primary rounded-pill"><?= $prod['quantity_required'] ?> <?= h($prod['unit']) ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <span class="text-muted">Нет необходимых продуктов</span>
                <?php endif; ?>
            </dd>
        </dl>
    </div>
    <div class="card-footer"><a href="<?= BASE_URL ?>/index.php?entity=service&action=index" class="btn btn-secondary">Назад</a></div>
</div>