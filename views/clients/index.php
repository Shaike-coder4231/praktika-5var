<?php // views/clients/index.php ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Клиенты</h2>
    <a href="<?= BASE_URL ?>/index.php?entity=client&action=create" class="btn btn-success">+ Добавить</a>
</div>

<form method="get" class="row g-3 mb-4">
    <input type="hidden" name="entity" value="client">
    <input type="hidden" name="action" value="index">
    <div class="col-md-6">
        <input type="search" name="search" class="form-control" placeholder="Поиск..." value="<?= h($search) ?>">
    </div>
    <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Найти</button></div>
</form>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-light">
            <tr>
                <th><a href="?entity=client&action=index&sort=last_name&order=<?= $sort==='last_name'&&$order==='ASC'?'DESC':'ASC' ?>">Фамилия <?= $sort==='last_name'?($order==='ASC'?'↑':'↓'):'' ?></a></th>
                <th>Имя</th>
                <th>Телефон</th>
                <th>Email</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
            <tr><td colspan="5" class="text-center py-4">Нет записей</td></tr>
            <?php else: foreach ($items as $item): ?>
            <tr>
                <td><?= h($item['last_name']) ?></td>
                <td><?= h($item['first_name']) ?></td>
                <td><?= h($item['phone']) ?></td>
                <td><?= h($item['email'] ?? '') ?></td>
                <td>
                    <a href="<?= BASE_URL ?>/index.php?entity=client&action=view&id=<?= $item['id'] ?>" class="btn btn-sm btn-info text-white">👁</a>
                    <a href="<?= BASE_URL ?>/index.php?entity=client&action=edit&id=<?= $item['id'] ?>" class="btn btn-sm btn-warning">✏</a>
                    <a href="<?= BASE_URL ?>/index.php?entity=client&action=delete&id=<?= $item['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить?')">🗑</a>
                </td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>