<?php
session_start();


$old_fields = $_SESSION['old_fields'] ?? [];
unset($_SESSION['old_fields']);

$errors_message = $_SESSION['errors-message'] ?? [];
unset($_SESSION['errors-message']);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RentACar</title>
</head>

<?php
include_once "variable.php";

function keepFields(string $field): string
{
  global $old_fields;

  return $old_fields[$field] ?? '';
}

include_once "register.php";
include_once "login.php";
?>

<?php

try {
  $conn = new PDO("mysql:host=" . SERVER_NAME . ";dbname=" . DB_NAME, USERNAME, PASSWORD_DB);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  $errors_message[] = "Connection failed: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST[ACTION_SUBMIT])) {
  $errors_message = [];

  switch ($_POST[ACTION_SUBMIT]) {

    case REGISTER_ACTION_SUBMIT:

      if (validateRegisterForm($errors_message)) {

        $licenseName = null;

        try {
          $licenseName = saveDriverLicense($_FILES[DRIVER_LICENSE_REGISTER]);

          $user = [
            EMAIL_COLUMN => $_POST[EMAIL_REGISTER],
            PASSWORD_COLUMN => $_POST[PASSWORD_REGISTER],
            ROLE_COLUMN => "client"
          ];

          $userId = registerUser($user, $conn);

          $client = [
            USER_ID_COLUMN => $userId,
            DNI_COLUMN => $_POST[DNI_REGISTER],
            NAME_COLUMN => $_POST[NAME_REGISTER],
            SURNAME_COLUMN => $_POST[SURNAME_REGISTER],
            BIRTHDATE_COLUMN => $_POST[BIRTHDATE_REGISTER],
            DRIVER_LICENSE_COLUMN => $licenseName
          ];

          registerClient($client, $conn);
        } catch (Throwable $e) {
          error_log($e->getMessage());
          $errors_message[] = "No se pudo completar el registro";
        }
      }
      break;

    case LOGIN_ACTION_SUBMIT:


      break;

    default:
      break;
  }

  $_SESSION['old_fields'] = [
    DNI_REGISTER => $_POST[DNI_REGISTER] ?? '',
    NAME_REGISTER => $_POST[NAME_REGISTER] ?? '',
    SURNAME_REGISTER => $_POST[SURNAME_REGISTER] ?? '',
    BIRTHDATE_REGISTER => $_POST[BIRTHDATE_REGISTER] ?? '',
    EMAIL_REGISTER => $_POST[EMAIL_REGISTER] ?? ''
  ];
      
  $_SESSION["errors-message"] = $errors_message;

  header("Location: main.php");
  exit;
}
?>

<?php
if (!empty($errors_message)) {
  foreach ($errors_message as $errors) {
    echo $errors;
  }
}
?>
</body>

</html>