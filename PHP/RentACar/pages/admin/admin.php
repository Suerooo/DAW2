<?php
session_start();

include_once __DIR__ . "/../../class/variable.php";
include_once __DIR__ . "/../../class/vehicle.php";

if (!isset($_SESSION[ROLE_COLUMN]) || $_SESSION[ROLE_COLUMN] !== "admin") {
  header("Location: main.php");
  exit;
}

function keepVehicleFields(string $field): string
{
  global $old_fields;

  return $old_fields[$field] ?? '';
}

$conn = null;

try {
  $conn = new PDO("mysql:host=" . SERVER_NAME . ";dbname=" . DB_NAME . ";charset=utf8mb4", USERNAME, PASSWORD_DB);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  error_log($e->getMessage());
  $errors_message[] = "No se pudo conectar con la base de datos";
}

include_once "vehicle-register.php";
include_once "vehicle-block.php";

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

if ($conn != null) {
  try {
    if (isset($_GET["delete-block"])) {
      deleteVehicleBlock((int) $_GET["delete-block"], $conn);

      header("Location: admin.php");
      exit;
    }
  } catch (PDOException $e) {
    error_log($e->getMessage());
    $errors_message[] = "No se pudo eliminar el bloqueo de ese vehículo";
  }
}

if ($conn != null) {
  try {
    if (isset($_GET["edit-block"])) {
      $vehicle_block = $vehicles_block[$_GET["edit-block"]];
      $vehicle = $vehicles[$vehicle_block["vehicle_id"]];
?>
      <form action="admin.php" method="POST">
        <label for="<?= VEHICLE_BLOCK ?>">Vehículo: </label>
        <select name="<?= VEHICLE_BLOCK ?>" id="<?= VEHICLE_BLOCK ?>" disabled>
          <option value="<?= $vehicle[ID_COLUMN] ?>"><?= $vehicle[PLATE_COLUMN] . " " . $vehicle[VEHICLE_CLASS_COLUMN] . " " . $vehicle[BRAND_COLUMN] . " " . $vehicle[MODEL_COLUMN] ?></option>

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
        <?php var_dump($vehicle_block) ?>
      </form>
<?php
    }
  } catch (PDOException $e) {
    error_log($e->getMessage());
    $errors_message[] = "No se pudo eliminar el bloqueo de ese vehículo";
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
            </tr>
          <?php } ?>
        </tbody>
      </table>
    <?php } ?>
  </section>´
  <section>
    <h2>Bloquear vehículo</h2>

    <form action="admin.php" method="post">
      <label for="<?= VEHICLE_BLOCK ?>">Vehículo: </label>
      <select name="<?= VEHICLE_BLOCK ?>" id="<?= VEHICLE_BLOCK ?> ">
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
            <th colspan="2">Opciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($vehicles_block as $index => $vehicle) { ?>
            <tr>
              <td><?= $vehicles[$vehicle["vehicle_id"]][PLATE_COLUMN] ?></td>
              <td><?= $vehicles[$vehicle["vehicle_id"]][VEHICLE_CLASS_COLUMN] ?></td>
              <td><?= $vehicles[$vehicle["vehicle_id"]][BRAND_COLUMN] ?></td>
              <td><?= $vehicles[$vehicle["vehicle_id"]][MODEL_COLUMN] ?></td>
              <td><?= $vehicle[START_DATE_COLUMN] ?></td>
              <td><?= $vehicle[END_DATE_COLUMN] ?></td>
              <td><?= $vehicle[REASON_COLUMN] ?></td>
              <td><a href="admin.php?edit-block=<?= $vehicles_block[$index]["id"] ?>">Editar</a></td>
              <td><a href="admin.php?delete-block=<?= $vehicles_block[$index]["id"] ?>">Eliminar</a></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    <?php } ?>
  </section>

</body>

</html>