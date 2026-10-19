<?php
function registerUser(array $user, PDO $conn): int
{
  $stmt = $conn->prepare("SELECT users.* FROM users LEFT JOIN clients ON users.id = clients.user_id WHERE users.email = ? AND users.role = 'client' AND clients.id IS NULL");
  $stmt->execute([$user[EMAIL_COLUMN]]);
  $incompleteUser = $stmt->fetch();

  if (!empty($incompleteUser) && password_verify($user[PASSWORD_COLUMN], $incompleteUser[PASSWORD_COLUMN])) {
    return (int) $incompleteUser[ID_COLUMN];
  }

  if ($user[ROLE_COLUMN] != "admin" && $user[ROLE_COLUMN] != "client") {
    throw new Exception("El rol del usuario tiene que ser 'admin' o 'client'");
  }

  $stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
  $stmt->execute([$user[EMAIL_COLUMN], password_hash($user[PASSWORD_COLUMN], PASSWORD_DEFAULT), $user[ROLE_COLUMN]]);

  return (int) $conn->lastInsertId();
}

function registerClient(Client $client, PDO $conn)
{
  $stmt = $conn->prepare("INSERT INTO clients (user_id, dni, name, surname, birthdate, driving_license) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->execute([
    $client->get_user_id(),
    $client->get_dni(),
    $client->get_name(),
    $client->get_surname(),
    $client->get_birthdate(),
    $client->get_driver_license(),
  ]);
}

function validateRegisterForm(array &$errors_message): bool
{

  $requiredFields = [
    DNI_REGISTER => "DNI",
    NAME_REGISTER => "Nombre",
    SURNAME_REGISTER => "Apellidos",
    BIRTHDATE_REGISTER => "Fecha de nacimiento",
    EMAIL_REGISTER => "Email",
    PASSWORD_REGISTER => "Contraseña"
  ];


  foreach ($requiredFields as $field => $display_field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
      $errors_message[] = "El campo $display_field es obligatorio";
      return false;
    }
  }

  if (!isset($_FILES[DRIVER_LICENSE_REGISTER]) || $_FILES[DRIVER_LICENSE_REGISTER]['error'] !== UPLOAD_ERR_OK) {
    $errors_message[] = "El campo licencia de conducir es obligatorio";
    return false;
  }

  return true;
}

function saveDriverLicense(array $file): string
{
  if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    throw new RuntimeException("Error al subir la licencia");
  }

  if ($file['size'] <= 0 || $file['size'] > 5 * 1024 * 1024) {
    throw new RuntimeException("El archivo debe ocupar como máximo 5 MB");
  }

  $allowedTypes = [
    "application/pdf" => "pdf",
    "image/jpeg" => "jpg",
    "image/png" => "png"
  ];

  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime = $finfo->file($file['tmp_name']);

  if (!isset($allowedTypes[$mime])) {
    throw new RuntimeException("El archivo debe ser PDF, JPG o PNG");
  }

  $filename = bin2hex(random_bytes(16)) . "." . $allowedTypes[$mime];

  $destination = LICENSE_DIR . DIRECTORY_SEPARATOR . $filename;

  if (!move_uploaded_file($file['tmp_name'], $destination)) {
    throw new RuntimeException("No se pudo guardar la licencia");
  }

  return $filename;
}
function showRegisterForm()
{
?>
  <h1>REGISTER</h1>
  <form action="main.php" method="POST" enctype="multipart/form-data">
    <label for="<?= DNI_REGISTER ?>">DNI:</label>
    <input type="text" name="<?= DNI_REGISTER ?>" id="<?= DNI_REGISTER ?>" value="<?= keepFields(DNI_REGISTER) ?>">
    <br>

    <label for="<?= NAME_REGISTER ?>">Nombre:</label>
    <input type="text" name="<?= NAME_REGISTER ?>" id="<?= NAME_REGISTER ?>" value="<?= keepFields(NAME_REGISTER) ?>">
    <br>

    <label for="<?= SURNAME_REGISTER ?>">Apellidos:</label>
    <input type="text" name="<?= SURNAME_REGISTER ?>" id="<?= SURNAME_REGISTER ?>" value="<?= keepFields(SURNAME_REGISTER) ?>">
    <br>

    <label for="<?= BIRTHDATE_REGISTER ?>">Fecha de nacimiento:</label>
    <input type="date" name="<?= BIRTHDATE_REGISTER ?>" id="<?= BIRTHDATE_REGISTER ?>" value="<?= keepFields(BIRTHDATE_REGISTER) ?>">
    <br>

    <label for="<?= DRIVER_LICENSE_REGISTER ?>">Licencia de conducir:</label>
    <input type="file" name="<?= DRIVER_LICENSE_REGISTER ?>" id="<?= DRIVER_LICENSE_REGISTER ?>">
    <br>

    <label for="<?= EMAIL_REGISTER ?>">Email:</label>
    <input type="email" name="<?= EMAIL_REGISTER ?>" id="<?= EMAIL_REGISTER ?>" value="<?= keepFields(EMAIL_REGISTER) ?>">
    <br>

    <label for="<?= PASSWORD_REGISTER ?>">Contraseña:</label>
    <input type="password" name="<?= PASSWORD_REGISTER ?>" id="<?= PASSWORD_REGISTER ?>">
    <br>

    <button type="submit" name="<?= ACTION_SUBMIT ?>" value="<?= REGISTER_ACTION_SUBMIT ?>">Registrar</button>
  </form>
<?php
}
?>
