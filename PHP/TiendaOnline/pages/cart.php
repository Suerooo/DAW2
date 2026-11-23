<?php
function showCart(PDO $conn, Cart $cart, int $client_id): void
{
  $products = $cart->getProducts();
  ?>
  <h1>Carrito</h1>
  <?php if (!$products) { ?>
    <p>Tu carrito está vacío.</p>
    <a href="index.php">Ver productos</a>
  <?php } else { ?>
    <?php
    $total = 0;
    $available = true;
    ?>
    <div class="cart-list">
      <?php foreach ($products as $id => $amount) { ?>
        <?php $product = getProductById($conn, $id); ?>
        <article class="panel cart-product">
          <div>
            <?php if ($product) { ?>
              <h2><a href="index.php?details-product=<?= $id ?>"><?= escapeHtml($product['name']) ?></a></h2>
              <?php
              $subtotal = (float) $product['price'] * $amount;
              $total += $subtotal;
              ?>
              <p><?= $amount ?> uds. × <?= formatPrice($product['price']) ?> = <?= formatPrice($subtotal) ?></p>
              <?php if ($amount > (int) $product['stock']) { ?>
                <?php $available = false; ?>
                <p class="out-of-stock">Stock disponible: <?= (int) $product['stock'] ?> uds.</p>
              <?php } ?>
            <?php } else { ?>
              <?php $available = false; ?>
              <h2>Producto no disponible</h2>
            <?php } ?>
          </div>
          <form action="index.php?view=cart" method="POST">
            <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
            <input type="hidden" name="product-id" value="<?= $id ?>">
            <button class="secondary" type="submit" name="action" value="remove-product">Quitar</button>
          </form>
        </article>
      <?php } ?>
    </div>
    <p class="price">Total <?= formatPrice($total) ?></p>
    <?php if (!$available) { ?>
      <p>Quita los productos que ya no tengan unidades suficientes para continuar.</p>
    <?php } elseif (!$client_id) { ?>
      <a class="button" href="index.php?view=account">Inicia sesión para hacer el pedido</a>
    <?php } else { ?>
      <form action="index.php?view=cart" method="POST">
        <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
        <button type="submit" name="action" value="place-order">Confirmar pedido</button>
      </form>
    <?php } ?>
  <?php } ?>
  <?php
}
