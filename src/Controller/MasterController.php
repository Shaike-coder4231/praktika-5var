<?php
class MasterController extends BaseController {
    private MasterRepository $repo;
    
    public function __construct(PDO $pdo) {
        parent::__construct($pdo);
        $this->repo = new MasterRepository($pdo);
    }
    
    public function index(): void {
        $page = max(1, (int) ($this->getParam('page') ?? 1));
        $search = trim($this->getParam('search') ?? '');
        $sort = $this->getParam('sort') ?? 'last_name';
        $order = strtoupper($this->getParam('order') ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
        $offset = ($page - 1) * ITEMS_PER_PAGE;
        
        $items = $search ? $this->repo->search($search, ITEMS_PER_PAGE, $offset) 
                        : $this->repo->findAll([$sort => $order], ITEMS_PER_PAGE, $offset);
        $total = $search ? count($items) : $this->repo->count();
        $pages = ceil($total / ITEMS_PER_PAGE);
        
        $this->render('masters/index', [
            'items' => $items, 'total' => $total, 'page' => $page, 'pages' => $pages,
            'search' => $search, 'sort' => $sort, 'order' => $order
        ]);
    }
    
    public function create(): void {
        if ($this->isPost()) {
            if (!verifyCsrfToken($this->getParam('csrf_token') ?? '')) {
                addFlashMessage('error', 'Ошибка безопасности');
                $this->redirect('index.php', ['entity' => 'master', 'action' => 'create']);
            }
            $errors = $this->validateMasterData($_POST);
            if (empty($errors)) {
                $data = [
                    'first_name' => trim($this->getParam('first_name') ?? ''),
                    'last_name' => trim($this->getParam('last_name') ?? ''),
                    'middle_name' => trim($this->getParam('middle_name') ?? ''),
                    'phone' => preg_replace('/[\s\-\(\)]/', '', $this->getParam('phone') ?? ''),
                    'email' => trim($this->getParam('email') ?? ''),
                    'specialization' => trim($this->getParam('specialization') ?? ''),
                    'hire_date' => $this->getParam('hire_date') ?: null,
                    'is_active' => !empty($this->getParam('is_active'))
                ];
                $this->repo->create($data);
                addFlashMessage('success', 'Мастер добавлен');
                $this->redirect('index.php', ['entity' => 'master', 'action' => 'index']);
            } else {
                addFlashMessage('error', 'Исправьте ошибки');
                $this->render('masters/form', ['data' => $_POST, 'errors' => $errors, 'action' => 'create']);
                return;
            }
        }
        $this->render('masters/form', ['data' => [], 'errors' => [], 'action' => 'create']);
    }
    
    public function edit(): void {
        $id = $this->validateId($this->getParam('id'));
        if (!$id || !($master = $this->repo->findById($id))) {
            $this->redirect('index.php', ['entity' => 'master', 'action' => 'index']);
        }
        if ($this->isPost()) {
            if (!verifyCsrfToken($this->getParam('csrf_token') ?? '')) {
                $this->redirect('index.php', ['entity' => 'master', 'action' => 'edit', 'id' => $id]);
            }
            $errors = $this->validateMasterData($_POST);
            if (empty($errors)) {
                $data = [
                    'first_name' => trim($this->getParam('first_name') ?? ''),
                    'last_name' => trim($this->getParam('last_name') ?? ''),
                    'middle_name' => trim($this->getParam('middle_name') ?? ''),
                    'phone' => preg_replace('/[\s\-\(\)]/', '', $this->getParam('phone') ?? ''),
                    'email' => trim($this->getParam('email') ?? ''),
                    'specialization' => trim($this->getParam('specialization') ?? ''),
                    'hire_date' => $this->getParam('hire_date') ?: null,
                    'is_active' => !empty($this->getParam('is_active'))
                ];
                $this->repo->update($id, $data);
                addFlashMessage('success', 'Данные обновлены');
                $this->redirect('index.php', ['entity' => 'master', 'action' => 'index']);
            } else {
                addFlashMessage('error', 'Исправьте ошибки');
                $this->render('masters/form', ['data' => array_merge($master, $_POST), 'errors' => $errors, 'action' => 'edit', 'id' => $id]);
                return;
            }
        }
        $this->render('masters/form', ['data' => $master, 'errors' => [], 'action' => 'edit', 'id' => $id]);
    }
    
    public function delete(): void {
        $id = $this->validateId($this->getParam('id'));
        if (!$id || !($master = $this->repo->findById($id))) {
            $this->redirect('index.php', ['entity' => 'master', 'action' => 'index']);
        }
        if ($this->isPost()) {
            if (!verifyCsrfToken($this->getParam('csrf_token') ?? '')) {
                $this->redirect('index.php', ['entity' => 'master', 'action' => 'index']);
            }
            if ($this->repo->hasFutureAppointments($id)) {
                addFlashMessage('error', 'Нельзя удалить: у мастера есть будущие записи');
                $this->redirect('index.php', ['entity' => 'master', 'action' => 'index']);
            }
            $this->repo->delete($id);
            addFlashMessage('success', 'Мастер удалён');
            $this->redirect('index.php', ['entity' => 'master', 'action' => 'index']);
        }
        $this->render('masters/delete', ['master' => $master]);
    }
    
    public function view(): void {
        $id = $this->validateId($this->getParam('id'));
        if (!$id || !($master = $this->repo->findById($id))) {
            $this->redirect('index.php', ['entity' => 'master', 'action' => 'index']);
        }
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as cnt FROM appointments WHERE master_id = :id AND appointment_date >= CURDATE()");
        $stmt->execute(['id' => $id]);
        $futureCount = (int) $stmt->fetch()['cnt'];
        $this->render('masters/view', ['master' => $master, 'futureAppointmentsCount' => $futureCount]);
    }
    
    private function validateMasterData(array $data): array {
        $errors = [];
        if (empty(trim($data['last_name'] ?? ''))) $errors['last_name'] = 'Фамилия обязательна';
        if (empty(trim($data['first_name'] ?? ''))) $errors['first_name'] = 'Имя обязательно';
        $phone = preg_replace('/[\s\-\(\)]/', '', $data['phone'] ?? '');
        if (!isValidPhone($phone)) $errors['phone'] = 'Неверный телефон';
        $email = trim($data['email'] ?? '');
        if ($email && !isValidEmail($email)) $errors['email'] = 'Неверный email';
        return $errors;
    }
}