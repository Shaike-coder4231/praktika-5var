<?php
// public_html/index.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$pdo = Database::getInstance();
$entity = $_GET['entity'] ?? 'client';
$action = $_GET['action'] ?? 'index';

$controllers = [
    'client' => 'ClientController',
    'master' => 'MasterController', // Мастера вместо специалистов
    'service' => 'ServiceController',
];

if (!isset($controllers[$entity])) {
    addFlashMessage('error', 'Сущность не найдена');
    header('Location: ' . BASE_URL . '/index.php?entity=client');
    exit;
}

$controller = new $controllers[$entity]($pdo);
$allowed = ['index', 'create', 'edit', 'delete', 'view'];

if (in_array($action, $allowed) && method_exists($controller, $action)) {
    $controller->$action();
} else {
    addFlashMessage('error', 'Действие не найдено');
    $controller->index();
}