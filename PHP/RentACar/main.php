<?php
session_start();

include_once "class/variable.php";
include_once "class/client.php";
include_once "pages/vehicle-reserve.php";

if (empty($_SESSION['token'])) {
  $_SESSION['token'] = bin2hex(random_bytes(32));
}

$old_fields = $_SESSION['old_fields'] ?? [];
unset($_SESSION['old_fields']);

$errors_message = $_SESSION['errors-message'] ?? [];
unset($_SESSION['errors-message']);

$success_message = $_SESSION['success-message'] ?? '';
unset($_SESSION['success-message']);

function keepFields(string $field): string
{
  global $old_fields;

  return htmlspecialchars(is_string($old_fields[$field] ?? null) ? $old_fields[$field] : '', ENT_QUOTES, 'UTF-8');
}

include_once "pages/register.php";
include_once "pages/login.php";

$conn = null;
try {
  $conn = new PDO("mysql:host=" . SERVER_NAME . ";dbname=" . DB_NAME . ";charset=utf8mb4", USERNAME, PASSWORD_DB);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  error_log($e->getMessage());
  $errors_message[] = "No se pudo conectar con la base de datos";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST[ACTION_SUBMIT])) {


  $errors_message = [];
  $register_completed = false;

  if ($conn == null) {
    $errors_message[] = "No se pudo conectar con la base de datos";
  } elseif (!is_string($_POST['token'] ?? null) || !hash_equals($_SESSION['token'], $_POST['token'])) {
    $errors_message[] = "El formulario ha caducado. Inténtalo de nuevo";
  }

  if (!empty($errors_message)) {
    $_SESSION['errors-message'] = $errors_message;
    header("Location: main.php");
    exit;
  }

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
          $errors_message[] = "No se pudo completar el registro. Comprueba que el email y el DNI no estén registrados";
        }
      }
      break;

    case LOGIN_ACTION_SUBMIT:
      if (!is_string($_POST[EMAIL_LOGIN] ?? null) || !is_string($_POST[PASSWORD_LOGIN] ?? null) || trim($_POST[EMAIL_LOGIN]) === '' || $_POST[PASSWORD_LOGIN] === '') {
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

$vehicles = [];
$catalog_loaded = false;
$start_date = is_string($_GET[START_DATE_RESERVATION] ?? null) ? $_GET[START_DATE_RESERVATION] : '';
$end_date = is_string($_GET[END_DATE_RESERVATION] ?? null) ? $_GET[END_DATE_RESERVATION] : '';
$date_errors = [];
$search = isset($_GET[START_DATE_RESERVATION]) || isset($_GET[END_DATE_RESERVATION]);

if (!$search || validateDates($start_date, $end_date, $date_errors)) {
  if ($conn != null) {
    try {
      $vehicles = getAvailableVehicles($conn, $start_date, $end_date);
      $catalog_loaded = true;
    } catch (PDOException $e) {
      error_log($e->getMessage());
      $errors_message[] = "No se pudo cargar el catálogo de vehículos";
    }
  }
}

$errors_message = array_merge($errors_message, $date_errors);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RentACar</title>
</head>

<body>
  <?php if (isset($_SESSION[USER_ID_COLUMN], $_SESSION[ROLE_COLUMN])) { ?>
    <a href="<?= $_SESSION[ROLE_COLUMN] === 'admin' ? 'pages/admin/admin.php' : 'pages/client.php' ?>">Mi cuenta</a>
  <?php } ?>
  <?php
  showRegisterForm();
  showLoginForm();

  if (!empty($success_message)) {
    echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8');
  }

  if (!empty($errors_message)) {
    foreach ($errors_message as $errors) {
      echo '<p>' . htmlspecialchars($errors, ENT_QUOTES, 'UTF-8') . '</p>';
    }
  }

  ?>
  <header>
    <h1>Vehículos disponibles</h1>
  </header>

  <?php
  showAvailabilityForm($start_date, $end_date);

  if ($catalog_loaded) {
    showVehicles($vehicles, $start_date, $end_date);
  }
  ?>
</body>

</html>
