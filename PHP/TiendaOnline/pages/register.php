<?php
function registerUser(string $email, string $password, string $name, string $surname, PDO $conn): int
{
  $stmt = $conn->prepare("INSERT INTO user (email, password_hash, name, surname) VALUES (?, ?, ?, ?)");
  $stmt->execute([$email, password_hash($password, PASSWORD_DEFAULT), $name, $surname]);

  return (int) $conn->lastInsertId();
}

function showRegisterForm()
{
?>
  <h1>REGISTER</h1>
  <form action="main.php" method="POST" enctype="multipart/form-data">

    <label for="<?= "name-register-fr" ?>">Nombre:</label>
    <input type="text" name="<?= "name-register-fr" ?>" id="<?= "name-register-fr" ?>">
    <br>

    <label for="<?= "surname-register-fr" ?>">Apellidos:</label>
    <input type="text" name="<?= "surname-register-fr" ?>" id="<?= "surname-register-fr" ?>">
    <br>

    <label for="<?= "email-register-fr" ?>">Email:</label>
    <input type="email" name="<?= "email-register-fr" ?>" id="<?= "email-register-fr" ?>">
    <br>

    <label for="<?= "password-register-fr" ?>">Contraseña:</label>
    <input type="password" name="<?= "password-register-fr" ?>" id="<?= "password-register-fr" ?>">
    <br>

    <button type="submit" name="<?= "action" ?>" value="<?= "register-action" ?>">Registrar</button>
  </form>
<?php
}
?>