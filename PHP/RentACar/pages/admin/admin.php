<?php
session_start();

include_once __DIR__ . "/../../class/variable.php";
include_once __DIR__ . "/../../class/vehicle.php";
include_once __DIR__ . "/../vehicle-reserve.php";

if (!isset($_SESSION[USER_ID_COLUMN], $_SESSION[ROLE_COLUMN]) || $_SESSION[ROLE_COLUMN] !== "admin") {
  header("Location: ../../main.php");
  exit;
}

if (empty($_SESSION['token'])) {
  $_SESSION['token'] = bin2hex(random_bytes(32));
}

$old_fields = $_SESSION['vehicle-old-fields'] ?? [];
unset($_SESSION['vehicle-old-fields']);

$errors_message = $_SESSION['vehicle-errors-message'] ?? [];
unset($_SESSION['vehicle-errors-message']);

$success_message = $_SESSION['vehicle-success-message'] ?? '';
unset($_SESSION['vehicle-success-message']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!is_string($_POST['token'] ?? null) || !hash_equals($_SESSION['token'], $_POST['token']))) {
  $_SESSION['vehicle-errors-message'] = ["El formulario ha caducado. Inténtalo de nuevo"];
  header("Location: admin.php");
  exit;
}

function keepVehicleFields(string $field): string
{
  global $old_fields;

  return htmlspecialchars(is_string($old_fields[$field] ?? null) ? $old_fields[$field] : '', ENT_QUOTES, 'UTF-8');
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

$vehicle_block = null;

if (isset($_GET['edit-block'])) {
  $block_id = is_string($_GET['edit-block']) && ctype_digit($_GET['edit-block']) ? (int) $_GET['edit-block'] : 0;
  $vehicle_block = $vehicles_block[$block_id] ?? null;

  if ($vehicle_block == null) {
    $errors_message[] = "No se encontró el cierre";
  } elseif (empty($old_fields)) {
    $old_fields = [
      VEHICLE_BLOCK => (string) $vehicle_block['vehicle_id'],
      START_DATE_BLOCK => $vehicle_block[START_DATE_COLUMN],
      END_DATE_BLOCK => $vehicle_block[END_DATE_COLUMN],
      REASON_BLOCK => $vehicle_block[REASON_COLUMN] ?? ''
    ];
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
    <a href="../../main.php">Volver al inicio</a>
    <form action="../logout.php" method="POST">
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

  <section>
    <h2>Añadir vehículo</h2>

    <form action="admin.php" method="POST">
      <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
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
      <select name="<?= COLOR_VEHICLE ?>" id="<?= COLOR_VEHICLE ?>" required>
        <option value="">Selecciona un color</option>
        <?php foreach (['blanco', 'negro', 'gris', 'plata', 'rojo', 'azul', 'verde', 'amarillo', 'otro'] as $color) { ?>
          <option value="<?= $color ?>" <?php if (keepVehicleFields(COLOR_VEHICLE) === $color) { echo 'selected'; } ?>><?= ucfirst($color) ?></option>
        <?php } ?>
      </select>
      <br>

      <label for="<?= DOORS_VEHICLE ?>">Puertas:</label>
      <input type="number" name="<?= DOORS_VEHICLE ?>" id="<?= DOORS_VEHICLE ?>" min="2" max="5" value="<?= keepVehicleFields(DOORS_VEHICLE) ?>" required>
      <br>

      <label for="<?= SEATS_VEHICLE ?>">Asientos:</label>
      <input type="number" name="<?= SEATS_VEHICLE ?>" id="<?= SEATS_VEHICLE ?>" min="2" max="9" value="<?= keepVehicleFields(SEATS_VEHICLE) ?>" required>
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
        <option value="">Selecciona una motorización</option>
        <option value="gasolina" <?php if (keepVehicleFields(MOTORIZATION_VEHICLE) == 'gasolina') {
                                    echo 'selected';
                                  } ?>>Gasolina</option>
        <option value="diesel" <?php if (keepVehicleFields(MOTORIZATION_VEHICLE) == 'diesel') {
                                  echo 'selected';
                                } ?>>Diésel</option>
        <option value="hibrido" <?php if (keepVehicleFields(MOTORIZATION_VEHICLE) == 'hibrido') {
                                  echo 'selected';
                                } ?>>Híbrido</option>
        <option value="hibrido_enchufable" <?php if (keepVehicleFields(MOTORIZATION_VEHICLE) == 'hibrido_enchufable') {
                                              echo 'selected';
                                            } ?>>Híbrido enchufable</option>
        <option value="electrico" <?php if (keepVehicleFields(MOTORIZATION_VEHICLE) == 'electrico') {
                                    echo 'selected';
                                  } ?>>Eléctrico</option>
        <option value="otro" <?php if (keepVehicleFields(MOTORIZATION_VEHICLE) == 'otro') {
                                echo 'selected';
                              } ?>>Otro</option>
      </select>
      <br>

      <label for="<?= CLASS_VEHICLE ?>">Clase:</label>
      <select name="<?= CLASS_VEHICLE ?>" id="<?= CLASS_VEHICLE ?>" required>
        <option value="">Selecciona una clase</option>
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
        <?php foreach (['e', 'f', 'g'] as $vehicle_class) { ?>
          <option value="<?= $vehicle_class ?>" <?php if (keepVehicleFields(CLASS_VEHICLE) === $vehicle_class) { echo 'selected'; } ?>><?= strtoupper($vehicle_class) ?></option>
        <?php } ?>
      </select>
      <br>

      <label for="<?= BASE_PRICE_VEHICLE ?>">Precio base:</label>
      <input type="number" name="<?= BASE_PRICE_VEHICLE ?>" id="<?= BASE_PRICE_VEHICLE ?>" min="0.01" max="99999999.99" step="0.01" value="<?= keepVehicleFields(BASE_PRICE_VEHICLE) ?>" required>
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
              <td><?= htmlspecialchars($vehicle[PLATE_COLUMN], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($vehicle[VEHICLE_CLASS_COLUMN], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($vehicle[BRAND_COLUMN], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($vehicle[MODEL_COLUMN], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($vehicle[COLOR_COLUMN], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= (int) $vehicle[DOORS_COLUMN] ?></td>
              <td><?= (int) $vehicle[SEATS_COLUMN] ?></td>
              <td><?= htmlspecialchars($vehicle[TRANSMISSION_COLUMN], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($vehicle[MOTORIZATION_COLUMN], ENT_QUOTES, 'UTF-8') ?></td>
              <td>
                <form action="admin.php" method="POST">
                  <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                  <input type="hidden" name="vehicle-id" value="<?= (int) $vehicle[ID_COLUMN] ?>">
                  <input type="number" name="<?= BASE_PRICE_VEHICLE ?>" aria-label="Precio base de <?= htmlspecialchars($vehicle[PLATE_COLUMN], ENT_QUOTES, 'UTF-8') ?>" min="0.01" max="99999999.99" step="0.01" value="<?= $vehicle[BASE_PRICE_COLUMN] ?>" required>€
                  <button type="submit" name="<?= ACTION_SUBMIT ?>" value="<?= SUBMIT_VEHICLE_PRICE ?>">Guardar precio</button>
                </form>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    <?php } ?>
  </section>
  <section>
    <h2><?= $vehicle_block == null ? 'Bloquear vehículo' : 'Editar cierre' ?></h2>

    <form action="admin.php" method="post">
      <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
      <?php if ($vehicle_block != null) { ?>
        <input type="hidden" name="block-id" value="<?= (int) $vehicle_block[ID_COLUMN] ?>">
      <?php } ?>
      <label for="<?= VEHICLE_BLOCK ?>">Vehículo: </label>
      <select name="<?= VEHICLE_BLOCK ?>" id="<?= VEHICLE_BLOCK ?>" required>
        <option value="">Selecciona una opción</option>
        <?php foreach ($vehicles as $vehicle) { ?>
          <option value="<?= $vehicle[ID_COLUMN] ?>" <?php if (keepVehicleFields(VEHICLE_BLOCK) === (string) $vehicle[ID_COLUMN]) { echo 'selected'; } ?>><?= htmlspecialchars($vehicle[PLATE_COLUMN] . " " . $vehicle[VEHICLE_CLASS_COLUMN] . " " . $vehicle[BRAND_COLUMN] . " " . $vehicle[MODEL_COLUMN], ENT_QUOTES, 'UTF-8') ?></option>
        <?php } ?>

      </select>
      <br>

      <label for="<?= START_DATE_BLOCK ?>">Fecha inicio: </label>
      <input type="date" name="<?= START_DATE_BLOCK ?>" id="<?= START_DATE_BLOCK ?>" value="<?= keepVehicleFields(START_DATE_BLOCK) ?>" required>
      <br>

      <label for="<?= END_DATE_BLOCK ?>">Fecha final: </label>
      <input type="date" name="<?= END_DATE_BLOCK ?>" id="<?= END_DATE_BLOCK ?>" value="<?= keepVehicleFields(END_DATE_BLOCK) ?>" required>
      <br>

      <label for="<?= REASON_BLOCK ?>">Razón: </label>
      <input type="text" name="<?= REASON_BLOCK ?>" id="<?= REASON_BLOCK ?>" maxlength="255" value="<?= keepVehicleFields(REASON_BLOCK) ?>" required>
      <br>

      <button type="submit" name="<?= ACTION_SUBMIT ?>" value="<?= $vehicle_block == null ? SUBMIT_VEHICLE_BLOCK : SUBMIT_VEHICLE_BLOCK_EDIT ?>"><?= $vehicle_block == null ? 'Bloquear vehículo' : 'Guardar cierre' ?></button>
      <?php if ($vehicle_block != null) { ?>
        <a href="admin.php">Volver sin guardar</a>
      <?php } ?>
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
              <td><?= htmlspecialchars($vehicles[$vehicle["vehicle_id"]][PLATE_COLUMN], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($vehicles[$vehicle["vehicle_id"]][VEHICLE_CLASS_COLUMN], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($vehicles[$vehicle["vehicle_id"]][BRAND_COLUMN], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($vehicles[$vehicle["vehicle_id"]][MODEL_COLUMN], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= $vehicle[START_DATE_COLUMN] ?></td>
              <td><?= $vehicle[END_DATE_COLUMN] ?></td>
              <td><?= htmlspecialchars($vehicle[REASON_COLUMN] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
              <td><a href="admin.php?edit-block=<?= $vehicles_block[$index]["id"] ?>">Editar</a></td>
              <td>
                <form action="admin.php" method="POST">
                  <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                  <input type="hidden" name="block-id" value="<?= (int) $vehicle[ID_COLUMN] ?>">
                  <button type="submit" name="<?= ACTION_SUBMIT ?>" value="<?= SUBMIT_VEHICLE_BLOCK_DELETE ?>">Eliminar</button>
                </form>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    <?php } ?>
  </section>

</body>

</html>
