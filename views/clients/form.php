<?php // views/clients/form.php ?>
<h2><?= $action==='create'?'Новый клиент':'Редактировать клиента' ?></h2>
<form method="post" class="needs-validation" novalidate>
    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
    <input type="hidden" name="entity" value="client">
    <input type="hidden" name="action" value="<?= $action ?>">
    <?php if ($action==='edit' && isset($id)): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Фамилия *</label>
            <input type="text" name="last_name" class="form-control <?= isset($errors['last_name'])?'is-invalid':'' ?>" value="<?= h($data['last_name'] ?? '') ?>" required>
            <div class="invalid-feedback"><?= $errors['last_name'] ?? '' ?></div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Имя *</label>
            <input type="text" name="first_name" class="form-control <?= isset($errors['first_name'])?'is-invalid':'' ?>" value="<?= h($data['first_name'] ?? '') ?>" required>
            <div class="invalid-feedback"><?= $errors['first_name'] ?? '' ?></div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Телефон *</label>
            <input type="tel" name="phone" class="form-control <?= isset($errors['phone'])?'is-invalid':'' ?>" value="<?= h($data['phone'] ?? '') ?>" required>
            <div class="invalid-feedback"><?= $errors['phone'] ?? '' ?></div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= h($data['email'] ?? '') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Дата рождения</label>
            <input type="date" name="birth_date" class="form-control" value="<?= h($data['birth_date'] ?? '') ?>">
        </div>
    </div>
    <div class="mt-4">
        <button type="submit" class="btn btn-primary"><?= $action==='create'?'Создать':'Сохранить' ?></button>
        <a href="<?= BASE_URL ?>/index.php?entity=client&action=index" class="btn btn-secondary">Отмена</a>
    </div>
</form>