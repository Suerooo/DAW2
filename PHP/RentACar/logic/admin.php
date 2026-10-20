<?php
session_start();

include_once __DIR__ . "/../class/variable.php";
include_once __DIR__ . "/../class/vehicle.php";

if (!isset($_SESSION[ROLE_COLUMN]) || $_SESSION[ROLE_COLUMN] !== "admin") {
  header("Location: main.php");
  exit;
}

$old_fields = $_SESSION['vehicle-old-fields'] ?? [];
unset($_SESSION['vehicle-old-fields']);

$errors_message = $_SESSION['vehicle-errors-message'] ?? [];
unset($_SESSION['vehicle-errors-message']);

$success_message = $_SESSION['vehicle-success-message'] ?? '';
unset($_SESSION['vehicle-success-message']);

function keepVehicleFields(string $field): string
{
  global $old_fields;

  return $old_fields[$field] ?? '';
}

function validateVehicleForm(array &$errors_message): bool
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

function getVehicles(PDO $conn): array
{
  $stmt = $conn->query("SELECT * FROM vehicles ORDER BY vehicle_class, brand, model, plate");

  return $stmt->fetchAll();
}

$conn = null;

try {
  $conn = new PDO("mysql:host=" . SERVER_NAME . ";dbname=" . DB_NAME . ";charset=utf8mb4", USERNAME, PASSWORD_DB);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  error_log($e->getMessage());
  $errors_message[] = "No se pudo conectar con la base de datos";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST[ACTION_SUBMIT]) && $_POST[ACTION_SUBMIT] === VEHICLE_ACTION_SUBMIT) {
  $errors_message = [];

  if ($conn == null) {
    $errors_message[] = "No se pudo conectar con la base de datos";
  } elseif (validateVehicleForm($errors_message)) {
    $vehicle = new Vehicle(
      $_POST[PLATE_VEHICLE],
      $_POST[BRAND_VEHICLE],
      $_POST[MODEL_VEHICLE],
      $_POST[COLOR_VEHICLE],
      (int) $_POST[DOORS_VEHICLE],
      (int) $_POST[SEATS_VEHICLE],
      $_POST[TRANSMISSION_VEHICLE],
      $_POST[MOTORIZATION_VEHICLE],
      $_POST[CLASS_VEHICLE],
      $_POST[BASE_PRICE_VEHICLE]
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

$vehicles = [];

if ($conn != null) {
  try {
    $vehicles = getVehicles($conn);
  } catch (PDOException $e) {
    error_log($e->getMessage());
    $errors_message[] = "No se pudo cargar la lista de vehículos";
  }
}

include_once __DIR__ . "/../admin-view.php";
