<?php

function login(string $email_login, string $password_login, PDO $conn): int
{
  $stmt = $conn->prepare("SELECT id, password_hash FROM client WHERE email = ?");
  $stmt->execute([$email_login]);
  $user = $stmt->fetch();

  if (!empty($user) && password_verify($password_login, $user["password_hash"])) {
    return (int) $user["id"];
  }

  return 0;
}

function showLoginForm()
{
?>
  <section class="panel">
    <h2>Iniciar sesión</h2>
    <form action="index.php?view=account" method="POST" class="form-fields">
      <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">

      <label for="email-login-fr">Correo electrónico</label>
      <input type="email" name="email-login-fr" id="email-login-fr" maxlength="190" autocomplete="username" required>

      <label for="password-login-fr">Contraseña</label>
      <input type="password" name="password-login-fr" id="password-login-fr" autocomplete="current-password" required>

      <button type="submit" name="action" value="login-action">Entrar</button>
    </form>
  </section>
<?php
}

function showCloseSession()
{
?>
  <form action="index.php" method="post">
    <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
    <button type="submit" name="action" value="close-session-action">Cerrar sesión</button>
  </form>
<?php
}
?>
