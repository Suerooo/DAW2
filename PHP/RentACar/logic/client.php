<?php
session_start();

include_once __DIR__ . "/../class/variable.php";

if (!isset($_SESSION[ROLE_COLUMN]) || $_SESSION[ROLE_COLUMN] !== "client") {
  header("Location: main.php");
  exit;
}

$errors_message = [];
$vehicles_by_class = [];

try {
  $conn = new PDO("mysql:host=" . SERVER_NAME . ";dbname=" . DB_NAME . ";charset=utf8mb4", USERNAME, PASSWORD_DB);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $stmt = $conn->query("SELECT * FROM vehicles WHERE active = TRUE ORDER BY vehicle_class, brand, model, plate");
  $vehicles = $stmt->fetchAll();

  foreach ($vehicles as $vehicle) {
    $vehicles_by_class[$vehicle[VEHICLE_CLASS_COLUMN]][] = $vehicle;
  }
} catch (PDOException $e) {
  error_log($e->getMessage());
  $errors_message[] = "No se pudo cargar el catálogo de vehículos";
}

include_once __DIR__ . "/../client-view.php";
