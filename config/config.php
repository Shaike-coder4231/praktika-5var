<?php
// config/config.php

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$script = dirname($_SERVER['PHP_SELF']);
$script = rtrim($script, '/\\');
define('BASE_URL', $protocol . '://' . $host . $script);

define('APP_NAME', 'Салон красоты - Онлайн запись');
define('ITEMS_PER_PAGE', 10);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// === ПОДКЛЮЧЕНИЕ КЛАССОВ ===
$rootDir = dirname(__DIR__); 

require_once $rootDir . '/src/Repository/BaseRepository.php';
require_once $rootDir . '/src/Repository/CategoryRepository.php';
require_once $rootDir . '/src/Repository/ClientRepository.php';
require_once $rootDir . '/src/Repository/MasterRepository.php';
require_once $rootDir . '/src/Repository/ServiceRepository.php';
require_once $rootDir . '/src/Repository/ProductRepository.php';
require_once $rootDir . '/src/Repository/AdditionalServiceRepository.php';

require_once $rootDir . '/src/Controller/BaseController.php';
require_once $rootDir . '/src/Controller/ClientController.php';
require_once $rootDir . '/src/Controller/MasterController.php';
require_once $rootDir . '/src/Controller/ServiceController.php';

function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function addFlashMessage(string $type, string $message): void {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][] = ['type' => $type, 'message' => $message];
}

function getFlashMessages(): array {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

function h(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function isValidPhone(string $phone): bool {
    $clean = preg_replace('/[\s\-\(\)]/', '', $phone);
    return preg_match('/^\+?7\d{10}$/', $clean) === 1;
}

function formatPrice(float $price): string {
    return number_format($price, 2, '.', ' ') . ' ₽';
}

function formatDate(?string $date): string {
    return $date ? date('d.m.Y', strtotime($date)) : '';
}