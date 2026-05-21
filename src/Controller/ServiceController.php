<?php
class ServiceController extends BaseController {
    private ServiceRepository $repo;
    private CategoryRepository $catRepo;
    private ProductRepository $prodRepo;
    
    public function __construct(PDO $pdo) {
        parent::__construct($pdo);
        $this->repo = new ServiceRepository($pdo);
        $this->catRepo = new CategoryRepository($pdo);
        $this->prodRepo = new ProductRepository($pdo);
    }
    public function reportPairs(): void {
    $sql = "SELECT 
                s1.name AS service_1,
                s2.name AS service_2,
                COUNT(*) as combination_count
            FROM appointments a1
            INNER JOIN appointments a2 
                ON a1.client_id = a2.client_id 
                AND a1.id < a2.id
                AND a1.service_id != a2.service_id
            INNER JOIN services s1 ON a1.service_id = s1.id
            INNER JOIN services s2 ON a2.service_id = s2.id
            GROUP BY s1.id, s2.id
            HAVING combination_count >= 2
            ORDER BY combination_count DESC";
            
    $stmt = $this->pdo->query($sql);
    $pairs = $stmt->fetchAll();
    $this->render('services/report_pairs', ['pairs' => $pairs]);
}
    
    public function index(): void {
        $page = max(1, (int) ($this->getParam('page') ?? 1));
        $search = trim($this->getParam('search') ?? '');
        $sort = $this->getParam('sort') ?? 'name';
        $order = strtoupper($this->getParam('order') ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
        $offset = ($page - 1) * ITEMS_PER_PAGE;
        
        $items = $search ? $this->repo->search($search, ITEMS_PER_PAGE, $offset) 
                        : $this->repo->findAll([$sort => $order], ITEMS_PER_PAGE, $offset);
        $total = $search ? count($items) : $this->repo->count();
        $pages = ceil($total / ITEMS_PER_PAGE);
        
        $this->render('services/index', [
            'items' => $items, 'total' => $total, 'page' => $page, 'pages' => $pages,
            'search' => $search, 'sort' => $sort, 'order' => $order,
            'categories' => $this->catRepo->getAllOrdered()
        ]);
    }
    
    public function create(): void {
        if ($this->isPost()) {
            if (!verifyCsrfToken($this->getParam('csrf_token') ?? '')) {
                addFlashMessage('error', 'Ошибка безопасности');
                $this->redirect('index.php', ['entity' => 'service', 'action' => 'create']);
            }
            $errors = $this->validateServiceData($_POST);
            if (empty($errors)) {
                $data = [
                    'name' => trim($this->getParam('name') ?? ''),
                    'description' => trim($this->getParam('description') ?? ''),
                    'price' => (float) ($this->getParam('price') ?? 0),
                    'duration_minutes' => (int) ($this->getParam('duration_minutes') ?? 0),
                    'category_id' => $this->getParam('category_id') ?: null,
                    'requires_consultation' => !empty($this->getParam('requires_consultation')),
                    'is_active' => !empty($this->getParam('is_active'))
                ];
                $this->repo->create($data);
                addFlashMessage('success', 'Услуга добавлена');
                $this->redirect('index.php', ['entity' => 'service', 'action' => 'index']);
            } else {
                addFlashMessage('error', 'Исправьте ошибки');
                $this->render('services/form', ['data' => $_POST, 'errors' => $errors, 'action' => 'create', 'categories' => $this->catRepo->getAllOrdered()]);
                return;
            }
        }
        $this->render('services/form', ['data' => [], 'errors' => [], 'action' => 'create', 'categories' => $this->catRepo->getAllOrdered()]);
    }
    
    public function edit(): void {
        $id = $this->validateId($this->getParam('id'));
        if (!$id || !($service = $this->repo->findById($id))) {
            $this->redirect('index.php', ['entity' => 'service', 'action' => 'index']);
        }
        if ($this->isPost()) {
            if (!verifyCsrfToken($this->getParam('csrf_token') ?? '')) {
                $this->redirect('index.php', ['entity' => 'service', 'action' => 'edit', 'id' => $id]);
            }
            $errors = $this->validateServiceData($_POST);
            if (empty($errors)) {
                $data = [
                    'name' => trim($this->getParam('name') ?? ''),
                    'description' => trim($this->getParam('description') ?? ''),
                    'price' => (float) ($this->getParam('price') ?? 0),
                    'duration_minutes' => (int) ($this->getParam('duration_minutes') ?? 0),
                    'category_id' => $this->getParam('category_id') ?: null,
                    'requires_consultation' => !empty($this->getParam('requires_consultation')),
                    'is_active' => !empty($this->getParam('is_active'))
                ];
                $this->repo->update($id, $data);
                addFlashMessage('success', 'Услуга обновлена');
                $this->redirect('index.php', ['entity' => 'service', 'action' => 'index']);
            } else {
                addFlashMessage('error', 'Исправьте ошибки');
                $this->render('services/form', ['data' => array_merge($service, $_POST), 'errors' => $errors, 'action' => 'edit', 'id' => $id, 'categories' => $this->catRepo->getAllOrdered()]);
                return;
            }
        }
        $this->render('services/form', ['data' => $service, 'errors' => [], 'action' => 'edit', 'id' => $id, 'categories' => $this->catRepo->getAllOrdered()]);
    }
    
    public function delete(): void {
        $id = $this->validateId($this->getParam('id'));
        if (!$id || !($service = $this->repo->findById($id))) {
            $this->redirect('index.php', ['entity' => 'service', 'action' => 'index']);
        }
        if ($this->isPost()) {
            if (!verifyCsrfToken($this->getParam('csrf_token') ?? '')) {
                $this->redirect('index.php', ['entity' => 'service', 'action' => 'index']);
            }
            if ($this->repo->hasAppointments($id)) {
                addFlashMessage('error', 'Нельзя удалить: услуга используется в записях');
                $this->redirect('index.php', ['entity' => 'service', 'action' => 'index']);
            }
            $this->repo->delete($id);
            addFlashMessage('success', 'Услуга удалена');
            $this->redirect('index.php', ['entity' => 'service', 'action' => 'index']);
        }
        $this->render('services/delete', ['service' => $service]);
    }
    
    public function view(): void {
        $id = $this->validateId($this->getParam('id'));
        if (!$id || !($service = $this->repo->findById($id))) {
            $this->redirect('index.php', ['entity' => 'service', 'action' => 'index']);
        }
        
        $categories = $this->catRepo->getAllOrdered();
        $categoryName = null;
        foreach ($categories as $c) {
            if ($c['id'] == $service['category_id']) {
                $categoryName = $c['name'];
                break;
            }
        }
        
        $products = $this->prodRepo->getProductsForService($id);
        
        $this->render('services/view', [
            'service' => $service,
            'categoryName' => $categoryName,
            'products' => $products
        ]);
    }
    
    private function validateServiceData(array $data): array {
        $errors = [];
        if (empty(trim($data['name'] ?? ''))) $errors['name'] = 'Название обязательно';
        if ((float)($data['price'] ?? 0) <= 0) $errors['price'] = 'Цена > 0';
        if ((int)($data['duration_minutes'] ?? 0) <= 0) $errors['duration_minutes'] = 'Длительность > 0';
        return $errors;
    }
}