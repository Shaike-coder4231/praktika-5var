<?php
class AdditionalServiceRepository extends BaseRepository {
    protected string $table = 'additional_services';
    protected array $fillable = ['name', 'description', 'price', 'is_active'];
    
    public function getActive(): array {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE is_active = TRUE ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}