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

const NUMBER_ID = "number";
const NUMBER_ID_BET = "number_bet";

const EVEN_ODD_ID = "even_odd";
const EVEN_ODD_ID_BET = "even_odd_bet";
const EVEN_ODD_ID_NONE = "even_odd_none";
const EVEN = "even";
const ODD = "odd";

const COLOR_ID = "color";
const COLOR_ID_BET = "color_bet";
const COLOR_ID_NONE = "color_none";
const RED = "red";
const BLACK = "black";

const DOZEN_ID = "dozen";
const DOZEN_ID_BET = "dozen_bet";
const DOZEN_ID_NONE = "dozen_none";
const DOZEN1 = "dozen_one";
const DOZEN2 = "dozen_two";
const DOZEN3 = "dozen_three";

const HIGH_LOW_ID = "high_low";
const HIGH_LOW_ID_BET = "high_low_bet";
const HIGH_LOW_ID_NONE = "high_low_none";
const HIGH = "high";
const LOW = "low";

const TYPE = "type";
const CHOICE = "choice";
const MONEY = "money";
const WON = "won";
const RESULTS = "results";
const INITIAL_BALANCE = "initial_balance";
const FINAL_BALANCE = "final_balance";
const PROFIT_LOSS = "profit_loss";
const WINNING_NUMBER = "winning_number";

const ACTION = "action";
const ACTION_BET = "bet";
const ACTION_SPIN = "spin";

const SESSION_BALANCE = "balance";
const SESSION_CURRENT_GAME = "current_game";
const SESSION_HISTORY = "history";
const SESSION_LAST_RESULT = "last_result";

if (!isset($_SESSION[SESSION_BALANCE])) {
  $_SESSION[SESSION_BALANCE] = 1000;
}

if (!isset($_SESSION[SESSION_CURRENT_GAME])) {
  $_SESSION[SESSION_CURRENT_GAME] = [];
}

if (!isset($_SESSION[SESSION_HISTORY])) {
  $_SESSION[SESSION_HISTORY] = [];
}

function getChoiceLabel(string $choice): string
{
  return match ($choice) {
    HIGH => "Alto",
    LOW => "Bajo",
    EVEN => "Par",
    ODD => "Impar",
    RED => "Rojo",
    BLACK => "Negro",
    DOZEN1 => "Primera docena",
    DOZEN2 => "Segunda docena",
    DOZEN3 => "Tercera docena",
    default => $choice
  };
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

function addBet(array &$currentGame, float &$balance, string $choice, string $betType, float $moneyBet)
{
  if ($moneyBet > 0) {
    $balance -= $moneyBet;

    $currentGame[] = [
      TYPE => $betType,
      CHOICE => $choice,
      MONEY => $moneyBet
    ];
  }
}

function resolveBet(array $bet, bool $result, float &$balance, int $multiplier): array
{
  if ($result) {
    $winnings = $bet[MONEY] * $multiplier;
    $balance += $winnings;

    return [
      TYPE => $bet[TYPE],
      CHOICE => $bet[CHOICE],
      MONEY => $winnings,
      WON => true
    ];
  } else {
    return [
      TYPE => $bet[TYPE],
      CHOICE => $bet[CHOICE],
      MONEY => $bet[MONEY],
      WON => false
    ];
  }
}


function play(array &$currentGame, float &$balance, array &$history): array
{
  $winningNumber = random_int(0, 36);

  $totalBet = 0;

  foreach ($currentGame as $bet) {
    $totalBet += $bet[MONEY];
  }

  $initialBalance = $balance + $totalBet;

  foreach ($currentGame as $bet) {
    switch ($bet[TYPE]) {
      case NUMBER_ID:
        $results[] = resolveBet($bet, ($winningNumber === $bet[CHOICE]), $balance, 36);
        break;

      case COLOR_ID:
        $results[] = resolveBet($bet, checkColor($bet[CHOICE], $winningNumber), $balance, 2);
        break;

      case EVEN_ODD_ID:
        $results[] = resolveBet($bet, checkEvenOdd($bet[CHOICE], $winningNumber), $balance, 2);
        break;

      case DOZEN_ID:
        $results[] = resolveBet($bet, checkDozen($bet[CHOICE], $winningNumber), $balance, 3);
        break;

      case HIGH_LOW_ID:
        $results[] = resolveBet($bet, checkHighLow($bet[CHOICE], $winningNumber), $balance, 2);
        break;

      default:
        echo "A ocurrido un error";
        break;
    }
  }

  $history[] = [
    WINNING_NUMBER => $winningNumber,
    PROFIT_LOSS => $balance - $initialBalance,
  ];

  $currentGame = [];

  return [
    WINNING_NUMBER => $winningNumber,
    RESULTS => $results
  ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_POST[ACTION])) {
  switch ($_POST[ACTION]) {
    case ACTION_BET:

      if ($_POST[NUMBER_ID] != "" && ($_POST[NUMBER_ID] >= 0 && $_POST[NUMBER_ID] <= 36)) {
        addBet($_SESSION[SESSION_CURRENT_GAME], $_SESSION[SESSION_BALANCE], $_POST[NUMBER_ID], NUMBER_ID, $moneyBet = $_POST[NUMBER_ID_BET] == "" ? 0 : $_POST[NUMBER_ID_BET]);
      }

      if ($_POST[COLOR_ID] != "" && ($_POST[COLOR_ID] === RED || $_POST[COLOR_ID] === BLACK)) {
        addBet($_SESSION[SESSION_CURRENT_GAME], $_SESSION[SESSION_BALANCE], $_POST[COLOR_ID], COLOR_ID, $moneyBet = $_POST[COLOR_ID_BET] == "" ? 0 : $_POST[COLOR_ID_BET]);
      }

      if ($_POST[EVEN_ODD_ID] != "" && ($_POST[EVEN_ODD_ID] === EVEN || $_POST[EVEN_ODD_ID] === ODD)) {
        addBet($_SESSION[SESSION_CURRENT_GAME], $_SESSION[SESSION_BALANCE], $_POST[EVEN_ODD_ID], EVEN_ODD_ID, $moneyBet = $_POST[EVEN_ODD_ID_BET] == "" ? 0 : $_POST[EVEN_ODD_ID_BET]);
      }

      if ($_POST[DOZEN_ID] != "" && ($_POST[DOZEN_ID] === DOZEN1 || $_POST[DOZEN_ID] === DOZEN2 || $_POST[DOZEN_ID] === DOZEN3)) {
        addBet($_SESSION[SESSION_CURRENT_GAME], $_SESSION[SESSION_BALANCE], $_POST[DOZEN_ID], DOZEN_ID, $moneyBet = $_POST[DOZEN_ID_BET] == "" ? 0 : $_POST[DOZEN_ID_BET]);
      }

      if ($_POST[HIGH_LOW_ID] != "" && ($_POST[HIGH_LOW_ID] === HIGH || $_POST[HIGH_LOW_ID] === LOW)) {
        addBet($_SESSION[SESSION_CURRENT_GAME], $_SESSION[SESSION_BALANCE], $_POST[HIGH_LOW_ID], HIGH_LOW_ID, $moneyBet = $_POST[HIGH_LOW_ID_BET] == "" ? 0 : $_POST[HIGH_LOW_ID_BET]);
      }

      break;

    case ACTION_SPIN:
      $_SESSION[SESSION_LAST_RESULT] = play($_SESSION[SESSION_CURRENT_GAME], $_SESSION[SESSION_BALANCE], $_SESSION[SESSION_HISTORY]);
      break;

    default:
      echo "Esa opción no esta en el menu \n";
      break;
  }

  header("Location: Ruleta.php");
  exit;
}
?>


<body>
  <form action="Ruleta.php" method="POST">
    <h1>APOSTAR</h1>
    <div>
      <h2>APOSTAR A UN NUMERO</h2>

      <label for="<?= NUMBER_ID ?>">Numero al que apostar:</label>
      <input type="number" id="<?= NUMBER_ID ?>" name="<?= NUMBER_ID ?>" min="0" max="36" placeholder="0-36">

      <br>

      <label for="<?= NUMBER_ID_BET ?>">Cantidad de dinero a apostar:</label>
      <input type="number" id="<?= NUMBER_ID_BET ?>" name="<?= NUMBER_ID_BET ?>" min="0">
    </div>

    <div>
      <h2>APOSTAR A UN COLOR</h2>

      <input type="radio" id="<?= COLOR_ID_NONE ?>" name="<?= COLOR_ID ?>" value="" checked />
      <label for="<?= COLOR_ID_NONE ?>">Ninguno</label>

      <input type="radio" id="<?= RED ?>" name="<?= COLOR_ID ?>" value="<?= RED ?>" />
      <label for="<?= RED ?>">Rojo</label>

      <input type="radio" id="<?= BLACK ?>" name="<?= COLOR_ID ?>" value="<?= BLACK ?>" />
      <label for="<?= BLACK ?>">Negro</label>

      <br>

      <label for="<?= COLOR_ID_BET ?>">Cantidad de dinero a apostar:</label>
      <input type="number" id="<?= COLOR_ID_BET ?>" name="<?= COLOR_ID_BET ?>" min="0">
    </div>

    <div>
      <h2>APOSTAR A PAR O IMPAR</h2>

      <input type="radio" id="<?= EVEN_ODD_ID_NONE ?>" name="<?= EVEN_ODD_ID ?>" value="" checked />
      <label for="<?= EVEN_ODD_ID_NONE ?>">Ninguno</label>

      <input type="radio" id="<?= EVEN ?>" name="<?= EVEN_ODD_ID ?>" value="<?= EVEN ?>" />
      <label for="<?= EVEN ?>">Par</label>

      <input type="radio" id="<?= ODD ?>" name="<?= EVEN_ODD_ID ?>" value="<?= ODD ?>" />
      <label for="<?= ODD ?>">Impar</label>

      <br>

      <label for="<?= EVEN_ODD_ID_BET ?>">Cantidad de dinero a apostar:</label>
      <input type="number" id="<?= EVEN_ODD_ID_BET ?>" name="<?= EVEN_ODD_ID_BET ?>" min="0">
    </div>

    <div>
      <h2>APOSTAR A DOCENAS</h2>

      <input type="radio" id="<?= DOZEN_ID_NONE ?>" name="<?= DOZEN_ID ?>" value="" checked />
      <label for="<?= DOZEN_ID_NONE ?>">Ninguno</label>

      <input type="radio" id="<?= DOZEN1 ?>" name="<?= DOZEN_ID ?>" value="<?= DOZEN1 ?>" />
      <label for="<?= DOZEN1 ?>">Primera docena 1-12</label>

      <input type="radio" id="<?= DOZEN2 ?>" name="<?= DOZEN_ID ?>" value="<?= DOZEN2 ?>" />
      <label for="<?= DOZEN2 ?>">Segunda docena 13-24</label>

      <input type="radio" id="<?= DOZEN3 ?>" name="<?= DOZEN_ID ?>" value="<?= DOZEN3 ?>" />
      <label for="<?= DOZEN3 ?>">Tercera docena 25-36</label>

      <br>

      <label for="<?= DOZEN_ID_BET ?>">Cantidad de dinero a apostar:</label>
      <input type="number" id="<?= DOZEN_ID_BET ?>" name="<?= DOZEN_ID_BET ?>" min="0">
    </div>

    <div>
      <h2>APOSTAR A ALTO O BAJO</h2>

      <input type="radio" id="<?= HIGH_LOW_ID_NONE ?>" name="<?= HIGH_LOW_ID ?>" value="" checked />
      <label for="<?= HIGH_LOW_ID_NONE ?>">Ninguno</label>

      <input type="radio" id="<?= HIGH ?>" name="<?= HIGH_LOW_ID ?>" value="<?= HIGH ?>" />
      <label for="<?= HIGH ?>">Alto</label>

      <input type="radio" id="<?= LOW ?>" name="<?= HIGH_LOW_ID ?>" value="<?= LOW ?>" />
      <label for="<?= LOW ?>">Bajo</label>

      <br>

      <label for="<?= HIGH_LOW_ID_BET ?>">Cantidad de dinero a apostar:</label>
      <input type="number" id="<?= HIGH_LOW_ID_BET ?>" name="<?= HIGH_LOW_ID_BET ?>" min="0">
    </div>

    <button type="submit" name="<?= ACTION ?>" value="<?= ACTION_BET ?>">Apostar</button>
    <button type="submit" name="<?= ACTION ?>" value="<?= ACTION_SPIN ?>">Girar ruleta</button>

    <br>

  </form>

  <h1>APUESTAS ACTUALES</h1>
  <?php
  echo $_SESSION[SESSION_BALANCE] . "<br>";
  foreach ($_SESSION[SESSION_CURRENT_GAME] ?? [] as $value) {
    echo "Apostó por: " . getChoiceLabel($value[CHOICE]) . " | Apostó: " . $value[MONEY] . "€ <br>";
  }
  ?>

  <h1>RESULTADOS ÚLTIMA PARTIDA</h1>

  <?php
  if (isset($_SESSION[SESSION_LAST_RESULT])) {
    echo "Numero ganador: " . $_SESSION[SESSION_LAST_RESULT][WINNING_NUMBER] . "<br>";

    foreach ($_SESSION[SESSION_LAST_RESULT][RESULTS] ?? [] as $value) {
      if ($value[WON]) {
        echo "Apuesta ganada -> " . "Apostó por: " . getChoiceLabel($value[CHOICE]) . " | Apostó: +" . $value[MONEY] . "€ <br>";
      } else {
        echo "Apuesta perdida -> " . "Apostó por: " . getChoiceLabel($value[CHOICE]) . " | Apostó: -" . $value[MONEY] . "€ <br>";
      }
    }
  }
  ?>

  <h1>HISTORIAL</h1>
  <?php
  foreach ($_SESSION[SESSION_HISTORY] ?? [] as $key => $value) {
    echo ($key + 1) . ". Numero ganador: " . $value[WINNING_NUMBER] . " | Ganancias/Perdidas: " . $value[PROFIT_LOSS] . "€ <br>";
  }
  ?>

</body>

</html>