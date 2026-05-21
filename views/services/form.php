<h2><?= $action==='create'?'Новая услуга':'Редактировать услугу' ?></h2>
<form method="post" class="needs-validation" novalidate>
    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
    <input type="hidden" name="entity" value="service">
    <input type="hidden" name="action" value="<?= $action ?>">
    <?php if ($action==='edit' && isset($id)): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>

    <div class="row g-3">
        <div class="col-12"><label>Название *</label><input type="text" name="name" class="form-control" value="<?= h($data['name'] ?? '') ?>" required></div>
        <div class="col-md-4"><label>Цена *</label><input type="number" name="price" step="0.01" class="form-control" value="<?= h($data['price'] ?? '') ?>" required></div>
        <div class="col-md-4"><label>Длительность (мин) *</label><input type="number" name="duration_minutes" class="form-control" value="<?= h($data['duration_minutes'] ?? '') ?>" required></div>
        <div class="col-md-4">
            <label>Категория</label>
            <select name="category_id" class="form-select">
                <option value="">— Выберите —</option>
                <?php foreach ($categories as $cat): ?><option value="<?= $cat['id'] ?>" <?= ($data['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= h($cat['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-12"><div class="form-check"><input type="hidden" name="requires_consultation" value="0"><input type="checkbox" name="requires_consultation" class="form-check-input" value="1" <?= ($data['requires_consultation'] ?? 0) ? 'checked' : '' ?>><label class="form-check-label text-danger">Требует обязательной консультации</label></div></div>
        <div class="col-12"><div class="form-check"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" class="form-check-input" value="1" <?= ($data['is_active'] ?? 1) ? 'checked' : '' ?>><label class="form-check-label">Активна</label></div></div>
    </div>
    <div class="mt-4"><button type="submit" class="btn btn-primary">Сохранить</button></div>
</form>