<?php
session_start();

include_once "pages/register.php";
include_once "pages/login.php";


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
  showLoginForm();
  showRegisterForm();
  ?>
</body>

</html>