<?php
if (!defined('ROLE_COLUMN') || !isset($_SESSION[ROLE_COLUMN]) || $_SESSION[ROLE_COLUMN] !== "admin") {
  header("Location: ../../main.php");
  exit;
}

function getVehiclesBlock(PDO $conn): array
{
  $stmt = $conn->query("SELECT * FROM vehicle_blocks order by vehicle_id");

  return array_column($stmt->fetchAll(), null, ID_COLUMN);
}

function checkVehicleBlock(int $vehicle_id, string $start_date, string $end_date, PDO $conn)
{
  $errors_message = [];

  if (!validateDates($start_date, $end_date, $errors_message, false)) {
    throw new RuntimeException($errors_message[0]);
  }

  $stmt = $conn->prepare("SELECT id FROM vehicles WHERE id = ? FOR UPDATE");
  $stmt->execute([$vehicle_id]);

  if (!$stmt->fetch()) {
    throw new RuntimeException("No se encontró el vehículo");
  }

  $stmt = $conn->prepare("SELECT id FROM reservations WHERE vehicle_id = ? AND state <> 'cancelled' AND start_date <= ? AND end_date >= ? LIMIT 1 FOR UPDATE");
  $stmt->execute([$vehicle_id, $end_date, $start_date]);

  if ($stmt->fetch()) {
    throw new RuntimeException("El cierre coincide con una reserva confirmada de ese vehículo");
  }
}

function addVehicleBlock(int $vehicle_id, string $start_date, string $end_date, string $reason, PDO $conn)
{
  $conn->beginTransaction();

  try {
    checkVehicleBlock($vehicle_id, $start_date, $end_date, $conn);
    $stmt = $conn->prepare("INSERT INTO vehicle_blocks (vehicle_id, start_date, end_date, reason) values (?, ?, ?, ?)");
    $stmt->execute([$vehicle_id, $start_date, $end_date, ucfirst(strtolower($reason))]);
    $conn->commit();
  } catch (Throwable $e) {
    $conn->rollBack();
    throw $e;
  }
}

function deleteVehicleBlock(int $id, PDO $conn)
{
  $stmt = $conn->prepare("DELETE FROM vehicle_blocks WHERE id = ?");
  $stmt->execute([$id]);

  if ($stmt->rowCount() === 0) {
    throw new RuntimeException("No se encontró el cierre");
  }
}

function editVehicleBlock(int $id, int $vehicle_id, string $start_date, string $end_date, string $reason, PDO $conn)
{
  $conn->beginTransaction();

  try {
    checkVehicleBlock($vehicle_id, $start_date, $end_date, $conn);
    $stmt = $conn->prepare("SELECT id FROM vehicle_blocks WHERE id = ? FOR UPDATE");
    $stmt->execute([$id]);

    if (!$stmt->fetch()) {
      throw new RuntimeException("No se encontró el cierre");
    }

    $stmt = $conn->prepare("UPDATE vehicle_blocks SET vehicle_id = ?, start_date = ?, end_date = ?, reason = ? WHERE id = ?");
    $stmt->execute([$vehicle_id, $start_date, $end_date, $reason, $id]);
    $conn->commit();
  } catch (Throwable $e) {
    $conn->rollBack();
    throw $e;
  }
}

function validateVehicleBlockForm(array &$errors_message): bool
{
  $requiredFields = [
    VEHICLE_BLOCK => "Vehículo",
    START_DATE_BLOCK => "Fecha inicio",
    END_DATE_BLOCK => "Fecha final",
    REASON_BLOCK => "Razón"
  ];

  foreach ($requiredFields as $field => $display_field) {
    if (!is_string($_POST[$field] ?? null) || trim($_POST[$field]) === '') {
      $errors_message[] = "El campo $display_field es obligatorio";
      return false;
    }
  }

  if (!ctype_digit($_POST[VEHICLE_BLOCK]) || (int) $_POST[VEHICLE_BLOCK] <= 0 || mb_strlen($_POST[REASON_BLOCK]) > 255) {
    $errors_message[] = "El vehículo o la razón del cierre no son válidos";
  }

  return validateDates($_POST[START_DATE_BLOCK], $_POST[END_DATE_BLOCK], $errors_message, false);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST[ACTION_SUBMIT] ?? '', [SUBMIT_VEHICLE_BLOCK, SUBMIT_VEHICLE_BLOCK_EDIT, SUBMIT_VEHICLE_BLOCK_DELETE], true)) {
  $errors_message = [];
  $action = $_POST[ACTION_SUBMIT];
  $block_id = is_string($_POST['block-id'] ?? null) && ctype_digit($_POST['block-id']) ? (int) $_POST['block-id'] : 0;

  if ($conn == null) {
    $errors_message[] = "No se pudo conectar con la base de datos";
  } elseif ($action !== SUBMIT_VEHICLE_BLOCK && $block_id <= 0) {
    $errors_message[] = "Selecciona un cierre válido";
  } elseif ($action === SUBMIT_VEHICLE_BLOCK_DELETE || validateVehicleBlockForm($errors_message)) {
    try {
      switch ($action) {
        case SUBMIT_VEHICLE_BLOCK:
          addVehicleBlock((int) $_POST[VEHICLE_BLOCK], $_POST[START_DATE_BLOCK], $_POST[END_DATE_BLOCK], $_POST[REASON_BLOCK], $conn);
          $_SESSION['vehicle-success-message'] = "Bloqueo de vehículo añadido correctamente";
          break;
        case SUBMIT_VEHICLE_BLOCK_EDIT:
          editVehicleBlock($block_id, (int) $_POST[VEHICLE_BLOCK], $_POST[START_DATE_BLOCK], $_POST[END_DATE_BLOCK], $_POST[REASON_BLOCK], $conn);
          $_SESSION['vehicle-success-message'] = "Bloqueo de vehículo modificado correctamente";
          break;
        case SUBMIT_VEHICLE_BLOCK_DELETE:
          deleteVehicleBlock($block_id, $conn);
          $_SESSION['vehicle-success-message'] = "Bloqueo de vehículo eliminado correctamente";
          break;
      }
    } catch (PDOException $e) {
      error_log($e->getMessage());
      $errors_message[] = "No se pudo guardar el cambio del cierre";
    } catch (RuntimeException $e) {
      $errors_message[] = $e->getMessage();
    }
  }

  if (!empty($errors_message)) {
    $_SESSION['vehicle-errors-message'] = $errors_message;
    $_SESSION['vehicle-old-fields'] = [
      VEHICLE_BLOCK => $_POST[VEHICLE_BLOCK] ?? '',
      START_DATE_BLOCK => $_POST[START_DATE_BLOCK] ?? '',
      END_DATE_BLOCK => $_POST[END_DATE_BLOCK] ?? '',
      REASON_BLOCK => $_POST[REASON_BLOCK] ?? ''
    ];
  }

  header("Location: admin.php" . (!empty($errors_message) && $action === SUBMIT_VEHICLE_BLOCK_EDIT ? '?edit-block=' . $block_id : ''));
  exit;
}
