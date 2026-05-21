<h2>Часто заказываемые вместе услуги</h2>
<?php if (empty($pairs)): ?>
    <div class="alert alert-info">Нет данных. Создайте несколько записей от одного клиента на разные услуги.</div>
<?php else: ?>
    <table class="table table-striped">
        <thead><tr><th>Услуга 1</th><th>Услуга 2</th><th>Количество сочетаний</th></tr></thead>
        <tbody>
            <?php foreach ($pairs as $row): ?>
            <tr>
                <td><?= h($row['service_1']) ?></td>
                <td><?= h($row['service_2']) ?></td>
                <td><span class="badge bg-primary"><?= $row['combination_count'] ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<a href="<?= BASE_URL ?>/index.php?entity=service&action=index" class="btn btn-secondary mt-3">← Назад к услугам</a>