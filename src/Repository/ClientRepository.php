<?php
class ClientRepository extends BaseRepository {
    protected string $table = 'clients';
    protected array $fillable = ['first_name', 'last_name', 'middle_name', 'phone', 'email', 'birth_date'];
    
    public function search(string $query, ?int $limit = null, ?int $offset = null): array {
        $sql = "SELECT * FROM {$this->table} 
                WHERE `last_name` LIKE :q OR `first_name` LIKE :q OR `phone` LIKE :q
                ORDER BY `last_name`, `first_name`";
        
        if ($limit !== null) $sql .= " LIMIT :limit";
        if ($offset !== null) $sql .= " OFFSET :offset";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':q', "%$query%", PDO::PARAM_STR);
        if ($limit !== null) $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        if ($offset !== null) $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function hasAppointments(int $clientId): bool {
        $stmt = $this->pdo->prepare("SELECT 1 FROM appointments WHERE client_id = :id LIMIT 1");
        $stmt->execute(['id' => $clientId]);
        return (bool) $stmt->fetch();
    }
}