<h2>Новая запись</h2>
<form method="post" class="needs-validation" novalidate>
    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
    <input type="hidden" name="entity" value="appointment">
    <input type="hidden" name="action" value="create">

    <div class="row g-3">
        <!-- Данные клиента (упрощенно) -->
        <div class="col-md-4">
            <label>Имя клиента</label>
            <input type="text" name="first_name" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label>Телефон</label>
            <input type="tel" name="phone" class="form-control" required>
        </div>

        <!-- Выбор услуги -->
        <div class="col-12">
            <label>Услуга</label>
            <select name="service_id" id="serviceSelect" class="form-select" required>
                <option value="">-- Выберите услугу --</option>
                <?php foreach ($services as $s): ?>
                    <option value="<?= $s['id'] ?>" data-duration="<?= $s['duration_minutes'] ?>"><?= h($s['name']) ?> (<?= $s['duration_minutes'] ?> мин)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Выбор мастера -->
        <div class="col-12">
            <label>Мастер</label>
            <select name="master_id" id="masterSelect" class="form-select" required disabled>
                <option value="">-- Сначала выберите услугу --</option>
                <!-- JS заполнит подходящих мастеров -->
            </select>
        </div>

        <!-- Дата -->
        <div class="col-md-6">
            <label>Дата</label>
            <input type="date" name="appointment_date" id="dateInput" class="form-control" min="<?= date('Y-m-d') ?>" required>
        </div>

        <!-- Слоты (Генерируются JS) -->
        <div class="col-12">
            <label>Доступное время</label>
            <div id="slotsContainer" class="d-flex flex-wrap gap-2 mt-2">
                <span class="text-muted">Выберите мастера и дату</span>
            </div>
            <input type="hidden" name="appointment_time" id="selectedTime" required>
        </div>
    </div>
    
    <button type="submit" class="btn btn-primary mt-4" id="submitBtn" disabled>Записаться</button>
</form>

<script src="<?= BASE_URL ?>/assets/js/booking.js"></script>
<script>
    // Передаем URL для AJAX в JS
    window.bookingConfig = {
        slotsUrl: '<?= BASE_URL ?>/ajax_slots.php',
        masters: <?= json_encode($masters) ?>
    };
</script>