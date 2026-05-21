<?php
class ProductRepository extends BaseRepository {
    protected string $table = 'products';
    protected array $fillable = ['name', 'description', 'unit', 'stock_quantity', 'min_stock_quantity'];
    
    public function getLowStockProducts(): array {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE stock_quantity <= min_stock_quantity ORDER BY stock_quantity ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function checkStock(int $productId, int $requiredQuantity): bool {
        $stmt = $this->pdo->prepare("SELECT stock_quantity FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $productId]);
        $product = $stmt->fetch();
        return $product && $product['stock_quantity'] >= $requiredQuantity;
    }
    
    public function decreaseStock(int $productId, int $quantity): bool {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET stock_quantity = stock_quantity - :qty WHERE id = :id AND stock_quantity >= :qty");
        return $stmt->execute(['id' => $productId, 'qty' => $quantity]);
    }
    
    public function getProductsForService(int $serviceId): array {
        $stmt = $this->pdo->prepare("
            SELECT p.*, sp.quantity_required 
            FROM products p 
            INNER JOIN service_products sp ON p.id = sp.product_id 
            WHERE sp.service_id = :service_id
        ");
        $stmt->execute(['service_id' => $serviceId]);
        return $stmt->fetchAll();
    }
}