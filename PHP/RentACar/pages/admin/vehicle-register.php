<?php
$old_fields = $_SESSION['vehicle-old-fields'] ?? [];
unset($_SESSION['vehicle-old-fields']);

$errors_message = $_SESSION['vehicle-errors-message'] ?? [];
unset($_SESSION['vehicle-errors-message']);

$success_message = $_SESSION['vehicle-success-message'] ?? '';
unset($_SESSION['vehicle-success-message']);

function getVehicles(PDO $conn): array
{
  $stmt = $conn->query("SELECT * FROM vehicles ORDER BY vehicle_class, brand, model, plate");

  return $stmt->fetchAll();
}

function registerVehicle(Vehicle $vehicle, PDO $conn): void
{
  $stmt = $conn->prepare("INSERT INTO vehicles (plate, brand, model, color, doors, seats, transmission, motorization, vehicle_class, base_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->execute([
    $vehicle->get_plate(),
    $vehicle->get_brand(),
    $vehicle->get_model(),
    $vehicle->get_color(),
    $vehicle->get_doors(),
    $vehicle->get_seats(),
    $vehicle->get_transmission(),
    $vehicle->get_motorization(),
    $vehicle->get_vehicle_class(),
    $vehicle->get_base_price()
  ]);
}

function validateVehicleRegisterForm(array &$errors_message): bool
{
  $requiredFields = [
    PLATE_VEHICLE => "Matrícula",
    BRAND_VEHICLE => "Marca",
    MODEL_VEHICLE => "Modelo",
    COLOR_VEHICLE => "Color",
    DOORS_VEHICLE => "Puertas",
    SEATS_VEHICLE => "Asientos",
    TRANSMISSION_VEHICLE => "Transmisión",
    MOTORIZATION_VEHICLE => "Motorización",
    CLASS_VEHICLE => "Clase",
    BASE_PRICE_VEHICLE => "Precio base"
  ];

  foreach ($requiredFields as $field => $display_field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
      $errors_message[] = "El campo $display_field es obligatorio";
      return false;
    }
  }

  return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST[ACTION_SUBMIT]) && $_POST[ACTION_SUBMIT] === SUBMIT_VEHICLE_REGISTER) {
  $errors_message = [];

  if ($conn == null) {
    $errors_message[] = "No se pudo conectar con la base de datos";
  } elseif (validateVehicleRegisterForm($errors_message)) {
    $vehicle = new Vehicle(
      ucfirst(strtolower($_POST[PLATE_VEHICLE])),
      ucfirst(strtolower($_POST[BRAND_VEHICLE])),
      ucfirst(strtolower($_POST[MODEL_VEHICLE])),
      ucfirst(strtolower($_POST[COLOR_VEHICLE])),
      (int) $_POST[DOORS_VEHICLE],
      (int) $_POST[SEATS_VEHICLE],
      ucfirst(strtolower($_POST[TRANSMISSION_VEHICLE])),
      ucfirst(strtolower($_POST[MOTORIZATION_VEHICLE])),
      ucfirst(strtolower($_POST[CLASS_VEHICLE])),
      (float) $_POST[BASE_PRICE_VEHICLE]
    );

    try {
      registerVehicle($vehicle, $conn);
      $_SESSION['vehicle-success-message'] = "Vehículo añadido correctamente";
    } catch (PDOException $e) {
      error_log($e->getMessage());
      $errors_message[] = "No se pudo añadir el vehículo";
    }
  }

  if (!empty($errors_message)) {
    $_SESSION['vehicle-errors-message'] = $errors_message;
    $_SESSION['vehicle-old-fields'] = [
      PLATE_VEHICLE => $_POST[PLATE_VEHICLE] ?? '',
      BRAND_VEHICLE => $_POST[BRAND_VEHICLE] ?? '',
      MODEL_VEHICLE => $_POST[MODEL_VEHICLE] ?? '',
      COLOR_VEHICLE => $_POST[COLOR_VEHICLE] ?? '',
      DOORS_VEHICLE => $_POST[DOORS_VEHICLE] ?? '',
      SEATS_VEHICLE => $_POST[SEATS_VEHICLE] ?? '',
      TRANSMISSION_VEHICLE => $_POST[TRANSMISSION_VEHICLE] ?? '',
      MOTORIZATION_VEHICLE => $_POST[MOTORIZATION_VEHICLE] ?? '',
      CLASS_VEHICLE => $_POST[CLASS_VEHICLE] ?? '',
      BASE_PRICE_VEHICLE => $_POST[BASE_PRICE_VEHICLE] ?? ''
    ];
  }

  header("Location: admin.php");
  exit;
}
