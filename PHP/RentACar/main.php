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

include_once "logic/register.php";
include_once "logic/login.php";

$conn = null;
try {
  $conn = new PDO("mysql:host=" . SERVER_NAME . ";dbname=" . DB_NAME . ";charset=utf8mb4", USERNAME, PASSWORD_DB);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  $errors_message[] = "Connection failed: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST[ACTION_SUBMIT])) {
  if ($conn == null) {
    $_SESSION["errors-message"] = $errors_message;
    header("Location: main.php");
    exit;
  }

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
?>
</body>

</html>
