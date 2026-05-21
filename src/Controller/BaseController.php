<?php
abstract class BaseController {
    protected PDO $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    protected function render(string $view, array $data = []): void {
        extract($data, EXTR_SKIP);
        include __DIR__ . "/../../views/layouts/header.php";
        include __DIR__ . "/../../views/$view.php";
        include __DIR__ . "/../../views/layouts/footer.php";
    }
    
    protected function redirect(string $url, array $params = []): void {
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        if (strpos($url, 'http') !== 0) {
            $url = BASE_URL . '/' . ltrim($url, '/');
        }
        header("Location: $url");
        exit;
    }
    
    protected function getParam(string $name, $default = null) {
        return $_GET[$name] ?? $_POST[$name] ?? $default;
    }
    
    protected function isPost(): bool {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    protected function validateId(?string $id): ?int {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        return ($id && $id > 0) ? $id : null;
    }
}