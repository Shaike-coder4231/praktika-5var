<div class="card border-danger"><div class="card-header bg-danger text-white">Удаление</div><div class="card-body">
    <p>Удалить услугу: <strong><?= h($service['name']) ?></strong>?</p>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
        <input type="hidden" name="entity" value="service">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" value="<?= $service['id'] ?>">
        <button type="submit" class="btn btn-danger">Да</button>
        <a href="<?= BASE_URL ?>/index.php?entity=service&action=index" class="btn btn-secondary">Отмена</a>
    </form>
</div></div>