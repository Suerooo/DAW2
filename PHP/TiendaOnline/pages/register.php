<?php
function registerUser(Client $client, PDO $conn): int
{
  $stmt = $conn->prepare("INSERT INTO client (email, password_hash, name, surname) VALUES (?, ?, ?, ?)");
  $stmt->execute([
    $client->getEmail(),
    password_hash($client->getPassword(), PASSWORD_DEFAULT),
    $client->getName(),
    $client->getSurname()
  ]);

  return (int) $conn->lastInsertId();
}

function showRegisterForm()
{
?>
  <section class="panel">
    <h2>Crear una cuenta</h2>
    <form action="index.php?view=account" method="POST" class="form-fields">
      <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">

      <label for="name-register-fr">Nombre</label>
      <input type="text" name="name-register-fr" id="name-register-fr" maxlength="100" autocomplete="given-name" required>

      <label for="surname-register-fr">Apellidos</label>
      <input type="text" name="surname-register-fr" id="surname-register-fr" maxlength="150" autocomplete="family-name" required>

      <label for="email-register-fr">Correo electrónico</label>
      <input type="email" name="email-register-fr" id="email-register-fr" maxlength="190" autocomplete="email" required>

      <label for="password-register-fr">Contraseña</label>
      <input type="password" name="password-register-fr" id="password-register-fr" minlength="8" maxlength="72" autocomplete="new-password" required>

      <button type="submit" name="action" value="register-action">Registrarme</button>
    </form>
  </section>
<?php
}
?>
