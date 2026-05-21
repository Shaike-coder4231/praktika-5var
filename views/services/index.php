<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Услуги</h2>
    <a href="<?= BASE_URL ?>/index.php?entity=service&action=create" class="btn btn-success">+ Добавить</a>
</div>
<div class="table-responsive">
    <table class="table table-striped">
        <thead><tr>
            <th>Название</th>
            <th>Цена</th>
            <th>Консультация</th>
            <th>Статус</th>
            <th>Действия</th>
        </tr></thead>
        <tbody>
        <?php foreach ($items as $item): ?>
        <tr>
            <td><?= h($item['name']) ?></td>
            <td><?= formatPrice($item['price']) ?></td>
            <td><?= $item['requires_consultation'] ? '<span class="badge bg-warning text-dark">Обязательно</span>' : '<span class="badge bg-light text-dark">Нет</span>' ?></td>
            <td><?= $item['is_active'] ? '<span class="badge bg-success">Активна</span>' : '<span class="badge bg-secondary">Скрыта</span>' ?></td>
            <td>
                <a href="<?= BASE_URL ?>/index.php?entity=service&action=view&id=<?= $item['id'] ?>" class="btn btn-sm btn-info text-white">👁</a>
                <a href="<?= BASE_URL ?>/index.php?entity=service&action=edit&id=<?= $item['id'] ?>" class="btn btn-sm btn-warning">✏</a>
                <a href="<?= BASE_URL ?>/index.php?entity=service&action=delete&id=<?= $item['id'] ?>" class="btn btn-sm btn-danger">🗑</a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>