<?php

function login(string $email_login, string $password_login, PDO $conn): bool
{
  $stmt = $conn->prepare("SELECT * FROM client WHERE email = ?");
  $stmt->execute([$email_login]);
  $user = $stmt->fetch();

  if (!empty($user) && password_verify($password_login, $user["password_hash"])) {
    return true;
  }

  return false;
}

function showLoginForm()
{
?>
  <h1>LOGIN</h1>
  <form action="index.php" method="POST">
    <label for="<?= "email-login-fr" ?>">Email:</label>
    <input type="text" name="<?= "email-login-fr" ?>" id="<?= "email-login-fr" ?>">
    <br>

    <label for="<?= "password-login-fr" ?>">Contraseña:</label>
    <input type="password" name="<?= "password-login-fr" ?>" id="<?= "password-login-fr" ?>">
    <br>

    <button type="submit" name="<?= "action" ?>" value="<?= "login-action" ?>">Iniciar sesión</button>
  </form>
<?php
}

function showCloseSession()
{
?>
  <form action="index.php" method="post">
    <button type="submit" name="<?= "action" ?>" value="<?= "close-session-action" ?>">Cerrar sesión</button>
  </form>
<?php
}
?>