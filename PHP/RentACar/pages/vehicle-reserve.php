<?php
function validateDates(string $start_date, string $end_date, array &$errors_message, bool $reservation = true): bool
{
  if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
    $errors_message[] = "Las fechas no son válidas";
    return false;
  }

  $start = DateTime::createFromFormat('!Y-m-d', $start_date);
  $end = DateTime::createFromFormat('!Y-m-d', $end_date);

  if (!$start || !$end || $start->format('Y-m-d') !== $start_date || $end->format('Y-m-d') !== $end_date) {
    $errors_message[] = "Las fechas no son válidas";
  } elseif ($end < $start) {
    $errors_message[] = "La fecha final no puede ser anterior a la fecha de inicio";
  } elseif ($reservation && $start_date < date('Y-m-d')) {
    $errors_message[] = "La reserva no puede empezar en una fecha pasada";
  }

  return empty($errors_message);
}

function calculateReservationPrice(float $base_price, string $start_date, string $end_date): array
{
  $days = (new DateTime($start_date))->diff(new DateTime($end_date))->days + 1;
  $discount = 0;

  if ($days > 9) {
    $discount = 10;
  } elseif ($days > 4) {
    $discount = 5;
  }

  $original_price = round($base_price * $days, 2);

  return [
    'days' => $days,
    'original_price' => $original_price,
    'discount' => $discount,
    'final_price' => round($original_price * (100 - $discount) / 100, 2)
  ];
}

function getAvailableVehicles(PDO $conn, string $start_date = '', string $end_date = ''): array
{
  if ($start_date === '' && $end_date === '') {
    $stmt = $conn->query("SELECT * FROM vehicles ORDER BY vehicle_class, brand, model, base_price, plate");
  } else {
    $stmt = $conn->prepare("SELECT * FROM vehicles WHERE NOT EXISTS (SELECT id FROM vehicle_blocks WHERE vehicle_id = vehicles.id AND start_date <= ? AND end_date >= ?) AND NOT EXISTS (SELECT id FROM reservations WHERE vehicle_id = vehicles.id AND state <> 'cancelled' AND start_date <= ? AND end_date >= ?) ORDER BY vehicle_class, brand, model, base_price, plate");
    $stmt->execute([$end_date, $start_date, $end_date, $start_date]);
  }

  return $stmt->fetchAll();
}

function isVehicleAvailable(int $vehicle_id, string $start_date, string $end_date, PDO $conn, bool $lock = false): bool
{
  $lock_query = $lock ? " FOR UPDATE" : "";
  $stmt = $conn->prepare("SELECT id FROM vehicle_blocks WHERE vehicle_id = ? AND start_date <= ? AND end_date >= ? LIMIT 1" . $lock_query);
  $stmt->execute([$vehicle_id, $end_date, $start_date]);

  if ($stmt->fetch()) {
    return false;
  }

  $stmt = $conn->prepare("SELECT id FROM reservations WHERE vehicle_id = ? AND state <> 'cancelled' AND start_date <= ? AND end_date >= ? LIMIT 1" . $lock_query);
  $stmt->execute([$vehicle_id, $end_date, $start_date]);

  return !$stmt->fetch();
}

function reserveVehicle(int $user_id, int $model_id, string $start_date, string $end_date, float $base_price, PDO $conn): void
{
  $errors_message = [];

  if (!validateDates($start_date, $end_date, $errors_message)) {
    throw new RuntimeException($errors_message[0]);
  }

  $conn->beginTransaction();

  try {
    $stmt = $conn->prepare("SELECT id FROM clients WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $client_id = $stmt->fetchColumn();

    if (!$client_id) {
      throw new RuntimeException("No se encontraron los datos del cliente");
    }

    $stmt = $conn->prepare("SELECT * FROM vehicles WHERE id = ?");
    $stmt->execute([$model_id]);
    $model = $stmt->fetch();

    if (!$model || (float) $model[BASE_PRICE_COLUMN] != $base_price) {
      throw new RuntimeException("El precio o el modelo ha cambiado. Consulta de nuevo la disponibilidad");
    }

    $stmt = $conn->prepare("SELECT * FROM vehicles WHERE brand = ? AND model = ? AND vehicle_class = ? AND transmission = ? AND motorization = ? AND doors = ? AND seats = ? AND base_price = ? ORDER BY id FOR UPDATE");
    $stmt->execute([
      $model[BRAND_COLUMN],
      $model[MODEL_COLUMN],
      $model[VEHICLE_CLASS_COLUMN],
      $model[TRANSMISSION_COLUMN],
      $model[MOTORIZATION_COLUMN],
      $model[DOORS_COLUMN],
      $model[SEATS_COLUMN],
      $base_price
    ]);
    $vehicles = $stmt->fetchAll();

    foreach ($vehicles as $vehicle) {
      if (isVehicleAvailable((int) $vehicle[ID_COLUMN], $start_date, $end_date, $conn, true)) {
        $price = calculateReservationPrice((float) $vehicle[BASE_PRICE_COLUMN], $start_date, $end_date);

        if ($price['original_price'] > 99999999.99) {
          throw new RuntimeException("El importe de la reserva supera el máximo permitido");
        }

        $stmt = $conn->prepare("INSERT INTO reservations (client_id, vehicle_id, start_date, end_date, original_price, discount, final_price, state) VALUES (?, ?, ?, ?, ?, ?, ?, 'confirmed')");
        $stmt->execute([$client_id, $vehicle[ID_COLUMN], $start_date, $end_date, $price['original_price'], $price['discount'], $price['final_price']]);
        $conn->commit();
        return;
      }
    }

    throw new RuntimeException("Ya no quedan vehículos disponibles de ese modelo y precio para esas fechas");
  } catch (Throwable $e) {
    $conn->rollBack();
    throw $e;
  }
}

function showAvailabilityForm(string $start_date, string $end_date)
{
?>
  <form method="GET">
    <label for="<?= START_DATE_RESERVATION ?>">Fecha inicio:</label>
    <input type="date" name="<?= START_DATE_RESERVATION ?>" id="<?= START_DATE_RESERVATION ?>" min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($start_date, ENT_QUOTES, 'UTF-8') ?>" required>
    <br>

    <label for="<?= END_DATE_RESERVATION ?>">Fecha final:</label>
    <input type="date" name="<?= END_DATE_RESERVATION ?>" id="<?= END_DATE_RESERVATION ?>" min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($end_date, ENT_QUOTES, 'UTF-8') ?>" required>
    <br>

    <button type="submit">Consultar disponibilidad</button>
  </form>
  <p>El precio base es por día. Se incluyen el día de inicio y el de fin.</p>
<?php
}

function showVehicles(array $vehicles, string $start_date = '', string $end_date = '', bool $client = false)
{
  $vehicles_by_class = [];

  foreach ($vehicles as $vehicle) {
    $id = json_encode([
      $vehicle[BRAND_COLUMN],
      $vehicle[MODEL_COLUMN],
      $vehicle[TRANSMISSION_COLUMN],
      $vehicle[MOTORIZATION_COLUMN],
      $vehicle[DOORS_COLUMN],
      $vehicle[SEATS_COLUMN],
      $vehicle[BASE_PRICE_COLUMN]
    ]);
    $vehicle_class = $vehicle[VEHICLE_CLASS_COLUMN];

    if (!isset($vehicles_by_class[$vehicle_class][$id])) {
      $vehicles_by_class[$vehicle_class][$id] = $vehicle;
      $vehicles_by_class[$vehicle_class][$id]['colors'] = [];
    }

    $vehicles_by_class[$vehicle_class][$id]['colors'][$vehicle[COLOR_COLUMN]] = $vehicle[COLOR_COLUMN];
  }
?>
  <?php if (empty($vehicles_by_class)) { ?>
    <p>Ahora mismo no hay vehículos disponibles para la consulta.</p>
  <?php } ?>

  <?php foreach ($vehicles_by_class as $vehicle_class => $vehicles) { ?>
    <section>
      <h2>Clase <?= htmlspecialchars($vehicle_class, ENT_QUOTES, 'UTF-8') ?></h2>

      <?php foreach ($vehicles as $vehicle) { ?>
        <article>
          <h3><?= htmlspecialchars($vehicle[BRAND_COLUMN] . ' ' . $vehicle[MODEL_COLUMN], ENT_QUOTES, 'UTF-8') ?></h3>
          <ul>
            <li>Colores disponibles: <?= htmlspecialchars(implode(', ', $vehicle['colors']), ENT_QUOTES, 'UTF-8') ?></li>
            <li>Puertas: <?= (int) $vehicle[DOORS_COLUMN] ?></li>
            <li>Asientos: <?= (int) $vehicle[SEATS_COLUMN] ?></li>
            <li>Transmisión: <?= htmlspecialchars($vehicle[TRANSMISSION_COLUMN], ENT_QUOTES, 'UTF-8') ?></li>
            <li>Motorización: <?= htmlspecialchars($vehicle[MOTORIZATION_COLUMN], ENT_QUOTES, 'UTF-8') ?></li>
            <li>Precio base por día: <?= number_format((float) $vehicle[BASE_PRICE_COLUMN], 2, ',', '.') ?>€</li>
          </ul>

          <?php if ($start_date !== '' && $end_date !== '') { ?>
            <?php $price = calculateReservationPrice((float) $vehicle[BASE_PRICE_COLUMN], $start_date, $end_date); ?>
            <p>Duración: <?= $price['days'] ?> días</p>
            <p>Precio sin descuento: <?= number_format($price['original_price'], 2, ',', '.') ?>€</p>
            <p>Descuento: <?= $price['discount'] ?>%</p>
            <p>Precio final: <?= number_format($price['final_price'], 2, ',', '.') ?>€</p>

            <?php if ($client) { ?>
              <form action="client.php" method="POST">
                <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                <input type="hidden" name="<?= MODEL_RESERVATION ?>" value="<?= (int) $vehicle[ID_COLUMN] ?>">
                <input type="hidden" name="<?= BASE_PRICE_VEHICLE ?>" value="<?= $vehicle[BASE_PRICE_COLUMN] ?>">
                <input type="hidden" name="<?= START_DATE_RESERVATION ?>" value="<?= htmlspecialchars($start_date, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="<?= END_DATE_RESERVATION ?>" value="<?= htmlspecialchars($end_date, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" name="<?= ACTION_SUBMIT ?>" value="<?= SUBMIT_RESERVATION ?>">Confirmar reserva</button>
              </form>
            <?php } else { ?>
              <p>Inicia sesión como cliente para reservar.</p>
            <?php } ?>
          <?php } ?>
        </article>
      <?php } ?>
    </section>
  <?php } ?>
<?php
}
