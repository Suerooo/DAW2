<?php
session_start();

include_once "class/variable.php";
include_once "class/client.php";

$old_fields = $_SESSION['old_fields'] ?? [];
unset($_SESSION['old_fields']);

$errors_message = $_SESSION['errors-message'] ?? [];
unset($_SESSION['errors-message']);

$success_message = $_SESSION['success-message'] ?? '';
unset($_SESSION['success-message']);

function keepFields(string $field): string
{
  global $old_fields;

  return $old_fields[$field] ?? '';
}

include_once "pages/register.php";
include_once "pages/login.php";

$conn = null;
try {
  $conn = new PDO("mysql:host=" . SERVER_NAME . ";dbname=" . DB_NAME . ";charset=utf8mb4", USERNAME, PASSWORD_DB);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  $errors_message[] = "Connection failed: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST[ACTION_SUBMIT])) {


  $errors_message = [];
  $register_completed = false;

  switch ($_POST[ACTION_SUBMIT]) {

    case REGISTER_ACTION_SUBMIT:

      if (validateRegisterForm($errors_message)) {

        $licenseName = null;
        $transaction = false;

        try {
          $conn->beginTransaction();
          $transaction = true;

          $licenseName = saveDriverLicense($_FILES[DRIVER_LICENSE_REGISTER]);

          $user = [
            EMAIL_COLUMN => $_POST[EMAIL_REGISTER],
            PASSWORD_COLUMN => $_POST[PASSWORD_REGISTER],
            ROLE_COLUMN => "client"
          ];

          $userId = registerUser($user, $conn);

          $client = new Client($userId, $_POST[DNI_REGISTER], $_POST[NAME_REGISTER], $_POST[SURNAME_REGISTER], $_POST[BIRTHDATE_REGISTER], $licenseName);

          registerClient($client, $conn);

          $conn->commit();
          $transaction = false;
          $register_completed = true;
          $_SESSION['success-message'] = "Registro completado correctamente";
        } catch (Throwable $e) {
          if ($transaction) {
            $conn->rollBack();
          }

          if ($licenseName != null) {
            unlink(LICENSE_DIR . DIRECTORY_SEPARATOR . $licenseName);
          }

          error_log($e->getMessage());
          $errors_message[] = "No se pudo completar el registro" . $e->getMessage();
        }
      }
      break;

    case LOGIN_ACTION_SUBMIT:
      if (!isset($_POST[EMAIL_LOGIN], $_POST[PASSWORD_LOGIN]) || $_POST[EMAIL_LOGIN] === '' || $_POST[PASSWORD_LOGIN] === '') {
        $errors_message[] = "El email y la contraseña son obligatorios";
      } else {
        login($_POST[EMAIL_LOGIN], $_POST[PASSWORD_LOGIN], $conn, $errors_message);
      }
      break;

    default:
      break;
  }

  if (!$register_completed) {
    $_SESSION['old_fields'] = [
      DNI_REGISTER => $_POST[DNI_REGISTER] ?? '',
      NAME_REGISTER => $_POST[NAME_REGISTER] ?? '',
      SURNAME_REGISTER => $_POST[SURNAME_REGISTER] ?? '',
      BIRTHDATE_REGISTER => $_POST[BIRTHDATE_REGISTER] ?? '',
      EMAIL_REGISTER => $_POST[EMAIL_REGISTER] ?? '',
      EMAIL_LOGIN => $_POST[EMAIL_LOGIN] ?? ''
    ];
  }

  $_SESSION["errors-message"] = $errors_message;

  header("Location: main.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RentACar</title>
</head>

<body>
  <?php
  showRegisterForm();
  showLoginForm();

  if (!empty($success_message)) {
    echo $success_message;
  }

  if (!empty($errors_message)) {
    foreach ($errors_message as $errors) {
      echo $errors;
    }
  }

  include_once __DIR__ . "/class/variable.php";

  $errors_message = [];
  $vehicles_by_class = [];

  try {
    $conn = new PDO("mysql:host=" . SERVER_NAME . ";dbname=" . DB_NAME . ";charset=utf8mb4", USERNAME, PASSWORD_DB);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->query("SELECT * FROM vehicles ORDER BY vehicle_class, brand, model, plate");
    $vehicles = $stmt->fetchAll();


    foreach ($vehicles as $vehicle) {
      $id = strtolower($vehicle[MODEL_COLUMN] . $vehicle[VEHICLE_CLASS_COLUMN] . $vehicle[TRANSMISSION_COLUMN] . $vehicle[MOTORIZATION_COLUMN]);

      $vehicles_by_class[$vehicle[VEHICLE_CLASS_COLUMN]][$id] = $vehicle;
    }
  } catch (PDOException $e) {
    error_log($e->getMessage());
    $errors_message[] = "No se pudo cargar el catálogo de vehículos";
  }
  ?>
  <!DOCTYPE html>
  <html lang="es">

  <body>
    <header>
      <h1>Vehículos disponibles</h1>
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
      <p>Ahora mismo no hay vehículos disponibles</p>
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
</body>

</html>