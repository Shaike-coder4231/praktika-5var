<?php
class AppointmentController extends BaseController {
    private AppointmentRepository $repo;
    private MasterRepository $masterRepo;
    private ServiceRepository $serviceRepo;
    private ClientRepository $clientRepo;

    public function __construct(PDO $pdo) {
        parent::__construct($pdo);
        $this->repo = new AppointmentRepository($pdo);
        $this->masterRepo = new MasterRepository($pdo);
        $this->serviceRepo = new ServiceRepository($pdo);
        $this->clientRepo = new ClientRepository($pdo);
    }

    public function create(): void {
        if ($this->isPost()) {
            if (!verifyCsrfToken($this->getParam('csrf_token') ?? '')) {
                die("Ошибка CSRF");
            }

            try {
                $clientId = (int)($this->getParam('client_id') ?? 0);
                if ($clientId == 0) { // Если новый клиент - создаем его на лету (упрощенно)
                     // В реальном проекте лучше редирект на форму регистрации
                     $clientId = $this->clientRepo->create([
                         'first_name' => $this->getParam('first_name'),
                         'last_name' => $this->getParam('last_name'),
                         'phone' => $this->getParam('phone')
                     ]);
                }

                $service = $this->serviceRepo->findById($this->getParam('service_id'));
                $data = [
                    'client_id' => $clientId,
                    'master_id' => (int)$this->getParam('master_id'),
                    'service_id' => (int)$this->getParam('service_id'),
                    'appointment_date' => $this->getParam('appointment_date'),
                    'appointment_time' => $this->getParam('appointment_time'),
                    'duration_minutes' => (int)$service['duration_minutes'],
                    'notes' => $this->getParam('notes')
                ];

                $newId = $this->repo->create($data);
                addFlashMessage('success', "Запись создана! Код: $data[booking_code]");
                $this->redirect('index.php', ['entity' => 'appointment', 'action' => 'view', 'id' => $newId]);

            } catch (Exception $e) {
                addFlashMessage('error', $e->getMessage());
                $this->redirect('index.php', ['entity' => 'appointment', 'action' => 'create']);
            }
        }
        
        $masters = $this->masterRepo->getActive();
        $services = $this->serviceRepo->getActive();
        $this->render('appointments/create', ['masters' => $masters, 'services' => $services]);
    }

    public function index(): void {
        $page = max(1, (int)($this->getParam('page') ?? 1));
        $offset = ($page - 1) * 20;
        
        $filters = [
            'status' => $this->getParam('status'),
            'date_from' => $this->getParam('date_from'),
            'master_id' => $this->getParam('master_id')
        ];
        
        $items = $this->repo->getList($filters, 20, $offset);
        $masters = $this->masterRepo->getAllOrdered();

        $this->render('appointments/index', ['items' => $items, 'masters' => $masters, 'filters' => $filters, 'page' => $page]);
    }

    public function view(): void {
        $id = $this->validateId($this->getParam('id'));
        $item = $this->repo->findById($id);
        if (!$item) {
            addFlashMessage('error', 'Запись не найдена');
            $this->redirect('index.php', ['entity' => 'appointment', 'action' => 'index']);
        }
        $this->render('appointments/view', ['item' => $item]);
    }

    public function changeStatus(): void {
        if ($this->isPost() && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            $id = (int)$this->getParam('id');
            $status = $this->getParam('status');
            try {
                $this->repo->updateStatus($id, $status);
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;
        }
    }
    
    public function reschedule(): void {
        $id = $this->validateId($this->getParam('id'));
        $item = $this->repo->findById($id);
        
        if ($this->isPost()) {
             try {
                $this->repo->reschedule($id, $this->getParam('appointment_date'), $this->getParam('appointment_time'));
                addFlashMessage('success', 'Запись перенесена');
                $this->redirect('index.php', ['entity' => 'appointment', 'action' => 'view', 'id' => $id]);
             } catch (Exception $e) {
                 addFlashMessage('error', $e->getMessage());
             }
        }
        
        $this->render('appointments/reschedule', ['item' => $item]);
    }

    public function reports(): void {
        $month = $this->getParam('month') ?? date('Y-m');
        $daily = $this->repo->getReportDailyRevenue($month);
        $masters = $this->repo->getReportMasterWorkload($month);
        $this->render('appointments/reports', ['daily' => $daily, 'masters' => $masters, 'month' => $month]);
    }

    public function exportCsv(): void {
        $month = $this->getParam('month') ?? date('Y-m');
        $data = $this->repo->getReportDailyRevenue($month);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=report.csv');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Дата', 'Записей', 'Выручка']);
        foreach ($data as $row) {
            fputcsv($output, [$row['day'], $row['count'], $row['revenue']]);
        }
        fclose($output);
        exit;
    }
}