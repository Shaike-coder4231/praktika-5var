<div class="card border-danger"><div class="card-header bg-danger text-white">Удаление</div><div class="card-body">
    <p>Удалить мастера: <strong><?= h($master['last_name']) ?> <?= h($master['first_name']) ?></strong>?</p>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
        <input type="hidden" name="entity" value="master">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" value="<?= $master['id'] ?>">
        <button type="submit" class="btn btn-danger">Да</button>
        <a href="<?= BASE_URL ?>/index.php?entity=master&action=index" class="btn btn-secondary">Отмена</a>
    </form>
</div></div>