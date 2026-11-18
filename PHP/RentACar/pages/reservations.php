<?php
session_start();

include_once __DIR__ . "/../class/variable.php";

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

$reservations = [];
$conn = null;

try {
  $conn = new PDO("mysql:host=" . SERVER_NAME . ";dbname=" . DB_NAME . ";charset=utf8mb4", USERNAME, PASSWORD_DB);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  error_log($e->getMessage());
  $errors_message[] = "No se pudo conectar con la base de datos";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($conn == null) {
    $errors_message[] = "No se pudo conectar con la base de datos";
  } elseif (!is_string($_POST['token'] ?? null) || !hash_equals($_SESSION['token'], $_POST['token'])) {
    $errors_message[] = "El formulario ha caducado. Inténtalo de nuevo";
  } elseif (($_POST[ACTION_SUBMIT] ?? '') !== SUBMIT_RESERVATION_CANCEL || !is_string($_POST['reservation-id'] ?? null) || !ctype_digit($_POST['reservation-id'])) {
    $errors_message[] = "Selecciona una reserva válida";
  } else {
    try {
      $stmt = $conn->prepare("UPDATE reservations SET state = 'cancelled' WHERE id = ? AND client_id = (SELECT id FROM clients WHERE user_id = ?) AND state <> 'cancelled'");
      $stmt->execute([(int) $_POST['reservation-id'], $_SESSION[USER_ID_COLUMN]]);

      if ($stmt->rowCount() > 0) {
        $_SESSION['reservation-success-message'] = "Reserva cancelada correctamente";
      } else {
        $errors_message[] = "No se encontró una reserva activa de tu cuenta";
      }
    } catch (PDOException $e) {
      error_log($e->getMessage());
      $errors_message[] = "No se pudo cancelar la reserva";
    }
  }

  $_SESSION['reservation-errors-message'] = $errors_message;
  header("Location: reservations.php");
  exit;
}

if ($conn != null) {
  try {
    $stmt = $conn->prepare("SELECT reservations.*, vehicles.plate, vehicles.brand, vehicles.model FROM reservations JOIN clients ON reservations.client_id = clients.id JOIN vehicles ON reservations.vehicle_id = vehicles.id WHERE clients.user_id = ? ORDER BY reservations.id DESC");
    $stmt->execute([$_SESSION[USER_ID_COLUMN]]);
    $reservations = $stmt->fetchAll();
  } catch (PDOException $e) {
    error_log($e->getMessage());
    $errors_message[] = "No se pudieron cargar las reservas";
  }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ver reservas - RentACar</title>
</head>

<body>
  <header>
    <h1>Ver reservas</h1>
    <a href="client.php">Reservar vehículo</a>
    <a href="../main.php">Volver al inicio</a>
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

  <?php if (empty($reservations) && empty($errors_message)) { ?>
    <p>Todavía no tienes reservas.</p>
  <?php } elseif (!empty($reservations)) { ?>
    <table border="1">
      <thead>
        <tr>
          <th>Vehículo</th>
          <th>Matrícula asignada</th>
          <th>Fecha inicio</th>
          <th>Fecha final</th>
          <th>Precio sin descuento</th>
          <th>Descuento</th>
          <th>Precio final</th>
          <th>Estado</th>
          <th>Opciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($reservations as $reservation) { ?>
          <tr>
            <td><?= htmlspecialchars($reservation[BRAND_COLUMN] . ' ' . $reservation[MODEL_COLUMN], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($reservation[PLATE_COLUMN], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= $reservation[START_DATE_COLUMN] ?></td>
            <td><?= $reservation[END_DATE_COLUMN] ?></td>
            <td><?= number_format((float) $reservation['original_price'], 2, ',', '.') ?>€</td>
            <td><?= (int) $reservation['discount'] ?>%</td>
            <td><?= number_format((float) $reservation['final_price'], 2, ',', '.') ?>€</td>
            <td><?= $reservation['state'] === 'cancelled' ? 'Cancelada' : 'Confirmada' ?></td>
            <td>
              <?php if ($reservation['state'] !== 'cancelled') { ?>
                <form action="reservations.php" method="POST">
                  <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                  <input type="hidden" name="reservation-id" value="<?= (int) $reservation[ID_COLUMN] ?>">
                  <button type="submit" name="<?= ACTION_SUBMIT ?>" value="<?= SUBMIT_RESERVATION_CANCEL ?>">Cancelar</button>
                </form>
              <?php } ?>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  <?php } ?>
</body>

</html>
