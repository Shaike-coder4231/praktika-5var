<?php
abstract class BaseRepository {
    protected PDO $pdo;
    protected string $table;
    protected array $fillable = [];
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    public function findAll(array $orderBy = [], ?int $limit = null, ?int $offset = null): array {
        $sql = "SELECT * FROM {$this->table}";
        
        if (!empty($orderBy)) {
            $parts = [];
            foreach ($orderBy as $field => $dir) {
                $dir = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
                $parts[] = "`$field` $dir";
            }
            $sql .= " ORDER BY " . implode(', ', $parts);
        }
        
        if ($limit !== null) {
            $sql .= " LIMIT :limit";
        }
        if ($offset !== null) {
            $sql .= " OFFSET :offset";
        }
        
        $stmt = $this->pdo->prepare($sql);
        if ($limit !== null) $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        if ($offset !== null) $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function count(array $likeConditions = []): int {
        $sql = "SELECT COUNT(*) as cnt FROM {$this->table}";
        $params = [];
        
        if (!empty($likeConditions)) {
            $parts = [];
            foreach ($likeConditions as $field => $value) {
                $parts[] = "`$field` LIKE :$field";
                $params[$field] = "%$value%";
            }
            $sql .= " WHERE " . implode(' OR ', $parts);
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetch()['cnt'];
    }
    
    public function create(array $data): int {
        $filtered = array_intersect_key($data, array_flip($this->fillable));
        if (empty($filtered)) return 0;
        
        $fields = implode(', ', array_map(fn($f) => "`$f`", array_keys($filtered)));
        $placeholders = ':' . implode(', :', array_keys($filtered));
        
        $sql = "INSERT INTO {$this->table} ($fields) VALUES ($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($filtered);
        return (int) $this->pdo->lastInsertId();
    }
    
    public function update(int $id, array $data): bool {
        $filtered = array_intersect_key($data, array_flip($this->fillable));
        if (empty($filtered)) return true;
        
        $sets = [];
        foreach (array_keys($filtered) as $field) {
            $sets[] = "`$field` = :$field";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE id = :id";
        $filtered['id'] = $id;
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($filtered);
    }
    
    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
    
    public function exists(int $id): bool {
        return $this->findById($id) !== null;
    }
}