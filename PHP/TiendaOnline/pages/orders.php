<?php
function placeOrder(PDO $conn, int $client_id, array $products): int
{
  if (!$products) {
    throw new RuntimeException("El carrito está vacío");
  }

  ksort($products);
  $conn->beginTransaction();

  try {
    $stmt = $conn->prepare("INSERT INTO orders (client_id, state) VALUES (?, 'confirmed')");
    $stmt->execute([$client_id]);
    $order_id = (int) $conn->lastInsertId();

    $update = $conn->prepare("UPDATE product SET stock = stock - ? WHERE id = ? AND active = 1 AND stock >= ?");
    $insert = $conn->prepare("INSERT INTO details_order (order_id, product_id, amount, unit_price, subtotal)
      SELECT ?, id, ?, price, price * ? FROM product WHERE id = ?");

    foreach ($products as $id => $amount) {
      $update->execute([$amount, $id, $amount]);
      if (!$update->rowCount()) {
        $product = getProductById($conn, $id);
        if ($product) {
          throw new RuntimeException("No hay stock suficiente de " . $product['name']);
        }
        throw new RuntimeException("Hay un producto que ya no está disponible");
      }
      $insert->execute([$order_id, $amount, $amount, $id]);
    }

    $stmt = $conn->prepare("UPDATE orders SET total = (SELECT SUM(subtotal) FROM details_order WHERE order_id = ?) WHERE id = ?");
    $stmt->execute([$order_id, $order_id]);
    $conn->commit();

    return $order_id;
  } catch (Throwable $e) {
    if ($conn->inTransaction()) {
      $conn->rollBack();
    }
    throw $e;
  }
}

function showOrders(PDO $conn, int $client_id): void
{
  $stmt = $conn->prepare("SELECT o.id, o.order_date, o.total, p.name, d.amount, d.unit_price, d.subtotal
    FROM orders o JOIN details_order d ON d.order_id = o.id JOIN product p ON p.id = d.product_id
    WHERE o.client_id = ? AND o.state = 'confirmed' ORDER BY o.id DESC, d.id");
  $stmt->execute([$client_id]);
  $orders = [];
  foreach ($stmt->fetchAll() as $line) {
    $orders[$line['id']][] = $line;
  }
  ?>
  <h1>Mis pedidos</h1>
  <?php if (!$orders) { ?>
    <p>Todavía no has hecho ningún pedido.</p>
    <a href="index.php">Ver productos</a>
  <?php } ?>
  <div class="cart-list">
    <?php foreach ($orders as $id => $lines) { ?>
      <article class="panel order">
        <h2>Pedido n.º <?= (int) $id ?></h2>
        <p><?= date('d/m/Y H:i', strtotime($lines[0]['order_date'])) ?></p>
        <ul>
          <?php foreach ($lines as $line) { ?>
            <li><?= escapeHtml($line['name']) ?> · <?= (int) $line['amount'] ?> uds. × <?= formatPrice($line['unit_price']) ?> = <?= formatPrice($line['subtotal']) ?></li>
          <?php } ?>
        </ul>
        <p class="price">Total <?= formatPrice($lines[0]['total']) ?></p>
      </article>
    <?php } ?>
  </div>
  <?php
}
