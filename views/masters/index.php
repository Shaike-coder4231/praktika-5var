<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Мастера</h2>
    <a href="<?= BASE_URL ?>/index.php?entity=master&action=create" class="btn btn-success">+ Добавить</a>
</div>
<div class="table-responsive">
    <table class="table table-striped">
        <thead><tr>
            <th>Фамилия Имя</th>
            <th>Специализация</th>
            <th>Телефон</th>
            <th>Статус</th>
            <th>Действия</th>
        </tr></thead>
        <tbody>
        <?php foreach ($items as $item): ?>
        <tr>
            <td><?= h($item['last_name']) ?> <?= h($item['first_name']) ?></td>
            <td><?= h($item['specialization'] ?? '—') ?></td>
            <td><?= h($item['phone']) ?></td>
            <td><?= $item['is_active'] ? '<span class="badge bg-success">Работает</span>' : '<span class="badge bg-secondary">Уволен</span>' ?></td>
            <td>
                <a href="<?= BASE_URL ?>/index.php?entity=master&action=view&id=<?= $item['id'] ?>" class="btn btn-sm btn-info text-white">👁</a>
                <a href="<?= BASE_URL ?>/index.php?entity=master&action=edit&id=<?= $item['id'] ?>" class="btn btn-sm btn-warning">✏</a>
                <a href="<?= BASE_URL ?>/index.php?entity=master&action=delete&id=<?= $item['id'] ?>" class="btn btn-sm btn-danger">🗑</a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>