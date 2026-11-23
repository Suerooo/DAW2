<?php
function showProductPhoto(array $product): void
{
  if (!empty($product['imagen'])) {
    ?>
    <img class="product-photo" src="<?= escapeHtml($product['imagen']) ?>" alt="<?= escapeHtml($product['name']) ?>">
    <?php
  }
}

function showProductList(array $products): void
{
  ?>
  <div class="product-list">
    <?php foreach ($products as $product) { ?>
      <article class="product">
        <a href="index.php?details-product=<?= (int) $product['id'] ?>">
          <?php showProductPhoto($product); ?>
          <h3><?= escapeHtml($product['name']) ?></h3>
        </a>
        <p class="price"><?= formatPrice($product['price']) ?></p>
        <?php if ((int) $product['stock'] === 0) { ?>
          <p class="out-of-stock">Agotado</p>
        <?php } ?>
      </article>
    <?php } ?>
  </div>
  <?php
}

function showProductsOrdered(PDO $conn): void
{
  $ordered_products = [];
  foreach (getProducts($conn) as $product) {
    $ordered_products[$product['category']][] = $product;
  }

  if (!$ordered_products) {
    echo '<p>Todavía no hay productos en el catálogo.</p>';
  }

  foreach ($ordered_products as $category => $products) {
    ?>
    <section class="catalog-section">
      <h2><?= escapeHtml($category) ?></h2>
      <?php showProductList($products); ?>
    </section>
    <?php
  }
}

function showDetailsProduct(array $product, Cart $cart): void
{
  $stock = (int) $product['stock'];
  $available = $stock - ($cart->getProducts()[$product['id']] ?? 0);
  ?>
  <a class="back" href="index.php">Volver al catálogo</a>
  <article class="panel product-details">
    <h1><?= escapeHtml($product['name']) ?></h1>
    <?php showProductPhoto($product); ?>
    <p><?= escapeHtml($product['description'] ?? '') ?></p>
    <p class="price"><?= formatPrice($product['price']) ?></p>
    <dl>
      <dt>Categoría</dt><dd><?= escapeHtml($product['category']) ?></dd>
      <dt>Marca</dt><dd><?= escapeHtml($product['brand']) ?></dd>
      <dt>Modelo</dt><dd><?= escapeHtml($product['model']) ?></dd>
      <dt>Referencia</dt><dd><?= (int) $product['id'] ?></dd>
      <dt>Fecha de alta</dt><dd><?= escapeHtml($product['added_date']) ?></dd>
      <dt>Stock</dt><dd><?= $stock ?> unidades</dd>
    </dl>
    <?php if ($stock === 0) { ?>
      <p class="out-of-stock">Agotado</p>
    <?php } elseif ($available < 1) { ?>
      <p>Ya tienes todas las unidades disponibles en el carrito.</p>
    <?php } else { ?>
      <form action="index.php?details-product=<?= (int) $product['id'] ?>" method="POST" class="quantity-form">
        <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
        <input type="hidden" name="product-id" value="<?= (int) $product['id'] ?>">
        <label for="amount">Cantidad</label>
        <input type="number" name="amount" id="amount" min="1" max="<?= $available ?>" value="1" required>
        <button type="submit" name="action" value="add-product">Añadir al carrito</button>
      </form>
    <?php } ?>
  </article>
  <?php
}
