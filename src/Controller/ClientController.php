<?php
class ClientController extends BaseController {
    private ClientRepository $repo;
    
    public function __construct(PDO $pdo) {
        parent::__construct($pdo);
        $this->repo = new ClientRepository($pdo);
    }
    
    public function index(): void {
        $page = max(1, (int) ($this->getParam('page') ?? 1));
        $search = trim($this->getParam('search') ?? '');
        $sort = $this->getParam('sort') ?? 'last_name';
        $order = strtoupper($this->getParam('order') ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
        
        $offset = ($page - 1) * ITEMS_PER_PAGE;
        
        if ($search) {
            $items = $this->repo->search($search, ITEMS_PER_PAGE, $offset);
            $total = count($items);
        } else {
            $items = $this->repo->findAll([$sort => $order], ITEMS_PER_PAGE, $offset);
            $total = $this->repo->count();
        }
        
        $pages = ceil($total / ITEMS_PER_PAGE);
        
        $this->render('clients/index', [
            'items' => $items, 'total' => $total, 'page' => $page, 'pages' => $pages,
            'search' => $search, 'sort' => $sort, 'order' => $order
        ]);
    }
    
    public function create(): void {
        if ($this->isPost()) {
            if (!verifyCsrfToken($this->getParam('csrf_token') ?? '')) {
                addFlashMessage('error', 'Ошибка безопасности');
                $this->redirect('index.php', ['entity' => 'client', 'action' => 'create']);
            }
            
            $errors = $this->validateClientData($_POST);
            
            if (empty($errors)) {
                $data = [
                    'first_name' => trim($this->getParam('first_name') ?? ''),
                    'last_name' => trim($this->getParam('last_name') ?? ''),
                    'middle_name' => trim($this->getParam('middle_name') ?? ''),
                    'phone' => preg_replace('/[\s\-\(\)]/', '', $this->getParam('phone') ?? ''),
                    'email' => trim($this->getParam('email') ?? ''),
                    'birth_date' => $this->getParam('birth_date') ?: null
                ];
                
                $this->repo->create($data);
                addFlashMessage('success', 'Клиент успешно добавлен');
                $this->redirect('index.php', ['entity' => 'client', 'action' => 'index']);
            } else {
                addFlashMessage('error', 'Исправьте ошибки в форме');
                $this->render('clients/form', ['data' => $_POST, 'errors' => $errors, 'action' => 'create']);
                return;
            }
        }
        $this->render('clients/form', ['data' => [], 'errors' => [], 'action' => 'create']);
    }
    
    public function edit(): void {
        $id = $this->validateId($this->getParam('id'));
        if (!$id) {
            addFlashMessage('error', 'Неверный идентификатор');
            $this->redirect('index.php', ['entity' => 'client', 'action' => 'index']);
        }
        
        $client = $this->repo->findById($id);
        if (!$client) {
            addFlashMessage('error', 'Клиент не найден');
            $this->redirect('index.php', ['entity' => 'client', 'action' => 'index']);
        }
        
        if ($this->isPost()) {
            if (!verifyCsrfToken($this->getParam('csrf_token') ?? '')) {
                addFlashMessage('error', 'Ошибка безопасности');
                $this->redirect('index.php', ['entity' => 'client', 'action' => 'edit', 'id' => $id]);
            }
            
            $errors = $this->validateClientData($_POST);
            if (empty($errors)) {
                $data = [
                    'first_name' => trim($this->getParam('first_name') ?? ''),
                    'last_name' => trim($this->getParam('last_name') ?? ''),
                    'middle_name' => trim($this->getParam('middle_name') ?? ''),
                    'phone' => preg_replace('/[\s\-\(\)]/', '', $this->getParam('phone') ?? ''),
                    'email' => trim($this->getParam('email') ?? ''),
                    'birth_date' => $this->getParam('birth_date') ?: null
                ];
                $this->repo->update($id, $data);
                addFlashMessage('success', 'Данные клиента обновлены');
                $this->redirect('index.php', ['entity' => 'client', 'action' => 'index']);
            } else {
                addFlashMessage('error', 'Исправьте ошибки');
                $this->render('clients/form', ['data' => array_merge($client, $_POST), 'errors' => $errors, 'action' => 'edit', 'id' => $id]);
                return;
            }
        }
        $this->render('clients/form', ['data' => $client, 'errors' => [], 'action' => 'edit', 'id' => $id]);
    }
    
    public function delete(): void {
        $id = $this->validateId($this->getParam('id'));
        if (!$id || !($client = $this->repo->findById($id))) {
            addFlashMessage('error', 'Клиент не найден');
            $this->redirect('index.php', ['entity' => 'client', 'action' => 'index']);
        }
        
        if ($this->isPost()) {
            if (!verifyCsrfToken($this->getParam('csrf_token') ?? '')) {
                addFlashMessage('error', 'Ошибка безопасности');
                $this->redirect('index.php', ['entity' => 'client', 'action' => 'index']);
            }
            if ($this->repo->hasAppointments($id)) {
                addFlashMessage('error', 'Нельзя удалить: у клиента есть записи');
                $this->redirect('index.php', ['entity' => 'client', 'action' => 'index']);
            }
            $this->repo->delete($id);
            addFlashMessage('success', 'Клиент удалён');
            $this->redirect('index.php', ['entity' => 'client', 'action' => 'index']);
        }
        $this->render('clients/delete', ['client' => $client]);
    }
    
    public function view(): void {
        $id = $this->validateId($this->getParam('id'));
        if (!$id || !($client = $this->repo->findById($id))) {
            addFlashMessage('error', 'Клиент не найден');
            $this->redirect('index.php', ['entity' => 'client', 'action' => 'index']);
        }
        
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as cnt FROM appointments WHERE client_id = :id");
        $stmt->execute(['id' => $id]);
        $appointmentsCount = (int) $stmt->fetch()['cnt'];
        
        $this->render('clients/view', ['client' => $client, 'appointmentsCount' => $appointmentsCount]);
    }
    
    private function validateClientData(array $data): array {
        $errors = [];
        if (empty(trim($data['last_name'] ?? ''))) $errors['last_name'] = 'Фамилия обязательна';
        if (empty(trim($data['first_name'] ?? ''))) $errors['first_name'] = 'Имя обязательно';
        
        $phone = preg_replace('/[\s\-\(\)]/', '', $data['phone'] ?? '');
        if (!isValidPhone($phone)) $errors['phone'] = 'Неверный формат телефона (+79161234567)';
        
        $email = trim($data['email'] ?? '');
        if ($email && !isValidEmail($email)) $errors['email'] = 'Неверный email';
        
        return $errors;
    }
}