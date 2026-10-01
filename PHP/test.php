<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Test</title>
</head>

<body>
  <form action="test.php" method="POST">
    <div>
      <label for="nombre">Nombre:</label>
      <input type="text" id="nombre" name="nombre" placeholder="Tu nombre" value="
      <?php if (isset($_POST['nombre'])) {
        echo $_POST['nombre'];
      } ?>">
    </div>
    <div>
      <label for="apellidos">Apellidos:</label>
      <input type="text" id="apellido" name="apellido" placeholder="Tus apellidos" value="
      <?php if (isset($_POST['apellido'])) {
        echo $_POST['apellido'];
      } ?>">
    </div>
    <div>
      <label for="start">Start date:</label>

      <input type="date" id="birthdate" name="birthdate" min="2018-01-01" value="2018-07-22" />
    </div>
    <div>
      <label for="email">Email:</label>
      <input type="email" id="email" name="email" placeholder="Tu email" value="
      <?php if (isset($_POST['email'])) {
        echo $_POST['email'];
      } ?>">
    </div>
    <div>
      <label for="password">Contraseña:</label>
      <input type="password" id="password" name="password" placeholder="Tu contraseña">
    </div>
    <div>
      <label for="password">Repite la contraseña:</label>
      <input type="password" id="repeatpassword" name="repeatpassword" placeholder="Repite tu contraseña">
    </div>
    <div>
      <div>
        <input type="radio" id="hombre" name="genero" value="hombre" checked />
        <label for="hombre">Hombre</label>
        <input type="radio" id="mujer" name="genero" value="mujer" />
        <label for="mujer">Mujer</label>
      </div>
    </div>
    <br>
    <button type="submit">Enviar</button>
    <br>
  </form>

  <?php
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requeridas = [
      "email",
      "password",
      "repeatpassword"
    ];

    $campos = [
      "nombre" => "Nombre",
      "apellido" => "Apellidos",
      "email" => "Email",
      "password" => "Contraseña",
      "repeatpassword" => "Repite la contraseña",
      "genero" => "Genero"
    ];

    $error = "";

    foreach ($requeridas as $key) {
      if ($_POST[$key] == "") {
        $error = "Te falta el campo obligatorio " . $campos[$key] . "<br>";
        echo $error;
      }
    }

    if ($_POST['password'] != $_POST['repeatpassword']) {
      $error = "Las contraseñas no coinciden";
      echo $error;
    }

    if (!$error != "") {
      foreach ($_POST as $key => $value) {
        echo $campos[$key] . " => " . $value . "<br>";
      }
    }
  }

  ?>

</body>

</html>