<?php
session_start();

if (!isset($_SESSION["login-session"])) {
  $_SESSION["login-session"] = false;
}

$_SESSION["errors-message"] = [];


include_once "class/client.php";
include_once "pages/register.php";
include_once "pages/login.php";
include_once "pages/product.php";

$conn = null;
try {
  $conn = new PDO("mysql:host=" . "localhost" . ";dbname=" . "online_shop" . ";charset=utf8mb4", "root", "");
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  error_log($e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["action"])) {
  switch ($_POST["action"]) {
    case 'register-action':
      $client = new Client(
        $_POST["name-register-fr"],
        $_POST["surname-register-fr"],
        $_POST["email-register-fr"],
        $_POST["password-register-fr"]
      );

      if (registerUser($client, $conn)) {
        $_SESSION["login-session"] = true;
      } else {
        $_SESSION["errors-message"][] = "Email, contraseña, nombre o apellidos inválidos";
      }
      break;

    case 'login-action':
      if (login($_POST["email-login-fr"], $_POST["password-login-fr"], $conn)) {
        $_SESSION["login-session"] = true;
      } else {
        $_SESSION["errors-message"][] = "Email o contraseña incorrectas";
      }
      break;

    case "close-session-action":
      $_SESSION["login-session"] = false;
      break;

    default:
      break;
  }
}

function showErrors()
{
  foreach ($_SESSION["errors-message"] as $error) {
    echo $error;
  }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>

<body>
  <?php
  if (!$_SESSION["login-session"]) {
    showLoginForm();
    showRegisterForm();
  } else {
    showCloseSession();
    
    if (isset($_GET["details-product"])) {
      showDetailsProduct($conn, $_GET["details-product"]);
    } else {
      showProductsOrdered($conn);
    }
  }
  showErrors();
  ?>
</body>

</html>