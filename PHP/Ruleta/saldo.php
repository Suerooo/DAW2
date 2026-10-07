<?php session_start(); ?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RU-RULETA SALDO</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="style-header.css">
</head>

<?php

const BALANCE = "balance";

const DEPOSIT_MONEY_ID = "deposit_money";

const ACTION = "action";
const ACTION_DEPOSIT = "action_deposit";
const ACTION_WITHDRAW = "action_withdraw";

const WITHDRAW_MONEY_ID = "withdraw_money";
const MANDATORY_FILE = "mandatory_file";

function depositMoney(float $amount_money, float &$balance)
{
  if ($amount_money > 0) {
    $balance += $amount_money;
  }
}

function validateMandatoryFile(): bool
{
  $target_dir = "uploads/";
  $target_file = $target_dir . basename($_FILES[MANDATORY_FILE]["name"]);
  $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
  $upload_ok = true;

  if (isset($_POST["submit"])) {

    if (file_exists($target_file)) {
      $upload_ok = false;
    }

    if ($image_file_type != "pdf") {
      $upload_ok = false;
    }
  }

  return $upload_ok;
}

function withdrawMoney(float $amount_money, float &$balance)
{
  if ($amount_money <= $balance && $amount_money > 0) {
    $balance -= $amount_money;
  }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_POST[ACTION])) {
  switch ($_POST[ACTION]) {
    case ACTION_DEPOSIT:
      depositMoney($_POST[DEPOSIT_MONEY_ID], $_SESSION[BALANCE]);
      break;
    case ACTION_WITHDRAW:
      if (validateMandatoryFile()) {
        withdrawMoney($_POST[WITHDRAW_MONEY_ID], $_SESSION[BALANCE]);
      }
      break;

    default:
      # code...
      break;
  }

  header("Location: saldo.php");
  exit;
}

?>

<body>
  <?php include_once "header.php" ?>

  <form action="saldo.php" method="POST">
    <h1>INGRESAR DINERO</h1>
    <label for="<?= DEPOSIT_MONEY_ID ?>">Cuanto dinero quieres ingresar:</label>
    <input type="number" id="<?= DEPOSIT_MONEY_ID ?>" name="<?= DEPOSIT_MONEY_ID ?>" min="-2333">

    <br>

    <button type="submit" name="<?= ACTION ?>" value="<?= ACTION_DEPOSIT ?>">INGRESAR</button>
  </form>

  <form action="saldo.php" method="POST" enctype="multipart/form-data">
    Select image to upload:
    <input type="file" name="<?php MANDATORY_FILE ?>" id="<?php MANDATORY_FILE ?>">
    <input type="submit" value="Enviar archivo" name="submit">

    <br>

    <label for="<?= WITHDRAW_MONEY_ID ?>">Cuanto dinero quieres ingresar:</label>
    <input type="number" id="<?= WITHDRAW_MONEY_ID ?>" name="<?= WITHDRAW_MONEY_ID ?>" min="-2333">

    <br>

    <button type="submit" name="<?= ACTION ?>" value="<?= ACTION_WITHDRAW ?>">RETIRAR</button>
  </form>

</body>

</html>