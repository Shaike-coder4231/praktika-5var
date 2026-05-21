<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <style>
        body { background: #f8f9fa; }
        .table th a { color: inherit; text-decoration: none; }
        .table th a:hover { text-decoration: underline; }
        .btn-sm { padding: .25rem .5rem; font-size: .875rem; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">‍♀️ Салон красоты</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/index.php?entity=client&action=index">Клиенты</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/index.php?entity=master&action=index">Мастера</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/index.php?entity=service&action=index">Услуги</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/index.php?entity=service&action=reportPairs">📊 Отчёт по парам</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container">
    <?php foreach (getFlashMessages() as $msg): ?>
        <div class="alert alert-<?= $msg['type']==='success'?'success':'danger' ?> alert-dismissible fade show">
            <?= h($msg['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endforeach; ?>