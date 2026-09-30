<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>RU-ROULETTE</title>
</head>



<?php
// Which number we are going to bet on and how much; roulette generates a random number between 0 and 36; whether we guessed correctly or
// not; track whether we won or lost; initial balance 1000; correct number pays x36; history of profit/loss amounts and winning numbers
// Number *36, color x2, even/odd x2, dozens (1-12, 13-24, 25-36) *3, high/low (19-36, 1-18) *2
// spins without bets



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

const ID_EVEN_ODD = "Even/Odd";
const EVEN = "Even";
const ODD = "Odd";

const ID_COLOR = "Color";
const RED = "Red";
const BLACK = "Black";

const ID_DOZEN = "Dozen";
const DOZEN1 = "1-12";
const DOZEN2 = "13-24";
const DOZEN3 = "25-36";

const ID_HIGH_LOW = "High/Low";
const HIGH = "19-36";
const LOW = "1-18";



function validateBet(float &$balance): float
{
  do {
    $betAmount = (float) readline("Enter the amount of money you want to bet (current balance: $balance): ");
  } while ($betAmount > $balance);
  return $betAmount;
}



function addBet(array &$currentGame, float &$balance, string $choice, string $betType)
{
  $moneyBet = validateBet($_SESSION["balance"]);
  $balance -= $moneyBet;

  $currentGame[] = [
    "type" => $betType,
    "choice" => $choice,
    "money" => $moneyBet
  ];
}



function betOnNumber(array &$currentGame, float &$balance)

{
  do {
    $numberBet = (int) readline("Enter the number you want to bet on (0-36): ");
  } while ($numberBet < 0 || $numberBet > 36);

  addBet($currentGame, $balance, $numberBet, ID_NUMBER);
}



function betOnColor(array &$currentGame, float &$balance)

{
  do {
    echo "1. Red <br>";
    echo "2. Black <br>";
    $colorChoice = (int) readline("Choose a color: ");

    switch ($colorChoice) {
      case 1:
        addBet($currentGame, $balance, RED, ID_COLOR);
        break;
      case 2:
        addBet($currentGame, $balance, BLACK, ID_COLOR);
        break;
      default:
        echo "That option is not available";
        break;
    }
  } while ($colorChoice < 1 || $colorChoice > 2);
}



function betOnEvenOdd(array &$currentGame, float &$balance)

{

  do {
    echo "1. Even <br>";
    echo "2. Odd <br>";

    $evenOddChoice = (int) readline("Choose even or odd: ");

    switch ($evenOddChoice) {
      case 1:
        addBet($currentGame, $balance, EVEN, ID_EVEN_ODD);
        break;
      case 2:
        addBet($currentGame, $balance, ODD, ID_EVEN_ODD);
        break;
      default:
        echo "That option is not available";
        break;
    }
  } while ($evenOddChoice < 1 || $evenOddChoice > 2);
}



function betOnDozens(array &$currentGame, float &$balance)
{
  do {
    echo "1. First dozen (1-12) <br>";
    echo "2. Second dozen (13-24) <br>";
    echo "3. Third dozen (25-36) <br>";

    $dozenChoice = (int) readline("Choose an option: ");

    switch ($dozenChoice) {
      case 1:
        addBet($currentGame, $balance, DOZEN1, ID_DOZEN);
        break;
      case 2:
        addBet($currentGame, $balance, DOZEN2, ID_DOZEN);
        break;
      case 3:
        addBet($currentGame, $balance, DOZEN3, ID_DOZEN);
        break;
      default:
        echo "That option is not available";
        break;
    }
  } while ($dozenChoice < 1 || $dozenChoice > 3);
}



function betOnHighLow(array &$currentGame, float &$balance)
{
  do {
    echo "1. Low (1-18) <br>";
    echo "2. High (19-36) <br>";

    $highLowChoice = (int) readline("Choose an option: ");

    switch ($highLowChoice) {
      case 1:
        addBet($currentGame, $balance, LOW, ID_HIGH_LOW);
        break;
      case 2:
        addBet($currentGame, $balance, HIGH, ID_HIGH_LOW);
        break;
      default:
        echo "That option is not available";
        break;
    }
  } while ($highLowChoice < 1 || $highLowChoice > 2);
}



function showCurrentGame(array $currentGame, float $balance)
{
  $total = 0;

  echo "<br>==============================<br>";
  echo "         BETS <br>";

  foreach ($currentGame as $key => $bet) {
    echo ($key + 1) . ". " . $bet["type"] . " -> " . $bet["choice"] . " -> " . $bet["money"] . "$ <br>";
    $total += $bet["money"];
  }

  echo "------------------------------<br>";
  echo "Current balance: $balance$ <br>";
  echo "Total bet: $total$ <br>";
}





function checkColor(string $choice, int $winningNumber): bool
{
  $redNumbers = [1, 3, 5, 7, 9, 11, 13, 15, 17, 19, 21, 23, 25, 27, 29, 31, 33, 35];

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
    echo ($key + 1) . ". " . $bet["type"] . " -> " . $bet["choice"] . " -> " . "You won" . " -> " . "+" . "$winnings" . "$ <br>";
  } else {
    echo ($key + 1) . ". " . $bet["type"] . " -> " . $bet["choice"] . " -> " . "You lost" . " -> " . "-" . $bet["money"] . "$ <br>";
  }
}



function play(array &$currentGame, float &$balance, array &$history)
{
  $winningNumber = random_int(0, 36);
  echo "<br>      WINNING NUMBER $winningNumber <br>";

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
        echo "An error occurred";
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
  echo "         HISTORY <br>";
  echo "==============================<br> <br>";



  foreach ($history as $key => $game) {
    echo ($key + 1) . ". Initial balance: " . $game["initialBalance"] . "$ <br>";
    echo ($key + 1) . ". Final balance: " . $game["finalBalance"] . "$ <br>";
    echo ($key + 1) . ". Profit/Loss: " . $game["profitLoss"] . "$ <br>";
    echo ($key + 1) . ". Winning number: " . $game["winningNumber"] . " <br>";
    echo "---------------------------------- <br> <br>";
  }
}

?>



<body>
  <form action="Roulette_english.php" method="POST">

    <div>
      <h2>BET ON A NUMBER</h2>

      <label for="number">Number to bet on:</label>
      <input type="number" id="number" name="number" min="0" max="36" placeholder="0-36">

      <br>

      <label for="number_bet">Bet amount:</label>
      <input type="number" id="number_bet" name="number_bet" min="0">

      <br>

      <button type="submit" name="action" value="number">Bet</button>
    </div>

    <div>
      <h2>BET ON A COLOR</h2>

      <input type="radio" id="color_none" name="color" checked />
      <label for="color_none">None</label>

      <input type="radio" id="red" name="color" />
      <label for="red">Red</label>

      <input type="radio" id="black" name="color" />
      <label for="black">Black</label>

      <br>

      <label for="color_bet">Bet amount:</label>
      <input type="number" id="color_bet" name="color_bet" min="0">

      <br>

      <button type="submit" name="action" value="color">Bet</button>
    </div>

    <div>
      <h2>BET ON EVEN OR ODD</h2>
      <input type="radio" id="even_odd_none" name="even_odd" checked />
      <label for="even_odd_none">None</label>

      <input type="radio" id="even" name="even_odd" />
      <label for="even">Even</label>

      <input type="radio" id="odd" name="even_odd" />
      <label for="odd">Odd</label>

      <br>

      <label for="even_odd_bet">Bet amount:</label>
      <input type="number" id="even_odd_bet" name="even_odd_bet" min="0">

      <br>

      <button type="submit" name="action" value="even_odd">Bet</button>
    </div>

    <div>

      <h2>BET ON DOZENS</h2>

      <input type="radio" id="dozen_none" name="dozen" checked />
      <label for="dozen_none">None</label>

      <input type="radio" id="dozen_one" name="dozen" />
      <label for="dozen_one">First dozen 1-12</label>

      <input type="radio" id="dozen_two" name="dozen" />
      <label for="dozen_two">Second dozen 13-24</label>

      <input type="radio" id="dozen_three" name="dozen" />
      <label for="dozen_three">Third dozen 25-36</label>

      <br>

      <label for="dozen_bet">Bet amount:</label>
      <input type="number" id="dozen_bet" name="dozen_bet" min="0">

      <br>

      <button type="submit" name="action" value="dozen">Bet</button>
    </div>

    <div>
      <h2>BET ON HIGH OR LOW</h2>

      <input type="radio" id="high_low_none" name="high_low" checked />
      <label for="high_low_none">None</label>

      <input type="radio" id="high" name="high_low" />
      <label for="high">High</label>

      <input type="radio" id="low" name="high_low" />
      <label for="low">Low</label>

      <br>

      <label for="high_low_bet">Bet amount:</label>
      <input type="number" id="high_low_bet" name="high_low_bet" min="0">

      <br>

      <button type="submit" name="action" value="high_low">Bet</button>
    </div>

    <button type="submit" name="action" value="spin">Spin roulette</button>

    <br>

  </form>



  <?php

  var_dump($_POST);

  showCurrentGame($_SESSION["currentGame"], $_SESSION["balance"]);

  switch ($_POST["action"]) {
    case "number":
      betOnNumber($_SESSION["currentGame"], $_SESSION["balance"]);
      break;

    case "color":
      betOnColor($_SESSION["currentGame"], $_SESSION["balance"]);
      break;

    case "even_odd":
      betOnEvenOdd($_SESSION["currentGame"], $_SESSION["balance"]);
      break;

    case "dozen":
      betOnDozens($_SESSION["currentGame"], $_SESSION["balance"]);
      break;

    case "high_low":
      betOnHighLow($_SESSION["currentGame"], $_SESSION["balance"]);
      break;

    case 6:
      showHistory($_SESSION["history"]);
      break;

    case 7:
      play($_SESSION["currentGame"], $_SESSION["balance"], $_SESSION["history"]);
      break;

    case 8:
      echo "Exiting...\n";
      break;

    default:
      echo "That option is not in the menu \n";
      break;
  }

  showHistory($_SESSION["history"]);

  ?>
</body>

</html>