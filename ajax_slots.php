<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json');

$masterId = filter_input(INPUT_GET, 'master_id', FILTER_VALIDATE_INT);
$date = filter_input(INPUT_GET, 'date', FILTER_SANITIZE_STRING);
$serviceId = filter_input(INPUT_GET, 'service_id', FILTER_VALIDATE_INT);

if (!$masterId || !$date || !$serviceId) {
    echo json_encode(['error' => 'Invalid params']);
    exit;
}

$pdo = Database::getInstance();
$repo = new AppointmentRepository($pdo);
$serviceRepo = new ServiceRepository($pdo);

$service = $serviceRepo->findById($serviceId);
$slots = $repo->getAvailableSlots($masterId, $date, (int)$service['duration_minutes']);

echo json_encode(['slots' => $slots]);