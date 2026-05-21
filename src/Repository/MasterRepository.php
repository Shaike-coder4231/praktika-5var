<?php
class MasterRepository extends BaseRepository {
    protected string $table = 'masters';
    protected array $fillable = ['first_name', 'last_name', 'middle_name', 'phone', 'email', 'specialization', 'hire_date', 'is_active'];
    
    public function getActive(): array {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE is_active = TRUE ORDER BY last_name, first_name");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function search(string $query, ?int $limit = null, ?int $offset = null): array {
        $sql = "SELECT * FROM {$this->table} 
                WHERE `last_name` LIKE :q OR `first_name` LIKE :q OR `specialization` LIKE :q
                ORDER BY last_name, first_name";
        
        if ($limit !== null) $sql .= " LIMIT :limit";
        if ($offset !== null) $sql .= " OFFSET :offset";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':q', "%$query%", PDO::PARAM_STR);
        if ($limit !== null) $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        if ($offset !== null) $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function hasFutureAppointments(int $masterId): bool {
        $stmt = $this->pdo->prepare("SELECT 1 FROM appointments WHERE master_id = :id AND appointment_date >= CURDATE() LIMIT 1");
        $stmt->execute(['id' => $masterId]);
        return (bool) $stmt->fetch();
    }
}