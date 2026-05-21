<h2><?= $action==='create'?'Новый мастер':'Редактировать мастера' ?></h2>
<form method="post" class="needs-validation" novalidate>
    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
    <input type="hidden" name="entity" value="master">
    <input type="hidden" name="action" value="<?= $action ?>">
    <?php if ($action==='edit' && isset($id)): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>

    <div class="row g-3">
        <div class="col-md-6"><label>Фамилия *</label><input type="text" name="last_name" class="form-control" value="<?= h($data['last_name'] ?? '') ?>" required></div>
        <div class="col-md-6"><label>Имя *</label><input type="text" name="first_name" class="form-control" value="<?= h($data['first_name'] ?? '') ?>" required></div>
        <div class="col-md-6"><label>Специализация</label><input type="text" name="specialization" class="form-control" value="<?= h($data['specialization'] ?? '') ?>"></div>
        <div class="col-md-6"><label>Телефон *</label><input type="tel" name="phone" class="form-control" value="<?= h($data['phone'] ?? '') ?>" required></div>
        <div class="col-md-6"><label>Email</label><input type="email" name="email" class="form-control" value="<?= h($data['email'] ?? '') ?>"></div>
        <div class="col-md-6"><label>Дата найма</label><input type="date" name="hire_date" class="form-control" value="<?= h($data['hire_date'] ?? '') ?>"></div>
        <div class="col-md-12"><div class="form-check"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" class="form-check-input" value="1" <?= ($data['is_active'] ?? 1) ? 'checked' : '' ?>><label class="form-check-label">Работает</label></div></div>
    </div>
    <div class="mt-4"><button type="submit" class="btn btn-primary">Сохранить</button></div>
</form>