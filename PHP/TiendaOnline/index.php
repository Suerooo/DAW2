<?php
require_once __DIR__ . '/database/connection.php';
require_once __DIR__ . '/class/product.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
  require __DIR__ . '/pages/search.php';
  showSearchResults();
  exit;
}

require_once __DIR__ . '/class/client.php';
require_once __DIR__ . '/class/cart.php';
require_once __DIR__ . '/pages/register.php';
require_once __DIR__ . '/pages/login.php';
require_once __DIR__ . '/pages/product.php';
require_once __DIR__ . '/pages/cart.php';
require_once __DIR__ . '/pages/orders.php';

session_name('TIENDAONLINE');
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);
if (empty($_SESSION['token'])) {
  $_SESSION['token'] = bin2hex(random_bytes(32));
}

function escapeHtml(string $value): string
{
  return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function formatPrice(float $price): string
{
  return number_format($price, 2, ',', '.') . ' €';
}

function postText(string $field): string
{
  $value = $_POST[$field] ?? '';
  return is_string($value) ? $value : '';
}

function positiveInt($value): int
{
  return (int) filter_var($value, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 4294967295]
  ]);
}

$cart = new Cart();
$client_id = (int) ($_SESSION['client-id'] ?? 0);
$view = $_GET['view'] ?? '';
$details = isset($_GET['details-product']);
$product_id = positiveInt($_GET['details-product'] ?? null);
$message = $_SESSION['success-message'] ?? '';
$error = $_SESSION['errors-message'] ?? '';
unset($_SESSION['success-message'], $_SESSION['errors-message']);

$conn = null;
try {
  $conn = connectDatabase();
} catch (PDOException $e) {
  error_log($e->getMessage());
  http_response_code(503);
  $error = "No se pudo conectar con la base de datos";
}

if ($conn && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $location = 'index.php';
  if (in_array($view, ['account', 'cart', 'orders'], true)) {
    $location .= '?view=' . $view;
  } elseif ($product_id) {
    $location .= '?details-product=' . $product_id;
  }

  $action = postText('action');
  try {
    if (!hash_equals($_SESSION['token'], postText('token'))) {
      throw new RuntimeException("Vuelve a cargar la página e inténtalo de nuevo");
    }

    switch ($action) {
      case 'register-action':
      case 'login-action':
        $email = trim(postText($action === 'register-action' ? 'email-register-fr' : 'email-login-fr'));
        $password = postText($action === 'register-action' ? 'password-register-fr' : 'password-login-fr');

        if ($action === 'register-action') {
          $name = trim(postText('name-register-fr'));
          $surname = trim(postText('surname-register-fr'));
          if ($name === '' || mb_strlen($name) > 100 || $surname === '' || mb_strlen($surname) > 150 ||
              !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190 ||
              mb_strlen($password) < 8 || strlen($password) > 72 || strpos($password, "\0") !== false) {
            throw new RuntimeException("Completa los datos con un correo válido y una contraseña de al menos 8 caracteres");
          }
          $client_id = registerUser(new Client($name, $surname, $email, $password), $conn);
        } else {
          $client_id = login($email, $password, $conn);
          if (!$client_id) {
            throw new RuntimeException("El correo o la contraseña no son correctos");
          }
        }

        session_regenerate_id(true);
        $_SESSION['client-id'] = $client_id;
        $_SESSION['token'] = bin2hex(random_bytes(32));
        $location = $cart->getProducts() ? 'index.php?view=cart' : 'index.php';
        break;

      case 'close-session-action':
        unset($_SESSION['client-id']);
        session_regenerate_id(true);
        $_SESSION['token'] = bin2hex(random_bytes(32));
        break;

      case 'add-product':
        $id = positiveInt(postText('product-id'));
        $amount = positiveInt(postText('amount'));
        $product = $id ? getProductById($conn, $id) : false;
        if (!$product || !$amount) {
          throw new RuntimeException("Selecciona un producto y una cantidad válidos");
        }
        $cart->addProductCart($id, $amount, (int) $product['stock']);
        $location = 'index.php?view=cart';
        break;

      case 'remove-product':
        $id = positiveInt(postText('product-id'));
        if (!$id) {
          throw new RuntimeException("Selecciona un producto válido");
        }
        $cart->removeProductCart($id);
        break;

      case 'place-order':
        if (!$client_id) {
          $location = 'index.php?view=account';
          throw new RuntimeException("Inicia sesión para hacer el pedido");
        }
        $order_id = placeOrder($conn, $client_id, $cart->getProducts());
        $cart->clearCart();
        $_SESSION['token'] = bin2hex(random_bytes(32));
        $_SESSION['success-message'] = "Pedido n.º $order_id confirmado";
        $location = 'index.php?view=orders';
        break;
    }
  } catch (PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['errors-message'] = (int) ($e->errorInfo[1] ?? 0) === 1062 && $action === 'register-action'
      ? "Ya existe una cuenta con ese correo"
      : "No se pudo completar la operación";
  } catch (RuntimeException $e) {
    $_SESSION['errors-message'] = $e->getMessage();
  }

  header('Location: ' . $location, true, 303);
  exit;
}

if ($view === 'orders' && !$client_id) {
  header('Location: index.php?view=account');
  exit;
}

$product = false;
if ($conn && $details) {
  $product = $product_id ? getProductById($conn, $product_id) : false;
  if (!$product) {
    http_response_code(404);
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tienda de componentes</title>
  <link rel="icon" href="data:,">
  <link rel="stylesheet" href="css/style.css">
  <script src="JS/search.js" defer></script>
</head>
<body>
  <header class="header">
    <div class="header-inner">
      <a class="site-name" href="index.php">Tienda de componentes</a>
      <nav aria-label="Navegación principal">
        <a href="index.php">Catálogo</a>
        <a href="index.php?view=cart">Carrito (<?= array_sum($cart->getProducts()) ?>)</a>
        <?php if ($client_id) { ?>
          <a href="index.php?view=orders">Mis pedidos</a>
          <?php showCloseSession(); ?>
        <?php } else { ?>
          <a href="index.php?view=account">Mi cuenta</a>
        <?php } ?>
      </nav>
    </div>
  </header>

  <main>
    <?php if ($error !== '') { ?>
      <p class="message error" role="alert"><?= escapeHtml($error) ?></p>
    <?php } ?>
    <?php if ($message !== '') { ?>
      <p class="message success" role="status"><?= escapeHtml($message) ?></p>
    <?php } ?>

    <?php if ($conn) { ?>
      <?php if ($view === 'account') { ?>
        <h1>Mi cuenta</h1>
        <?php if ($client_id) { ?>
          <p>Ya has iniciado sesión.</p>
        <?php } else { ?>
          <div class="account-forms">
            <?php
            showLoginForm();
            showRegisterForm();
            ?>
          </div>
        <?php } ?>
      <?php } elseif ($view === 'cart') { ?>
        <?php showCart($conn, $cart, $client_id); ?>
      <?php } elseif ($view === 'orders') { ?>
        <?php showOrders($conn, $client_id); ?>
      <?php } elseif ($details) { ?>
        <?php if ($product) { ?>
          <?php showDetailsProduct($product, $cart); ?>
        <?php } else { ?>
          <h1>Producto no disponible</h1>
          <a href="index.php">Volver al catálogo</a>
        <?php } ?>
      <?php } else { ?>
        <h1>Catálogo</h1>
        <div class="search" role="search">
          <label for="product-search">Buscar productos</label>
          <input type="search" id="product-search" placeholder="Nombre, marca, modelo o categoría" maxlength="120" autocomplete="off">
          <p id="search-status" role="status"></p>
          <ul id="search-results"></ul>
        </div>
        <?php $recommendations = getRecommendedProducts($conn); ?>
        <?php if ($recommendations) { ?>
          <section class="catalog-section recommendations">
            <h2>Según tus búsquedas</h2>
            <?php showProductList($recommendations); ?>
          </section>
        <?php } ?>
        <?php showProductsOrdered($conn); ?>
      <?php } ?>
    <?php } ?>
  </main>
</body>
</html>
