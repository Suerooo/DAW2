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

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vehículos disponibles - RentACar</title>
</head>

<body>
  <header>
    <h1>Vehículos disponibles</h1>
    <p>Consulta nuestros vehículos organizados por clase.</p>
    <a href="main.php">Volver al inicio</a>
  </header>

  <?php $vehicles_by_class = $vehicles_by_class ?? []; ?>

  <?php if (!empty($errors_message)) { ?>
    <ul>
      <?php foreach ($errors_message as $error) { ?>
        <li><?= $error ?></li>
      <?php } ?>
    </ul>
  <?php } ?>

  <?php if (empty($vehicles_by_class) && empty($errors_message)) { ?>
    <p>Ahora mismo no hay vehículos disponibles.</p>
  <?php } ?>

  <?php foreach (($vehicles_by_class ?? []) as $vehicle_class => $vehicles) { ?>
    <section>
      <h2>Clase <?= $vehicle_class ?></h2>

      <?php foreach ($vehicles as $vehicle) { ?>
        <article>
          <h3><?= $vehicle[BRAND_COLUMN] . ' ' . $vehicle[MODEL_COLUMN] ?></h3>
          <ul>
            <li>Color: <?= $vehicle[COLOR_COLUMN] ?></li>
            <li>Puertas: <?= (int) $vehicle[DOORS_COLUMN] ?></li>
            <li>Asientos: <?= (int) $vehicle[SEATS_COLUMN] ?></li>
            <li>Transmisión: <?= $vehicle[TRANSMISSION_COLUMN] ?></li>
            <li>Motorización: <?= $vehicle[MOTORIZATION_COLUMN] ?></li>
          </ul>
        </article>
      <?php } ?>
    </section>
  <?php } ?>
</body>

</html>