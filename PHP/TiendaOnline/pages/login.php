<?php

function login(string $email_login, string $password_login, PDO $conn, array &$errors_message)
{
  $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
  $stmt->execute([$email_login]);
  $user = $stmt->fetch();

  if (!empty($user) && password_verify($password_login, $user[PASSWORD_COLUMN])) {
    $_SESSION["user_id"] = (int) $user["id"];
  }

  $errors_message[] = "El email o la contraseña no son correctos";
}

function showLoginForm()
{
?>
  <h1>LOGIN</h1>
  <form action="main.php" method="POST">
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
?>