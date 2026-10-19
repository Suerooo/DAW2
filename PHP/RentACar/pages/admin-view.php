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
    <a href="main.php">Volver al inicio</a>
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
      <input type="text" name="<?= COLOR_VEHICLE ?>" id="<?= COLOR_VEHICLE ?>" maxlength="50" value="<?= keepVehicleFields(COLOR_VEHICLE) ?>" required>
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
        <option value="manual" <?php if (keepVehicleFields(TRANSMISSION_VEHICLE) == 'manual') { echo 'selected'; } ?>>Manual</option>
        <option value="automatic" <?php if (keepVehicleFields(TRANSMISSION_VEHICLE) == 'automatic') { echo 'selected'; } ?>>Automática</option>
      </select>
      <br>

      <label for="<?= MOTORIZATION_VEHICLE ?>">Motorización:</label>
      <input type="text" name="<?= MOTORIZATION_VEHICLE ?>" id="<?= MOTORIZATION_VEHICLE ?>" maxlength="50" placeholder="Gasolina, diésel, híbrido..." value="<?= keepVehicleFields(MOTORIZATION_VEHICLE) ?>" required>
      <br>

      <label for="<?= CLASS_VEHICLE ?>">Clase:</label>
      <input type="text" name="<?= CLASS_VEHICLE ?>" id="<?= CLASS_VEHICLE ?>" maxlength="1" pattern="[A-Za-z]" placeholder="A, B, C..." value="<?= keepVehicleFields(CLASS_VEHICLE) ?>" required>
      <br>

      <button type="submit" name="<?= ACTION_SUBMIT ?>" value="<?= VEHICLE_ACTION_SUBMIT ?>">Añadir vehículo</button>
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
</body>

</html>
