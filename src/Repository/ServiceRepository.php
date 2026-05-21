<?php
class ServiceRepository extends BaseRepository {
    protected string $table = 'services';
    protected array $fillable = ['name', 'description', 'price', 'duration_minutes', 'category_id', 'requires_consultation', 'is_active'];
    
    public function getActive(): array {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE is_active = TRUE ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function search(string $query, ?int $limit = null, ?int $offset = null): array {
        $sql = "SELECT * FROM {$this->table} WHERE `name` LIKE :q OR `description` LIKE :q ORDER BY name";
        
        if ($limit !== null) $sql .= " LIMIT :limit";
        if ($offset !== null) $sql .= " OFFSET :offset";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':q', "%$query%", PDO::PARAM_STR);
        if ($limit !== null) $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        if ($offset !== null) $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getServicesRequiringConsultation(): array {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE requires_consultation = TRUE AND is_active = TRUE ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function hasAppointments(int $serviceId): bool {
        $stmt = $this->pdo->prepare("SELECT 1 FROM appointments WHERE service_id = :id LIMIT 1");
        $stmt->execute(['id' => $serviceId]);
        return (bool) $stmt->fetch();
    }
}