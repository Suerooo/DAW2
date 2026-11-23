<?php
function readShopCookie(string $name): array
{
  $value = $_COOKIE[$name] ?? '';
  $data = is_string($value) ? json_decode($value, true) : null;
  return is_array($data) ? $data : [];
}

function saveShopCookie(string $name, array $data): void
{
  $value = json_encode($data);
  if (strlen(rawurlencode($value)) > 3800) {
    throw new RuntimeException("El carrito no admite más productos distintos");
  }

  setcookie($name, empty($data) ? '' : $value, [
    'expires' => time() + (empty($data) ? -3600 : 30 * 24 * 60 * 60),
    'path' => rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax'
  ]);
}
