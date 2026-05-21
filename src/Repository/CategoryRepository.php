<?php
class CategoryRepository extends BaseRepository {
    protected string $table = 'categories';
    protected array $fillable = ['name', 'description'];
    
    public function getAllOrdered(): array {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}