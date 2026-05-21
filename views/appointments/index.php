<div class="d-flex justify-content-between mb-3">
    <h2>Управление записями</h2>
    <a href="<?= BASE_URL ?>/index.php?entity=appointment&action=create" class="btn btn-success">+ Запись</a>
</div>

<form method="get" class="row g-2 mb-4">
    <input type="hidden" name="entity" value="appointment">
    <input type="hidden" name="action" value="index">
    <div class="col-md-3">
        <select name="status" class="form-select">
            <option value="">Все статусы</option>
            <option value="pending" <?= $filters['status']=='pending'?'selected':'' ?>>Ожидание</option>
            <option value="confirmed" <?= $filters['status']=='confirmed'?'selected':'' ?>>Подтверждено</option>
            <option value="completed" <?= $filters['status']=='completed'?'selected':'' ?>>Завершено</option>
            <option value="cancelled" <?= $filters['status']=='cancelled'?'selected':'' ?>>Отменено</option>
        </select>
    </div>
    <div class="col-md-3">
        <select name="master_id" class="form-select">
            <option value="">Все мастера</option>
            <?php foreach ($masters as $m): ?>
                <option value="<?= $m['id'] ?>" <?= $filters['master_id']==$m['id']?'selected':'' ?>><?= h($m['last_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2"><button class="btn btn-primary w-100">Фильтр</button></div>
</form>

<table class="table table-hover">
    <thead><tr><th>Дата/Время</th><th>Клиент</th><th>Мастер</th><th>Услуга</th><th>Статус</th><th>Действия</th></tr></thead>
    <tbody>
        <?php foreach ($items as $item): 
            $statusClass = match($item['status']) {
                'confirmed' => 'bg-success', 'pending' => 'bg-warning text-dark', 'cancelled' => 'bg-danger', default => 'bg-secondary'
            };
        ?>
        <tr>
            <td><?= date('d.m H:i', strtotime($item['appointment_date'] . ' ' . $item['appointment_time'])) ?></td>
            <td><?= h($item['last_name']) ?> <?= h($item['first_name']) ?></td>
            <td><?= h($item['master_last_name']) ?></td>
            <td><?= h($item['service_name']) ?></td>
            <td><span class="badge <?= $statusClass ?>"><?= $item['status'] ?></span></td>
            <td>
                <a href="<?= BASE_URL ?>/index.php?entity=appointment&action=view&id=<?= $item['id'] ?>" class="btn btn-sm btn-info"></a>
                <!-- Кнопки быстрой смены статуса (AJAX) -->
                <?php if($item['status']=='pending'): ?>
                <button class="btn btn-sm btn-success status-btn" data-id="<?= $item['id'] ?>" data-status="confirmed">✔</button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>