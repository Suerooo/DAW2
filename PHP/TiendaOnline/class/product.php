<?php
require_once __DIR__ . '/cookie.php';

class Product {
    public function __construct(
        private int $id,
        private string $category,
        private string $name,
        private string $brand,
        private string $model,
        private string $description,
        private float $price,
        private int $stock,
        private string $image,
        private bool $active = true,
        private string $added_date = ''
    ) {
        if (empty($this->added_date)) {
            $this->added_date = date('Y-m-d H:i:s');
        }
    }

    public function getId(): int {
        return $this->id;
    }

    public function getCategory(): string {
        return $this->category;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getBrand(): string {
        return $this->brand;
    }

    public function getModel(): string {
        return $this->model;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getPrice(): float {
        return $this->price;
    }

    public function getStock(): int {
        return $this->stock;
    }

    public function getImage(): string {
        return $this->image;
    }

    public function isActive(): bool {
        return $this->active;
    }

    public function getAddedDate(): string {
        return $this->added_date;
    }

    public function setPrice(float $price): void {
        if ($price >= 0) {
            $this->price = $price;
        }
    }

    public function setStock(int $stock): void {
        if ($stock >= 0) {
            $this->stock = $stock;
        }
    }

    public function setActive(bool $active): void {
        $this->active = $active;
    }

    public function toArray(): array {
        return [
            'id'          => $this->id,
            'category'    => $this->category,
            'name'        => $this->name,
            'brand'       => $this->brand,
            'model'       => $this->model,
            'description' => $this->description,
            'price'       => $this->price,
            'stock'       => $this->stock,
            'image'       => $this->image,
            'active'      => $this->active,
            'added_date'  => $this->added_date,
        ];
    }
}

function getProducts(PDO $conn)
{
  return $conn->query("SELECT p.*, c.name AS category FROM product p JOIN category c ON c.id = p.category_id
    WHERE p.active = 1 ORDER BY c.id, p.name")->fetchAll();
}

function getProductById(PDO $conn, int $id)
{
  $stmt = $conn->prepare("SELECT p.*, c.name AS category FROM product p JOIN category c ON c.id = p.category_id WHERE p.id = ? AND p.active = 1;");
  $stmt->execute([$id]);
  return $stmt->fetch();
}

function searchProducts(PDO $conn, string $search): array
{
  $term = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search) . '%';
  $stmt = $conn->prepare("SELECT p.id, p.name, p.category_id FROM product p JOIN category c ON c.id = p.category_id
    WHERE p.active = 1 AND (p.name LIKE ? ESCAPE '!' OR p.brand LIKE ? ESCAPE '!'
      OR p.model LIKE ? ESCAPE '!' OR c.name LIKE ? ESCAPE '!')
    ORDER BY p.name LIMIT 8");
  $stmt->execute([$term, $term, $term, $term]);
  return $stmt->fetchAll();
}

function getSearchCategories(): array
{
  $categories = array_filter(readShopCookie('shop_recommendations'), function ($id) {
    return is_int($id) && $id > 0 && $id <= 4294967295;
  });
  return array_slice(array_unique($categories), 0, 5);
}

function saveSearchCategories(array $products): void
{
  if ($products) {
    $recent_categories = [];
    foreach ($products as $product) {
      $recent_categories[] = (int) $product['category_id'];
    }
    $categories = array_unique(array_merge($recent_categories, getSearchCategories()));
    saveShopCookie('shop_recommendations', array_slice($categories, 0, 5));
  }
}

function getRecommendedProducts(PDO $conn): array
{
  $categories = getSearchCategories();
  if (!$categories) {
    return [];
  }

  $parameters = implode(', ', array_fill(0, count($categories), '?'));
  $stmt = $conn->prepare("SELECT id, name, price, stock, imagen FROM product
    WHERE active = 1 AND stock > 0 AND category_id IN ($parameters)
    ORDER BY FIELD(category_id, $parameters), name LIMIT 6");
  $stmt->execute(array_merge($categories, $categories));
  return $stmt->fetchAll();
}
