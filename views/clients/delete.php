<div class="card border-danger">
    <div class="card-header bg-danger text-white">Удаление</div>
    <div class="card-body">
        <p>Удалить клиента: <strong><?= h($client['last_name']) ?> <?= h($client['first_name']) ?></strong>?</p>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <input type="hidden" name="entity" value="client">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $client['id'] ?>">
            <button type="submit" class="btn btn-danger">Да, удалить</button>
            <a href="<?= BASE_URL ?>/index.php?entity=client&action=index" class="btn btn-secondary">Отмена</a>
        </form>
    </div>
</div>