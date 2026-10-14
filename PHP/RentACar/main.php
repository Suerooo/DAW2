<?php
session_start();

$errors_message = $_SESSION["errors-message"] ?? [];
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RentACar</title>
</head>

<?php
const DNI_REGISTER = "dni-register";
const NAME_REGISTER = "name-register";
const SURNAME_REGISTER = "surname-register";
const BIRTHDATE_REGISTER = "birthdate-register";
const EMAIL_REGISTER = "email-register";
const PASSWORD_REGISTER = "password-register";

const EMAIL_LOGIN = "email-login";
const PASSWORD_LOGIN = "password-login";

const ACTION_SUBMIT = "action";
const REGISTER_ACTION_SUBMIT = "register-action";
const LOGIN_ACTION_SUBMIT = "login-action";

$server_name = "localhost";
$username = "root";
$password = "";
$db_name = "rentacar";

function registerUser(string $email, string $password, string $role, array &$errors_message, PDO $conn)
{
  if ($role != "admin" && $role != "client") {
    $errors_message[] = "El rol del usuario tiene que ser 'admin' o 'client'";
    return;
  }

  try {
    $stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
    $stmt->execute([$email, password_hash($password, PASSWORD_DEFAULT), $role]);
    echo "si";
  } catch (PDOException $e) {
    echo $e->getMessage();
  }
}

function getIdFromEmail(string $email, string $table, PDO $conn): int
{
  $result = $conn->query("select id from " . $table . " where email = '" . $email . "'");
  $row = $result->fetch();

  if (!$row["id"]) {
    
  }

  return $row["id"];
}

try {
  $conn = new PDO("mysql:host=$server_name;dbname=$db_name", $username, $password);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}
getIdFromEmail("hola@.com", "users", $conn);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST[ACTION_SUBMIT])) {
  $errors_message = [];

  switch ($_POST[ACTION_SUBMIT]) {
    case REGISTER_ACTION_SUBMIT:
      registerUser($_POST[EMAIL_REGISTER], $_POST[PASSWORD_REGISTER], "client", $errors_message, $conn);
      break;

    default:
      break;
  }

  $_SESSION["errors-message"] = $errors_message;

  header("Location: main.php");
  exit;
}
?>

<body>
  <h1>REGISTER</h1>
  <form action="main.php" method="POST">
    <label for="<?= DNI_REGISTER ?>">DNI:</label>
    <input type="text" name="<?= DNI_REGISTER ?>" id="<?= DNI_REGISTER ?>">
    <br>

    <label for="<?= NAME_REGISTER ?>">Nombre:</label>
    <input type="text" name="<?= NAME_REGISTER ?>" id="<?= NAME_REGISTER ?>">
    <br>

    <label for="<?= SURNAME_REGISTER ?>">Apellidos:</label>
    <input type="text" name="<?= SURNAME_REGISTER ?>" id="<?= SURNAME_REGISTER ?>">
    <br>

    <label for="<?= BIRTHDATE_REGISTER ?>">Fecha de nacimiento:</label>
    <input type="date" name="<?= BIRTHDATE_REGISTER ?>" id="<?= BIRTHDATE_REGISTER ?>">
    <br>

    <label for="<?= EMAIL_REGISTER ?>">Email:</label>
    <input type="email" name="<?= EMAIL_REGISTER ?>" id="<?= EMAIL_REGISTER ?>">
    <br>

    <label for="<?= PASSWORD_REGISTER ?>">Contraseña:</label>
    <input type="password" name="<?= PASSWORD_REGISTER ?>" id="<?= PASSWORD_REGISTER ?>">
    <br>

    <button type="submit" name="<?= ACTION_SUBMIT ?>" value="<?= REGISTER_ACTION_SUBMIT ?>">Registrar</button>
  </form>

  <h1>LOGIN</h1>
  <form action="main.php" method="POST">
    <label for="<?= EMAIL_LOGIN ?>">Email:</label>
    <input type="email" name="<?= EMAIL_LOGIN ?>" id="<?= EMAIL_LOGIN ?>">
    <br>

    <label for="<?= PASSWORD_LOGIN ?>">Contraseña:</label>
    <input type="password" name="<?= PASSWORD_LOGIN ?>" id="<?= PASSWORD_LOGIN ?>">
    <br>

    <button type="submit" name="<?= ACTION_SUBMIT ?>" value="<?= LOGIN_ACTION_SUBMIT ?>">Iniciar sesión</button>
  </form>

  <h1>ERRORES</h1>
  <?php
  foreach ($errors_message as $errors) {
    echo $errors;
  }
  ?>
</body>

</html>