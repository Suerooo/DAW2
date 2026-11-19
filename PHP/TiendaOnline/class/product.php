<?php

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

    // --- Getters ---

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
  $stmt = $conn->prepare("SELECT p.*, c.name AS category FROM product p JOIN category c ON c.id = p.category_id WHERE p.active = 1;");
  $stmt->execute();
  return $stmt->fetchAll();
}

function getProductById(PDO $conn, int $id)
{
  $stmt = $conn->prepare("SELECT p.*, c.name AS category FROM product p JOIN category c ON c.id = p.category_id WHERE p.id = ? AND p.active = 1;");
  $stmt->execute([$id]);
  return $stmt->fetch();
}

function getCategoryDescription(PDO $conn, string $category_name)
{
  $stmt = $conn->prepare("SELECT description from category where name = ?;");
  $stmt->execute([$category_name]);
  $category = $stmt->fetch();
  return $category["description"];
}

function showProductsOrdered(PDO $conn)
{
  $products = getProducts($conn);

  $ordered_product = [];

  foreach ($products as $product) {
    $ordered_product[$product["category"]][] = $product;
  }

  foreach ($ordered_product as $category => $products_of_category) {
?>
    <section>
      <h2><?= $category ?></h2>
      <p><?= getCategoryDescription($conn, $category) ?></p>
      <ul>
        <?php foreach ($products_of_category as $product): ?>
          <article class="product">
            <img src="<?= $product["imagen"] ?>">
            <h2><a href="index.php?details-product=<?= $product["id"] ?>"><?= $product["name"] ?></a></h2>
            <p>Precio: <?= $product["price"] ?>€</p>
          </article>
        <?php endforeach; ?>
      </ul>
    </section>
  <?php
  }
}

function showDetailsProduct(PDO $conn, int $id)
{
  $product = getProductById($conn, $id);

  ?>
  <article>
    <img src="<?= $product["imagen"] ?>">
    <h2><?= $product["name"] ?></h2>
    <p><?= $product["description"] ?></p>
    <p>Precio: <?= $product["price"] ?>€</p>
    <p>Marca: <?= $product["brand"] ?></p>
    <p>Modelo: <?= $product["model"] ?></p>
    <p>Stock: <?= $product["stock"] ?></p>
    <a href="index.php">Volver al catalogo</a>
  </article>
<?php
}
