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

function getVehiclesBlock(PDO $conn): array
{
  $stmt = $conn->query("SELECT * FROM vehicle_blocks");

  return $stmt->fetchAll();
}

function addVehicleBlock(int $vehicle_id, string $start_date, string $end_date, string $reason, PDO $conn)
{
  $stmt = $conn->prepare("INSERT INTO vehicle_blocks (vehicle_id, start_date, end_date, reason) values (?, ?, ?, ?)");
  $stmt->execute([$vehicle_id, $start_date, $end_date, $reason]);
}

$conn = null;

try {
  $conn = new PDO("mysql:host=" . SERVER_NAME . ";dbname=" . DB_NAME . ";charset=utf8mb4", USERNAME, PASSWORD_DB);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  error_log($e->getMessage());
  $errors_message[] = "No se pudo conectar con la base de datos";
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

$vehicles = [];

if ($conn != null) {
  try {
    $vehicles = getVehicles($conn);
  } catch (PDOException $e) {
    error_log($e->getMessage());
    $errors_message[] = "No se pudo cargar la lista de vehículos";
  }
}

$vehicles_block = [];

if ($conn != null) {
  try {
    $vehicles_block = getVehiclesBlock($conn);
  } catch (PDOException $e) {
    error_log($e->getMessage());
    $errors_message[] = "No se pudo cargar la lista de vehículos bloqueados";
  }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestor de vehículos - RentACar</title>
</head>

<body>
  <header>
    <h1>Gestor de vehículos</h1>
    <a href="../main.php">Volver al inicio</a>
  </header>

  <?php if (!empty($success_message)) { ?>
    <p><?= $success_message ?></p>
  <?php } ?>

  <?php if (!empty($errors_message)) { ?>
    <ul>
      <?php foreach ($errors_message as $error) { ?>
        <li><?= $error ?></li>
      <?php } ?>
    </ul>
  <?php } ?>

  <section>
    <h2>Añadir vehículo</h2>

    <form action="admin.php" method="POST">
      <label for="<?= PLATE_VEHICLE ?>">Matrícula:</label>
      <input type="text" name="<?= PLATE_VEHICLE ?>" id="<?= PLATE_VEHICLE ?>" maxlength="15" value="<?= keepVehicleFields(PLATE_VEHICLE) ?>" required>
      <br>

      <label for="<?= BRAND_VEHICLE ?>">Marca:</label>
      <input type="text" name="<?= BRAND_VEHICLE ?>" id="<?= BRAND_VEHICLE ?>" maxlength="80" value="<?= keepVehicleFields(BRAND_VEHICLE) ?>" required>
      <br>

      <label for="<?= MODEL_VEHICLE ?>">Modelo:</label>
      <input type="text" name="<?= MODEL_VEHICLE ?>" id="<?= MODEL_VEHICLE ?>" maxlength="80" value="<?= keepVehicleFields(MODEL_VEHICLE) ?>" required>
      <br>

      <label for="<?= COLOR_VEHICLE ?>">Color:</label>
      <input type="color" name="<?= COLOR_VEHICLE ?>" id="<?= COLOR_VEHICLE ?>" maxlength="50" value="<?= keepVehicleFields(COLOR_VEHICLE) ?>" required>
      <br>

      <label for="<?= DOORS_VEHICLE ?>">Puertas:</label>
      <input type="number" name="<?= DOORS_VEHICLE ?>" id="<?= DOORS_VEHICLE ?>" min="1" max="10" value="<?= keepVehicleFields(DOORS_VEHICLE) ?>" required>
      <br>

      <label for="<?= SEATS_VEHICLE ?>">Asientos:</label>
      <input type="number" name="<?= SEATS_VEHICLE ?>" id="<?= SEATS_VEHICLE ?>" min="1" max="20" value="<?= keepVehicleFields(SEATS_VEHICLE) ?>" required>
      <br>

      <label for="<?= TRANSMISSION_VEHICLE ?>">Transmisión:</label>
      <select name="<?= TRANSMISSION_VEHICLE ?>" id="<?= TRANSMISSION_VEHICLE ?>" required>
        <option value="">Selecciona una transmisión</option>
        <option value="manual" <?php if (keepVehicleFields(TRANSMISSION_VEHICLE) == 'manual') {
                                  echo 'selected';
                                } ?>>Manual</option>
        <option value="automatic" <?php if (keepVehicleFields(TRANSMISSION_VEHICLE) == 'automatic') {
                                    echo 'selected';
                                  } ?>>Automática</option>
      </select>
      <br>

      <label for="<?= MOTORIZATION_VEHICLE ?>">Motorización:</label>
      <select name="<?= MOTORIZATION_VEHICLE ?>" id="<?= MOTORIZATION_VEHICLE ?>" required>
        <option value="">Selecciona una transmisión</option>
        <option value="gasoline" <?php if (keepVehicleFields(MOTORIZATION_VEHICLE) == 'gasoline') {
                                    echo 'selected';
                                  } ?>>Gasolina</option>
        <option value="diesel" <?php if (keepVehicleFields(MOTORIZATION_VEHICLE) == 'diesel') {
                                  echo 'selected';
                                } ?>>Diésel</option>
        <option value="hybrid" <?php if (keepVehicleFields(MOTORIZATION_VEHICLE) == 'hybrid') {
                                  echo 'selected';
                                } ?>>Híbrido</option>
        <option value="electric" <?php if (keepVehicleFields(MOTORIZATION_VEHICLE) == 'electric') {
                                    echo 'selected';
                                  } ?>>Eléctrico</option>
      </select>
      <br>

      <label for="<?= CLASS_VEHICLE ?>">Clase:</label>
      <select name="<?= CLASS_VEHICLE ?>" id="<?= CLASS_VEHICLE ?>" required>
        <option value="">Selecciona una transmisión</option>
        <option value="a" <?php if (keepVehicleFields(CLASS_VEHICLE) == 'a') {
                            echo 'selected';
                          } ?>>A</option>
        <option value="b" <?php if (keepVehicleFields(CLASS_VEHICLE) == 'b') {
                            echo 'selected';
                          } ?>>B</option>
        <option value="c" <?php if (keepVehicleFields(CLASS_VEHICLE) == 'c') {
                            echo 'selected';
                          } ?>>C</option>
        <option value="d" <?php if (keepVehicleFields(CLASS_VEHICLE) == 'd') {
                            echo 'selected';
                          } ?>>D</option>
      </select>
      <br>

      <label for="<?= BASE_PRICE_VEHICLE ?>">Precio base:</label>
      <input type="number" name="<?= BASE_PRICE_VEHICLE ?>" id="<?= BASE_PRICE_VEHICLE ?>" min="0" value="<?= keepVehicleFields(BASE_PRICE_VEHICLE) ?>" required>
      <br>

      <button type="submit" name="<?= ACTION_SUBMIT ?>" value="<?= SUBMIT_VEHICLE_REGISTER ?>">Añadir vehículo</button>
    </form>
  </section>

  <section>
    <h2>Vehículos añadidos</h2>

    <?php if (empty($vehicles)) { ?>
      <p>Todavía no hay vehículos añadidos.</p>
    <?php } else { ?>
      <table border="1">
        <thead>
          <tr>
            <th>Matrícula</th>
            <th>Clase</th>
            <th>Marca</th>
            <th>Modelo</th>
            <th>Color</th>
            <th>Puertas</th>
            <th>Asientos</th>
            <th>Transmisión</th>
            <th>Motorización</th>
            <th>Precio base</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($vehicles as $vehicle) { ?>
            <tr>
              <td><?= $vehicle[PLATE_COLUMN] ?></td>
              <td><?= $vehicle[VEHICLE_CLASS_COLUMN] ?></td>
              <td><?= $vehicle[BRAND_COLUMN] ?></td>
              <td><?= $vehicle[MODEL_COLUMN] ?></td>
              <td><?= $vehicle[COLOR_COLUMN] ?></td>
              <td><?= (int) $vehicle[DOORS_COLUMN] ?></td>
              <td><?= (int) $vehicle[SEATS_COLUMN] ?></td>
              <td><?= $vehicle[TRANSMISSION_COLUMN] ?></td>
              <td><?= $vehicle[MOTORIZATION_COLUMN] ?></td>
              <td><?= $vehicle[BASE_PRICE_COLUMN] ?>€</td>
              <td>
                <?php
                if ($vehicle[ACTIVE_COLUMN]) {
                  echo "Activo";
                } else {
                  echo "Inactivo";
                }
                ?>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    <?php } ?>
  </section>
  <section>
    <h2>Bloquear vehículo</h2>

    <form action="admin.php" method="post">
      <label for="<?= VEHICLE_BLOCK ?>">Vehículo: </label>
      <select name="<?= VEHICLE_BLOCK ?>" id="<?= VEHICLE_BLOCK ?>">
        <option value="" selected>Selecciona una opción</option>
        <?php foreach ($vehicles as $vehicle) { ?>
          <option value="<?= $vehicle[ID_COLUMN] ?>"><?= $vehicle[PLATE_COLUMN] . " " . $vehicle[VEHICLE_CLASS_COLUMN] . " " . $vehicle[BRAND_COLUMN] . " " . $vehicle[MODEL_COLUMN] ?></option>
        <?php } ?>

      </select>
      <br>

      <label for="<?= START_DATE_BLOCK ?>">Fecha inicio: </label>
      <input type="date" name="<?= START_DATE_BLOCK ?>" id="<?= START_DATE_BLOCK ?>">
      <br>

      <label for="<?= END_DATE_BLOCK ?>">Fecha final: </label>
      <input type="date" name="<?= END_DATE_BLOCK ?>" id="<?= END_DATE_BLOCK ?>">
      <br>

      <label for="<?= REASON_BLOCK ?>">Razón: </label>
      <input type="text" name="<?= REASON_BLOCK ?>" id="<?= REASON_BLOCK ?>">
      <br>

      <button type="submit" name="<?= ACTION_SUBMIT ?>" value="<?= SUBMIT_VEHICLE_BLOCK ?>">Bloquear vehículo</button>
    </form>
  </section>

  <section>
    <h2>Vehículos bloqueados</h2>
    <?php if (empty($vehicles_block)) { ?>
      <p>No hay vehículos bloqueados</p>
    <?php } else { ?>
      <table border="1">
        <thead>
          <tr>
            <th>Matrícula</th>
            <th>Clase</th>
            <th>Marca</th>
            <th>Modelo</th>
            <th>Fecha inicio</th>
            <th>Fecha final</th>
            <th>Razón</th>
            <th>Opciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($vehicles_block as $vehicle) { ?>
            <tr>
              <td><?= $vehicles[$vehicle["vehicle_id"]][PLATE_COLUMN] ?></td>
              <td><?= $vehicles[$vehicle["vehicle_id"]][VEHICLE_CLASS_COLUMN] ?></td>
              <td><?= $vehicles[$vehicle["vehicle_id"]][BRAND_COLUMN] ?></td>
              <td><?= $vehicles[$vehicle["vehicle_id"]][MODEL_COLUMN] ?></td>
              <td><?= $vehicle[START_DATE_COLUMN] ?></td>
              <td><?= $vehicle[END_DATE_COLUMN] ?></td>
              <td><?= $vehicle[REASON_COLUMN] ?></td>
              <td><a href="">Eliminar</a></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    <?php } ?>
</body>

</html>