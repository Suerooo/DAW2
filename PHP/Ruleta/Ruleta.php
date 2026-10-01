<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>RU-RULETA</title>
</head>



<?php
session_start();

if (!isset($_SESSION["balance"])) {
  $_SESSION["balance"] = 1000;
}

if (!isset($_SESSION["currentGame"])) {
  $_SESSION["currentGame"] = [];
}

if (!isset($_SESSION["history"])) {
  $_SESSION["history"] = [];
}

const ID_NUMBER = "Number";

const ID_EVEN_ODD = "even_odd";
const EVEN = "even";
const ODD = "odd";

const ID_COLOR = "color";
const RED = "red";
const BLACK = "black";

const ID_DOZEN = "dozen";
const DOZEN1 = "dozen_one";
const DOZEN2 = "dozen_two";
const DOZEN3 = "dozen_three";

const ID_HIGH_LOW = "high_low";
const HIGH = "high";
const LOW = "low";


function addBet(array &$currentGame, float &$balance, string $choice, string $betType, float $moneyBet)
{
  if ($moneyBet < 0) {
    echo "No se puede apostar menos de 0€";
  } else {
    $balance -= $moneyBet;

    $currentGame[] = [
      "type" => $betType,
      "choice" => $choice,
      "money" => $moneyBet
    ];
  }
}


function showCurrentGame(array $currentGame, float $balance)
{
  $total = 0;

  echo "<br>==============================<br>";
  echo "         APUESTAS <br>";

  foreach ($currentGame as $key => $bet) {
    echo ($key + 1) . ". " . $bet["type"] . " -> " . $bet["choice"] . " -> " . $bet["money"] . "€ <br>";
    $total += $bet["money"];
  }

  echo "------------------------------<br>";
  echo "Saldo actual: $balance" . "€ <br>";
  echo "Total apostado: $total" . "€ <br>";
}





function checkColor(string $choice, int $winningNumber): bool
{
  $redNumbers = [1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36];

  foreach ($redNumbers as $redNumber) {
    $winningColor = ($winningNumber === $redNumber) ? RED : BLACK;
  }
  return $winningColor === $choice;
}



function checkEvenOdd(string $choice, int $winningNumber)
{
  if ($winningNumber % 2 === 0) {
    $winningEvenOdd = EVEN;
  } else {
    $winningEvenOdd = ODD;
  }

  return $choice === $winningEvenOdd;
}



function checkDozen(string $choice, int $winningNumber)
{
  if ($winningNumber >= 1 || $winningNumber <= 12) {
    $winningDozen = DOZEN1;
  } elseif ($winningNumber >= 13 || $winningNumber <= 24) {

    $winningDozen = DOZEN2;
  } elseif ($winningNumber >= 25 || $winningNumber <= 36) {

    $winningDozen = DOZEN3;
  }

  return $choice === $winningDozen;
}



function checkHighLow(string $choice, int $winningNumber)
{

  if ($winningNumber >= 1 || $winningNumber <= 18) {
    $winningHighLow = LOW;
  } elseif ($winningNumber >= 19 || $winningNumber <= 36) {
    $winningHighLow = HIGH;
  }

  return $winningHighLow === $choice;
}



function resolveBet(int $key, array $bet, bool $result, float &$balance, int $multiplier)
{
  if ($result) {
    $winnings = $bet["money"] * $multiplier;
    $balance += $winnings;
    echo ($key + 1) . ". " . $bet["type"] . " -> " . $bet["choice"] . " -> " . "Ganaste" . " -> " . "+" . "$winnings" . "€ <br>";
  } else {
    echo ($key + 1) . ". " . $bet["type"] . " -> " . $bet["choice"] . " -> " . "Perdiste" . " -> " . "-" . $bet["money"] . "€ <br>";
  }
}



function play(array &$currentGame, float &$balance, array &$history)
{
  $winningNumber = random_int(0, 36);
  echo "<br>      NUMERO GANADOR $winningNumber <br>";

  $totalBet = 0;

  foreach ($currentGame as $bet) {
    $totalBet += $bet["money"];
  }

  $initialBalance = $balance + $totalBet;

  foreach ($currentGame as $key => $bet) {
    switch ($bet["type"]) {
      case ID_NUMBER:
        resolveBet($key, $bet, ($winningNumber === $bet["choice"]), $balance, 36);
        break;

      case ID_COLOR:
        resolveBet($key, $bet, checkColor($bet["choice"], $winningNumber), $balance, 2);
        break;

      case ID_EVEN_ODD:
        resolveBet($key, $bet, checkEvenOdd($bet["choice"], $winningNumber), $balance, 2);
        break;

      case ID_DOZEN:
        resolveBet($key, $bet, checkDozen($bet["choice"], $winningNumber), $balance, 3);
        break;

      case ID_HIGH_LOW:
        resolveBet($key, $bet, checkHighLow($bet["choice"], $winningNumber), $balance, 2);
        break;

      default:
        echo "A ocurrido un error";
        break;
    }
  }



  $history[] = [
    "initialBalance" => $initialBalance,
    "finalBalance" => $balance,
    "profitLoss" => $balance - $initialBalance,
    "winningNumber" => $winningNumber
  ];

  $currentGame = [];
}



function showHistory(array $history)
{

  echo "<br>==============================<br>";
  echo "         HISTORIAL <br>";
  echo "==============================<br> <br>";



  foreach ($history as $key => $game) {
    echo ($key + 1) . ". Saldo inicial: " . $game["initialBalance"] . "€ <br>";
    echo ($key + 1) . ". Saldo final: " . $game["finalBalance"] . "€ <br>";
    echo ($key + 1) . ". Beneficio/Perdidas: " . $game["profitLoss"] . "€ <br>";
    echo ($key + 1) . ". Numero ganador: " . $game["winningNumber"] . " <br>";
    echo "---------------------------------- <br> <br>";
  }
}

?>



<body>
  <form action="Ruleta.php" method="POST">

    <div>
      <h2>APOSTAR A UN NUMERO</h2>

      <label for="number">Numero al que apostar:</label>
      <input type="number" id="number" name="number" min="0" max="36" placeholder="0-36">

      <br>

      <label for="number_bet">Cantidad de dinero a apostar:</label>
      <input type="number" id="number_bet" name="number_bet" min="0">
    </div>

    <div>
      <h2>APOSTAR A UN COLOR</h2>

      <input type="radio" id="color_none" name="color" value="" checked />
      <label for="color_none">Ninguno</label>

      <input type="radio" id="red" name="color" value="red" />
      <label for="red">Rojo</label>

      <input type="radio" id="black" name="color" value="black" />
      <label for="black">Negro</label>

      <br>

      <label for="color_bet">Cantidad de dinero a apostar:</label>
      <input type="number" id="color_bet" name="color_bet" min="0">
    </div>

    <div>
      <h2>APOSTAR A PAR O IMPAR</h2>
      <input type="radio" id="even_odd_none" name="even_odd" value="" checked />
      <label for="even_odd_none">Ninguno</label>

      <input type="radio" id="even" name="even_odd" value="even" />
      <label for="even">Par</label>

      <input type="radio" id="odd" name="even_odd" value="odd" />
      <label for="odd">Impar</label>

      <br>

      <label for="even_odd_bet">Cantidad de dinero a apostar:</label>
      <input type="number" id="even_odd_bet" name="even_odd_bet" min="0">
    </div>

    <div>

      <h2>APOSTAR A DOCENAS</h2>

      <input type="radio" id="dozen_none" name="dozen" value="" checked />
      <label for="dozen_none">Ninguno</label>

      <input type="radio" id="dozen_one" name="dozen" value="dozen_one" />
      <label for="dozen_one">Primera docena 1-12</label>

      <input type="radio" id="dozen_two" name="dozen" value="dozen_two" />
      <label for="dozen_two">Segunda docena 13-24</label>

      <input type="radio" id="dozen_three" name="dozen" value="dozen_three" />
      <label for="dozen_three">Tercera docena 25-36</label>

      <br>

      <label for="dozen_bet">Cantidad de dinero a apostar:</label>
      <input type="number" id="dozen_bet" name="dozen_bet" min="0">
    </div>

    <div>
      <h2>APOSTAR A ALTO O BAJO</h2>

      <input type="radio" id="high_low_none" name="high_low" value="" checked />
      <label for="high_low_none">Ninguno</label>

      <input type="radio" id="high" name="high_low" value="high" />
      <label for="high">Alto</label>

      <input type="radio" id="low" name="high_low" value="low" />
      <label for="low">Bajo</label>

      <br>

      <label for="high_low_bet">Cantidad de dinero a apostar:</label>
      <input type="number" id="high_low_bet" name="high_low_bet" min="0">
    </div>

    <button type="submit" name="action" value="bet">Apostar</button>
    <button type="submit" name="action" value="spin">Girar ruleta</button>

    <br>

  </form>

  <?php

  if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_POST["action"])) {
    switch ($_POST["action"]) {
      case "bet":

        if ($_POST["number"] != "") {
          addBet($_SESSION["currentGame"], $_SESSION["balance"], $_POST["number"], ID_NUMBER, $moneyBet = $_POST["number_bet"] == "" ? 0 : $_POST["number_bet"]);
        }

        if ($_POST["color"] != "") {
          addBet($_SESSION["currentGame"], $_SESSION["balance"], $_POST["color"], ID_COLOR, $moneyBet = $_POST["color_bet"] == "" ? 0 : $_POST["color_bet"]);
        }

        if ($_POST["even_odd"] != "") {
          addBet($_SESSION["currentGame"], $_SESSION["balance"], $_POST["even_odd"], ID_EVEN_ODD, $moneyBet = $_POST["even_odd_bet"] == "" ? 0 : $_POST["even_odd_bet"]);
        }

        if ($_POST["dozen"] != "") {
          addBet($_SESSION["currentGame"], $_SESSION["balance"], $_POST["dozen"], ID_DOZEN, $moneyBet = $_POST["dozen_bet"] == "" ? 0 : $_POST["dozen_bet"]);
        }

        if ($_POST["high_low"] != "") {
          addBet($_SESSION["currentGame"], $_SESSION["balance"], $_POST["high_low"], ID_HIGH_LOW, $moneyBet = $_POST["high_low_bet"] == "" ? 0 : $_POST["high_low_bet"]);
        }

        break;

      case "spin":
        play($_SESSION["currentGame"], $_SESSION["balance"], $_SESSION["history"]);
        break;

      default:
        echo "Esa opción no esta en el menu \n";
        break;
    }
  }

  showCurrentGame($_SESSION["currentGame"], $_SESSION["balance"]);
  showHistory($_SESSION["history"]);

  ?>
</body>

</html>