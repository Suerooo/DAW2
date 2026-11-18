<?php
session_start();

include_once __DIR__ . "/../class/variable.php";
include_once "vehicle-reserve.php";

if (!isset($_SESSION[USER_ID_COLUMN], $_SESSION[ROLE_COLUMN]) || $_SESSION[ROLE_COLUMN] !== "client") {
  header("Location: ../main.php");
  exit;
}

if (empty($_SESSION['token'])) {
  $_SESSION['token'] = bin2hex(random_bytes(32));
}

$errors_message = $_SESSION['reservation-errors-message'] ?? [];
unset($_SESSION['reservation-errors-message']);

$success_message = $_SESSION['reservation-success-message'] ?? '';
unset($_SESSION['reservation-success-message']);

$conn = null;

try {
  $conn = new PDO("mysql:host=" . SERVER_NAME . ";dbname=" . DB_NAME . ";charset=utf8mb4", USERNAME, PASSWORD_DB);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  error_log($e->getMessage());
  $errors_message[] = "No se pudo conectar con la base de datos";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $errors_message = [];
  $start_date = is_string($_POST[START_DATE_RESERVATION] ?? null) ? $_POST[START_DATE_RESERVATION] : '';
  $end_date = is_string($_POST[END_DATE_RESERVATION] ?? null) ? $_POST[END_DATE_RESERVATION] : '';

  if ($conn == null) {
    $errors_message[] = "No se pudo conectar con la base de datos";
  } elseif (!is_string($_POST['token'] ?? null) || !hash_equals($_SESSION['token'], $_POST['token'])) {
    $errors_message[] = "El formulario ha caducado. Inténtalo de nuevo";
  } elseif (($_POST[ACTION_SUBMIT] ?? '') !== SUBMIT_RESERVATION || !is_string($_POST[MODEL_RESERVATION] ?? null) || !ctype_digit($_POST[MODEL_RESERVATION]) || !is_string($_POST[BASE_PRICE_VEHICLE] ?? null) || !is_numeric($_POST[BASE_PRICE_VEHICLE])) {
    $errors_message[] = "Selecciona un modelo disponible";
  } elseif (validateDates($start_date, $end_date, $errors_message)) {
    try {
      reserveVehicle((int) $_SESSION[USER_ID_COLUMN], (int) $_POST[MODEL_RESERVATION], $start_date, $end_date, (float) $_POST[BASE_PRICE_VEHICLE], $conn);
      $_SESSION['reservation-success-message'] = "Reserva confirmada correctamente";
      header("Location: reservations.php");
      exit;
    } catch (PDOException $e) {
      error_log($e->getMessage());
      $errors_message[] = "No se pudo completar la reserva. Consulta de nuevo la disponibilidad";
    } catch (RuntimeException $e) {
      $errors_message[] = $e->getMessage();
    }
  }

  $_SESSION['reservation-errors-message'] = $errors_message;
  header("Location: client.php?" . http_build_query([START_DATE_RESERVATION => $start_date, END_DATE_RESERVATION => $end_date]));
  exit;
}

$vehicles = [];
$catalog_loaded = false;
$start_date = is_string($_GET[START_DATE_RESERVATION] ?? null) ? $_GET[START_DATE_RESERVATION] : '';
$end_date = is_string($_GET[END_DATE_RESERVATION] ?? null) ? $_GET[END_DATE_RESERVATION] : '';
$date_errors = [];
$search = isset($_GET[START_DATE_RESERVATION]) || isset($_GET[END_DATE_RESERVATION]);

if (!$search || validateDates($start_date, $end_date, $date_errors)) {
  if ($conn != null) {
    try {
      $vehicles = getAvailableVehicles($conn, $start_date, $end_date);
      $catalog_loaded = true;
    } catch (PDOException $e) {
      error_log($e->getMessage());
      $errors_message[] = "No se pudo consultar la disponibilidad";
    }
  }
}

$errors_message = array_merge($errors_message, $date_errors);

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reservar vehículo - RentACar</title>
</head>

<body>
  <header>
    <h1>Reservar vehículo</h1>
    <a href="../main.php">Volver al inicio</a>
    <a href="reservations.php">Ver reservas</a>
    <form action="logout.php" method="POST">
      <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
      <button type="submit">Cerrar sesión</button>
    </form>
  </header>

  <?php if (!empty($success_message)) { ?>
    <p><?= htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8') ?></p>
  <?php } ?>

  <?php if (!empty($errors_message)) { ?>
    <ul>
      <?php foreach ($errors_message as $error) { ?>
        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
      <?php } ?>
    </ul>
  <?php } ?>

  <?php
  showAvailabilityForm($start_date, $end_date);

  if ($catalog_loaded) {
    showVehicles($vehicles, $start_date, $end_date, true);
  }
  ?>
</body>

</html>
