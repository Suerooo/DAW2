<?php
if (!defined('ROLE_COLUMN') || !isset($_SESSION[ROLE_COLUMN]) || $_SESSION[ROLE_COLUMN] !== "admin") {
  header("Location: ../../main.php");
  exit;
}

function getVehicles(PDO $conn): array
{
  $stmt = $conn->query("SELECT * FROM vehicles ORDER BY vehicle_class, brand, model, plate");

  return array_column($stmt->fetchAll(), null, ID_COLUMN);
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
    if (!is_string($_POST[$field] ?? null) || trim($_POST[$field]) === '') {
      $errors_message[] = "El campo $display_field es obligatorio";
      return false;
    }
  }

  foreach ([PLATE_VEHICLE => 15, BRAND_VEHICLE => 80, MODEL_VEHICLE => 80] as $field => $length) {
    $_POST[$field] = trim($_POST[$field]);

    if (mb_strlen($_POST[$field]) > $length) {
      $errors_message[] = "El campo " . $requiredFields[$field] . " es demasiado largo";
    }
  }

  if (!in_array($_POST[DOORS_VEHICLE], ['2', '3', '4', '5'], true) || filter_var($_POST[SEATS_VEHICLE], FILTER_VALIDATE_INT, ['options' => ['min_range' => 2, 'max_range' => 9]]) === false) {
    $errors_message[] = "Las puertas deben ser 2, 3, 4 o 5 y los asientos entre 2 y 9";
  }

  if (!in_array($_POST[COLOR_VEHICLE], ['blanco', 'negro', 'gris', 'plata', 'rojo', 'azul', 'verde', 'amarillo', 'otro'], true) || !in_array($_POST[TRANSMISSION_VEHICLE], ['manual', 'automatic'], true) || !in_array($_POST[MOTORIZATION_VEHICLE], ['gasolina', 'diesel', 'hibrido', 'hibrido_enchufable', 'electrico', 'otro'], true) || !in_array(strtoupper($_POST[CLASS_VEHICLE]), ['A', 'B', 'C', 'D', 'E', 'F', 'G'], true)) {
    $errors_message[] = "El color, la transmisión, la motorización o la clase no son válidos";
  }

  if (!validateVehiclePrice($_POST[BASE_PRICE_VEHICLE])) {
    $errors_message[] = "El precio base debe ser positivo y tener como máximo dos decimales";
  }

  return empty($errors_message);
}

function validateVehiclePrice(string $base_price): bool
{
  return preg_match('/^\d{1,8}(\.\d{1,2})?$/', $base_price) && (float) $base_price > 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST[ACTION_SUBMIT] ?? '') === SUBMIT_VEHICLE_PRICE) {
  $errors_message = [];

  if ($conn == null) {
    $errors_message[] = "No se pudo conectar con la base de datos";
  } elseif (!is_string($_POST['vehicle-id'] ?? null) || !ctype_digit($_POST['vehicle-id']) || !is_string($_POST[BASE_PRICE_VEHICLE] ?? null) || !validateVehiclePrice($_POST[BASE_PRICE_VEHICLE])) {
    $errors_message[] = "Selecciona un vehículo y un precio positivo con un máximo de dos decimales";
  } else {
    try {
      $stmt = $conn->prepare("SELECT id FROM vehicles WHERE id = ?");
      $stmt->execute([(int) $_POST['vehicle-id']]);

      if ($stmt->fetch()) {
        $stmt = $conn->prepare("UPDATE vehicles SET base_price = ? WHERE id = ?");
        $stmt->execute([$_POST[BASE_PRICE_VEHICLE], (int) $_POST['vehicle-id']]);
        $_SESSION['vehicle-success-message'] = "Precio base modificado correctamente";
      } else {
        $errors_message[] = "No se encontró el vehículo";
      }
    } catch (PDOException $e) {
      error_log($e->getMessage());
      $errors_message[] = "No se pudo modificar el precio base";
    }
  }

  $_SESSION['vehicle-errors-message'] = $errors_message;
  header("Location: admin.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST[ACTION_SUBMIT]) && $_POST[ACTION_SUBMIT] === SUBMIT_VEHICLE_REGISTER) {
  $errors_message = [];

  if ($conn == null) {
    $errors_message[] = "No se pudo conectar con la base de datos";
  } elseif (validateVehicleRegisterForm($errors_message)) {
    $vehicle = new Vehicle(
      strtoupper($_POST[PLATE_VEHICLE]),
      ucfirst(strtolower($_POST[BRAND_VEHICLE])),
      ucfirst(strtolower($_POST[MODEL_VEHICLE])),
      $_POST[COLOR_VEHICLE],
      (int) $_POST[DOORS_VEHICLE],
      (int) $_POST[SEATS_VEHICLE],
      $_POST[TRANSMISSION_VEHICLE],
      $_POST[MOTORIZATION_VEHICLE],
      strtoupper($_POST[CLASS_VEHICLE]),
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
