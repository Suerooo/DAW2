<?php

function getVehiclesBlock(PDO $conn): array
{
  $stmt = $conn->query("SELECT * FROM vehicle_blocks order by vehicle_id");

  return $stmt->fetchAll();
}

function addVehicleBlock(int $vehicle_id, string $start_date, string $end_date, string $reason, PDO $conn)
{
  $stmt = $conn->prepare("INSERT INTO vehicle_blocks (vehicle_id, start_date, end_date, reason) values (?, ?, ?, ?)");
  $stmt->execute([$vehicle_id, $start_date, $end_date, ucfirst(strtolower($reason))]);
}

function deleteVehicleBlock(int $id, PDO $conn)
{
  $stmt = $conn->prepare("DELETE FROM vehicle_blocks WHERE id = ?");
  $stmt->execute([$id]);
}

function editVehicleBlock(int $id, int $vehicle_id, string $start_date, string $end_date, string $reason, PDO $conn)
{
  $stmt = $conn->prepare("UPDATE vehicle_blocks SET vehicle_id = ?, start_date = ?, end_date = ?, reason = ? WHERE id = ?");
  $stmt->execute([$vehicle_id, $start_date, $end_date, $reason, $id]);
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
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
      $errors_message[] = "El campo $display_field es obligatorio";
      return false;
    }
  }

  return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST[ACTION_SUBMIT]) && $_POST[ACTION_SUBMIT] === SUBMIT_VEHICLE_BLOCK) {
  $errors_message = [];

  if ($conn == null) {
    $errors_message[] = "No se pudo conectar con la base de datos";
  } elseif (validateVehicleBlockForm($errors_message)) {
    try {
      addVehicleBlock($_POST[VEHICLE_BLOCK], $_POST[START_DATE_BLOCK], $_POST[END_DATE_BLOCK], $_POST[REASON_BLOCK], $conn);
      $_SESSION['vehicle-success-message'] = "Bloqueo de vehículo añadido correctamente";
    } catch (PDOException $e) {
      error_log($e->getMessage());
      $errors_message[] = "No se pudo bloquear el vehículo" . $e->getMessage();
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

  header("Location: admin.php");
  exit;
}
