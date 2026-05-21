document.addEventListener('DOMContentLoaded', () => {
    const serviceSelect = document.getElementById('serviceSelect');
    const masterSelect = document.getElementById('masterSelect');
    const dateInput = document.getElementById('dateInput');
    const slotsContainer = document.getElementById('slotsContainer');
    const hiddenTime = document.getElementById('selectedTime');
    const submitBtn = document.getElementById('submitBtn');
    
    // 1. При выборе услуги фильтруем мастеров (простая логика: показываем всех активных, 
    // в сложной версии - проверять специализацию или таблицу связей)
    serviceSelect.addEventListener('change', () => {
        masterSelect.innerHTML = '<option value="">-- Выберите мастера --</option>';
        masterSelect.disabled = false;
        bookingConfig.masters.forEach(m => {
            let opt = document.createElement('option');
            opt.value = m.id;
            opt.textContent = m.last_name + ' ' + m.first_name;
            masterSelect.appendChild(opt);
        });
        loadSlots();
    });

    // 2. Загрузка слотов
    masterSelect.addEventListener('change', loadSlots);
    dateInput.addEventListener('change', loadSlots);

    function loadSlots() {
        const sid = serviceSelect.value;
        const mid = masterSelect.value;
        const date = dateInput.value;

        if (!sid || !mid || !date) {
            slotsContainer.innerHTML = '<span class="text-muted">Заполните все поля выше</span>';
            return;
        }

        slotsContainer.innerHTML = '<div class="spinner-border spinner-border-sm"></div>';

        fetch(`${bookingConfig.slotsUrl}?service_id=${sid}&master_id=${mid}&date=${date}`)
            .then(res => res.json())
            .then(data => {
                slotsContainer.innerHTML = '';
                if (data.slots.length === 0) {
                    slotsContainer.innerHTML = '<span class="text-danger">Нет свободных мест</span>';
                    submitBtn.disabled = true;
                    return;
                }

                data.slots.forEach(time => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'btn btn-outline-primary';
                    btn.textContent = time;
                    btn.onclick = () => {
                        document.querySelectorAll('#slotsContainer .btn').forEach(b => b.classList.remove('active'));
                        btn.classList.add('active', 'btn-primary');
                        hiddenTime.value = time;
                        submitBtn.disabled = false;
                    };
                    slotsContainer.appendChild(btn);
                });
            });
    }

    // AJAX для смены статуса в таблице
    document.querySelectorAll('.status-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('Подтвердить?')) return;
            const id = this.dataset.id;
            const status = this.dataset.status;
            
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `entity=appointment&action=changeStatus&id=${id}&status=${status}`
            }).then(() => location.reload());
        });
    });
});