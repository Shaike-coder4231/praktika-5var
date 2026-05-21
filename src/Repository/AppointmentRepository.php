<?php
class AppointmentRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Получить слоты для мастера на дату с учетом длительности услуги
    public function getAvailableSlots(int $masterId, string $date, int $serviceDuration): array {
        // 1. Получаем график работы мастера на этот день недели
        $dayOfWeek = date('N', strtotime($date)); // 1=Mon, 7=Sun
        
        $stmt = $this->pdo->prepare("SELECT start_time, end_time FROM working_hours WHERE master_id = :mid AND day_of_week = :day ORDER BY start_time");
        $stmt->execute(['mid' => $masterId, 'day' => $dayOfWeek]);
        $intervals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($intervals)) return []; // Выходной

        // 2. Получаем занятые слоты
        $stmt = $this->pdo->prepare("SELECT TIME(appointment_time) as time, duration_minutes FROM appointments 
                                     WHERE master_id = :mid AND appointment_date = :date AND status != 'cancelled'");
        $stmt->execute(['mid' => $masterId, 'date' => $date]);
        $busySlots = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $availableSlots = [];
        
        // 3. Генерируем слоты каждые 15 минут в рамках рабочих интервалов
        foreach ($intervals as $interval) {
            $current = strtotime($interval['start_time']);
            $end = strtotime($interval['end_time']);

            while ($current < $end) {
                $slotTime = date('H:i', $current);
                $slotEnd = date('H:i', $current + ($serviceDuration * 60));

                // Проверяем, не выходит ли за конец рабочего времени
                if (strtotime($slotEnd) > $end) break;

                // Проверяем пересечение с занятыми слотами
                $isBusy = false;
                foreach ($busySlots as $busy) {
                    $busyStart = strtotime($busy['time']);
                    $busyEnd = $busyStart + ($busy['duration_minutes'] * 60);
                    
                    // Логика пересечения интервалов:
                    if ($current < $busyEnd && ($current + $serviceDuration * 60) > $busyStart) {
                        $isBusy = true;
                        break;
                    }
                }

                if (!$isBusy) {
                    $availableSlots[] = $slotTime;
                }
                
                // Шаг 15 минут
                $current += 15 * 60;
            }
        }

        return $availableSlots;
    }

    // Создать запись (с проверкой занятости)
    public function create(array $data): int {
        $this->pdo->beginTransaction();
        try {
            // 1. Проверка занятости (конкурентная защита)
            $stmt = $this->pdo->prepare("SELECT id FROM appointments WHERE master_id = :mid AND appointment_date = :date AND appointment_time = :time AND status != 'cancelled'");
            $stmt->execute([
                'mid' => $data['master_id'], 
                'date' => $data['appointment_date'], 
                'time' => $data['appointment_time']
            ]);
            
            if ($stmt->fetch()) {
                throw new Exception("Это время уже занято!");
            }

            // 2. Генерация кода
            $data['booking_code'] = strtoupper(bin2hex(random_bytes(4)));

            // 3. Вставка
            $stmt = $this->pdo->prepare("INSERT INTO appointments (client_id, master_id, service_id, appointment_date, appointment_time, duration_minutes, status, booking_code, notes) 
                                         VALUES (:cid, :mid, :sid, :date, :time, :dur, 'pending', :code, :notes)");
            $stmt->execute([
                'cid' => $data['client_id'],
                'mid' => $data['master_id'],
                'sid' => $data['service_id'],
                'date' => $data['appointment_date'],
                'time' => $data['appointment_time'],
                'dur' => $data['duration_minutes'],
                'code' => $data['booking_code'],
                'notes' => $data['notes'] ?? null
            ]);

            $appointmentId = (int)$this->pdo->lastInsertId();

            // 4. Логирование
            $this->logAction($appointmentId, 'created', "Создана запись на {$data['appointment_date']} {$data['appointment_time']}");

            $this->pdo->commit();
            return $appointmentId;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function logAction(int $appointmentId, string $action, string $details): void {
        $stmt = $this->pdo->prepare("INSERT INTO appointment_logs (appointment_id, action, details) VALUES (:aid, :action, :details)");
        $stmt->execute(['aid' => $appointmentId, 'action' => $action, 'details' => $details]);
    }

    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT a.*, c.first_name, c.last_name, c.phone, s.name as service_name, m.first_name as master_name, m.last_name as master_last_name 
                                     FROM appointments a 
                                     JOIN clients c ON a.client_id = c.id 
                                     JOIN services s ON a.service_id = s.id 
                                     JOIN masters m ON a.master_id = m.id 
                                     WHERE a.id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function getList(array $filters = [], int $limit = 20, int $offset = 0): array {
        $sql = "SELECT a.*, c.first_name, c.last_name, s.name as service_name, m.first_name as master_name, m.last_name as master_last_name 
                FROM appointments a 
                JOIN clients c ON a.client_id = c.id 
                JOIN services s ON a.service_id = s.id 
                JOIN masters m ON a.master_id = m.id 
                WHERE 1=1";
        
        $params = [];
        if (!empty($filters['status'])) {
            $sql .= " AND a.status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND a.appointment_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['master_id'])) {
            $sql .= " AND a.master_id = :master_id";
            $params['master_id'] = $filters['master_id'];
        }

        $sql .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $newStatus, string $reason = ''): bool {
        $this->pdo->beginTransaction();
        try {
            // Проверка логики переходов
            $current = $this->findById($id);
            $allowedTransitions = [
                'pending' => ['confirmed', 'cancelled'],
                'confirmed' => ['completed', 'cancelled'],
                'completed' => [],
                'cancelled' => []
            ];

            if (!in_array($newStatus, $allowedTransitions[$current['status']] ?? [])) {
                throw new Exception("Недопустимый переход статуса");
            }

            $stmt = $this->pdo->prepare("UPDATE appointments SET status = :status, cancel_reason = :reason WHERE id = :id");
            $stmt->execute(['status' => $newStatus, 'reason' => $reason, 'id' => $id]);
            
            $this->logAction($id, 'status_changed', "Статус изменен на $newStatus" . ($reason ? ". Причина: $reason" : ""));
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function reschedule(int $id, string $newDate, string $newTime): bool {
        $appt = $this->findById($id);
        if (!$appt) throw new Exception("Запись не найдена");
        
        // Проверка доступности нового слота (рекурсивный вызов логики)
        $slots = $this->getAvailableSlots($appt['master_id'], $newDate, $appt['duration_minutes']);
        if (!in_array($newTime, $slots)) {
            throw new Exception("Выбранное время недоступно");
        }

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("UPDATE appointments SET appointment_date = :date, appointment_time = :time WHERE id = :id");
            $stmt->execute(['date' => $newDate, 'time' => $newTime, 'id' => $id]);
            
            $this->logAction($id, 'rescheduled', "Перенесено на $newDate $newTime");
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    // Отчеты
    public function getReportDailyRevenue(string $month): array {
        $stmt = $this->pdo->prepare("SELECT 
            DATE(appointment_date) as day, 
            COUNT(*) as count, 
            SUM(s.price) as revenue
            FROM appointments a
            JOIN services s ON a.service_id = s.id
            WHERE MONTH(appointment_date) = :month AND status = 'completed'
            GROUP BY day
            ORDER BY day");
        $stmt->execute(['month' => $month]);
        return $stmt->fetchAll();
    }

    public function getReportMasterWorkload(string $month): array {
        $stmt = $this->pdo->prepare("SELECT 
            CONCAT(m.last_name, ' ', m.first_name) as master, 
            COUNT(*) as total_appts,
            SUM(s.price) as total_revenue
            FROM appointments a
            JOIN masters m ON a.master_id = m.id
            JOIN services s ON a.service_id = s.id
            WHERE MONTH(a.appointment_date) = :month
            GROUP BY m.id
            ORDER BY total_appts DESC");
        $stmt->execute(['month' => $month]);
        return $stmt->fetchAll();
    }
}