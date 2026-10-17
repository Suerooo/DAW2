<?php

function login(string $email_login, string $password_login, PDO $conn)
{

  $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
  $stmt->execute([$email_login]);
  $user = $stmt->fetch();

  if (!empty($user) && password_verify($password_login, $user[PASSWORD_COLUMN])) {

    switch ($user[ROLE_COLUMN]) {
      case 'client':
        header("Location: client.php");
        exit;
      case 'admin':
        header("Location: admin.php");
        exit;
      default:
        header("Location: main.php");
        exit;
    }
  }
}
?>

<h1>LOGIN</h1>
<form action="main.php" method="POST">
  <label for="<?= EMAIL_LOGIN ?>">Email:</label>
  <input type="email" name="<?= EMAIL_LOGIN ?>" id="<?= EMAIL_LOGIN ?>" value="<?= keepFields(EMAIL_LOGIN) ?>">
  <br>

  <label for="<?= PASSWORD_LOGIN ?>">Contraseña:</label>
  <input type="password" name="<?= PASSWORD_LOGIN ?>" id="<?= PASSWORD_LOGIN ?>">
  <br>

  <button type="submit" name="<?= ACTION_SUBMIT ?>" value="<?= LOGIN_ACTION_SUBMIT ?>">Iniciar sesión</button>
</form>