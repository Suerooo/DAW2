<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['token']) && is_string($_POST['token'] ?? null) && hash_equals($_SESSION['token'], $_POST['token'])) {
  $_SESSION = [];

  if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
  }

  session_destroy();
}

header("Location: ../main.php");
exit;
