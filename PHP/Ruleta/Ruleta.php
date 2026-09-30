<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>RU-RULETA</title>
</head>

<?php
// Que numero vamos a apostar y que cantidad ruleta numero aleatorio entre el 0 y el 36 si acertamos o
// no cuento ganamos o perdimos saldo inicial 1000 aciertas x36 historial de cantidad de ganancias/perdida y numero ganadores
// Numero *36, color x2, par/impar x2, docenas(1-12, 13-24, 25-36) *3, Alto/bajo (19-36, 1-18) *2
// tiradas sin apuestas

if (!isset($_SESSION["saldo"])) {
  $_SESSION["saldo"] = 1000;
}

if (!isset($_SESSION["partidaEnJuego"])) {
  $_SESSION["partidaEnJuego"] = [];
}

if (!isset($_SESSION["historial"])) {
  $_SESSION["historial"] = [];
}

const ID_NUMERO = "Numero";

const ID_PAR_IMPAR = "Par/Impar";
const PAR = "Par";
const IMPAR = "Impar";

const ID_COLOR = "Color";
const ROJO = "Rojo";
const NEGRO = "Negro";

const ID_DOCENA = "Docena";
const DOCENA1 = "1-12";
const DOCENA2 = "13-24";
const DOCENA3 = "25-36";

const ID_ALTO_BAJO = "Alto/Bajo";
const ALTO = "19-36";
const BAJO = "1-18";

function validarApuesta(float &$saldo): float
{

  do {
    $cantidadApuesta = (float) readline("Introduce la cantidad de dinero que quieres apostar (saldo actual: $saldo): ");
  } while ($cantidadApuesta > $saldo);
  if () {
    # code...
  }

  return $cantidadApuesta;
}

function agregarApuesta(array &$partidaEnJuego, float &$saldo, string $eleccion, string $tipoDeApuesta)
{
  $dineroApostado = validarApuesta($_SESSION["saldo"]);
  $saldo -= $dineroApostado;

  $partidaEnJuego[] = [
    "tipo" => $tipoDeApuesta,
    "eleccion" => $eleccion,
    "dinero" => $dineroApostado
  ];
}

function apostarNumero(array &$partidaEnJuego, float &$saldo)
{
  do {
    $numeroJugado = (int) readline("Introduce a que numero quieres apostar (0-36): ");
  } while ($numeroJugado < 0 || $numeroJugado > 36);

  agregarApuesta($partidaEnJuego, $saldo, $numeroJugado, ID_NUMERO);
}

function apostarColor(array &$partidaEnJuego, float &$saldo)
{
  do {
    echo "1. Rojo <br>";
    echo "2. Negro <br>";

    $colorJugado = (int) readline("Elige un color: ");

    switch ($colorJugado) {
      case 1:
        agregarApuesta($partidaEnJuego, $saldo, ROJO, ID_COLOR);
        break;

      case 2:
        agregarApuesta($partidaEnJuego, $saldo, NEGRO, ID_COLOR);
        break;

      default:
        echo "Esa opcion no esta disponible";
        break;
    }
  } while ($colorJugado < 1 || $colorJugado > 2);
}

function apostarParImpar(array &$partidaEnJuego, float &$saldo)
{
  do {
    echo "1. Par <br>";
    echo "2. Impar <br>";

    $parImparJugado = (int) readline("Elige un par o impar: ");

    switch ($parImparJugado) {
      case 1:
        agregarApuesta($partidaEnJuego, $saldo, PAR, ID_PAR_IMPAR);
        break;

      case 2:
        agregarApuesta($partidaEnJuego, $saldo, IMPAR, ID_PAR_IMPAR);
        break;

      default:
        echo "Esa opcion no esta disponible";
        break;
    }
  } while ($parImparJugado < 1 || $parImparJugado > 2);
}

function apostarDocenas(array &$partidaEnJuego, float &$saldo)
{
  do {
    echo "1. Primer tramo (1-12) <br>";
    echo "2. Segundo tramo (13-24) <br>";
    echo "3. Tercer tramo (25-36) <br>";

    $docenaJugado = (int) readline("Elige una opcion: ");

    switch ($docenaJugado) {
      case 1:
        agregarApuesta($partidaEnJuego, $saldo, DOCENA1, ID_DOCENA);
        break;

      case 2:
        agregarApuesta($partidaEnJuego, $saldo, DOCENA2, ID_DOCENA);
        break;

      case 3:
        agregarApuesta($partidaEnJuego, $saldo, DOCENA3, ID_DOCENA);
        break;

      default:
        echo "Esa opcion no esta disponible";
        break;
    }
  } while ($docenaJugado < 1 || $docenaJugado > 3);
}

function apostarAltoBajo(array &$partidaEnJuego, float &$saldo)
{
  do {
    echo "1. Bajo (1-18) <br>";
    echo "2. Alto (19-36) <br>";

    $altoBajoJugado = (int) readline("Elige una opcion: ");

    switch ($altoBajoJugado) {
      case 1:
        agregarApuesta($partidaEnJuego, $saldo, BAJO, ID_ALTO_BAJO);
        break;

      case 2:
        agregarApuesta($partidaEnJuego, $saldo, ALTO, ID_ALTO_BAJO);
        break;

      default:
        echo "Esa opcion no esta disponible";
        break;
    }
  } while ($altoBajoJugado < 1 || $altoBajoJugado > 2);
}

function mostrarPartidaEnJuego(array $partidaEnJuego, float $saldo)
{
  $total = 0;

  echo "<br>==============================<br>";
  echo "         APUESTAS <br>";


  foreach ($partidaEnJuego as $key => $apuesta) {
    echo ($key + 1) . ". " . $apuesta["tipo"] . " -> " . $apuesta["eleccion"] . " -> " . $apuesta["dinero"] . "$ <br>";

    $total += $apuesta["dinero"];
  }

  echo "------------------------------<br>";
  echo "Saldo actual: $saldo$ <br>";
  echo "Total apostado: $total$ <br>";
}


function comprobarColor(string $eleccion, int $numeroGanador): bool
{
  $numerosRojos = [1, 3, 5, 7, 9, 11, 13, 15, 17, 19, 21, 23, 25, 27, 29, 31, 33, 35];

  foreach ($numerosRojos as $numeroRojo) {
    $colorGanador = ($numeroGanador === $numeroRojo) ? ROJO : NEGRO;
  }

  return $colorGanador === $eleccion;
}

function comprobarParImpar(string $eleccion, int $numeroGanador)
{
  if ($numeroGanador % 2 === 0) {
    $parImparGanador = PAR;
  } else {
    $parImparGanador = IMPAR;
  }

  return $eleccion === $parImparGanador;
}

function comprobarDocena(string $eleccion, int $numeroGanador)
{
  if ($numeroGanador >= 1 || $numeroGanador <= 12) {
    $docenaGanador = DOCENA1;
  } elseif ($numeroGanador >= 13 || $numeroGanador <= 24) {
    $docenaGanador = DOCENA2;
  } elseif ($numeroGanador >= 25 || $numeroGanador <= 36) {
    $docenaGanador = DOCENA3;
  }

  return $eleccion === $docenaGanador;
}

function comprobarAltoBajo(string $eleccion, int $numeroGanador)
{
  if ($numeroGanador >= 1 || $numeroGanador <= 18) {
    $altoBajoGanador = BAJO;
  } elseif ($numeroGanador >= 19 || $numeroGanador <= 36) {
    $altoBajoGanador = ALTO;
  }

  return $altoBajoGanador === $eleccion;
}

function resolverApuesta(int $key, array $apuesta, bool $resolucion, float &$saldo, int $multiplicador)
{
  if ($resolucion) {
    $ganancia = $apuesta["dinero"] * $multiplicador;
    $saldo += $ganancia;
    echo ($key + 1) . ". " . $apuesta["tipo"] . " -> " . $apuesta["eleccion"] . " -> " . "Ganaste" . " -> " . "+" . "$ganancia" . "$ <br>";
  } else {
    echo ($key + 1) . ". " . $apuesta["tipo"] . " -> " . $apuesta["eleccion"] . " -> " . "Perdiste" . " -> " . "-" . $apuesta["dinero"] . "$ <br>";
  }
}

function jugar(array &$partidaEnJuego, float &$saldo, array &$historial)
{
  $numeroGanador = random_int(0, 36);
  echo "<br>      NUMERO GANADOR $numeroGanador <br>";

  $totalApostado = 0;
  foreach ($partidaEnJuego as $apuesta) {
    $totalApostado += $apuesta["dinero"];
  }

  $saldoInicial = $saldo + $totalApostado;

  foreach ($partidaEnJuego as $key => $apuesta) {
    switch ($apuesta["tipo"]) {

      case ID_NUMERO:
        resolverApuesta($key, $apuesta, ($numeroGanador === $apuesta["eleccion"]), $saldo, 36);
        break;
      case ID_COLOR:
        resolverApuesta($key, $apuesta, comprobarColor($apuesta["eleccion"], $numeroGanador), $saldo, 2);
        break;
      case ID_PAR_IMPAR:
        resolverApuesta($key, $apuesta, comprobarParImpar($apuesta["eleccion"], $numeroGanador), $saldo, 2);
        break;
      case ID_DOCENA:
        resolverApuesta($key, $apuesta, comprobarDocena($apuesta["eleccion"], $numeroGanador), $saldo, 3);
        break;
      case ID_ALTO_BAJO:
        resolverApuesta($key, $apuesta, comprobarAltoBajo($apuesta["eleccion"], $numeroGanador), $saldo, 2);
        break;

      default:
        echo "Ocurrio un error";
        break;
    }
  }

  $historial[] = [
    "saldoInicial" => $saldoInicial,
    "saldoFinal" => $saldo,
    "gananciaPerdida" => $saldo - $saldoInicial,
    "numeroGanador" => $numeroGanador
  ];

  $partidaEnJuego = [];
}

function mostrarHistorial(array $historial)
{
  echo "<br>==============================<br>";
  echo "         HISTORIAL <br>";
  echo "==============================<br> <br>";

  foreach ($historial as $key => $partida) {
    echo ($key + 1) . ". Saldo inicial: " . $partida["saldoInicial"] . "$ <br>";
    echo ($key + 1) . ". Saldo final: " . $partida["saldoFinal"] . "$ <br>";
    echo ($key + 1) . ". Ganancias/Perdidas: " . $partida["gananciaPerdida"] . "$ <br>";
    echo ($key + 1) . ". Numero ganador: " . $partida["numeroGanador"] . " <br>";
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
      <label for="number_bet">Cantidad apostada:</label>
      <input type="number" id="number_bet" name="number_bet" min="0">

      <br>
      <button type="submit" name="action" value="number">Apostar</button>
    </div>
    <div>
      <h2>APOSTAR A UN COLOR</h2>
      <input type="radio" id="color_none" name="color" checked />
      <label for="color_none">Ninguno</label>

      <input type="radio" id="red" name="color" />
      <label for="red">Rojo</label>

      <input type="radio" id="black" name="color" />
      <label for="black">Negro</label>
      <br>
      <label for="color_bet">Cantidad apostada:</label>
      <input type="number" id="color_bet" name="color_bet" min="0">

      <br>
      <button type="submit" name="action" value="color">Apostar</button>
    </div>
    <div>
      <h2>APOSTAR A PAR O IMPAR</h2>
      <input type="radio" id="even_odd_none" name="even_odd" checked />
      <label for="even_odd_none">Ninguno</label>

      <input type="radio" id="even" name="even_odd" />
      <label for="even">Par</label>

      <input type="radio" id="odd" name="even_odd" />
      <label for="odd">Impar</label>
      <br>
      <label for="even_odd_bet">Cantidad apostada:</label>
      <input type="number" id="even_odd_bet" name="even_odd_bet" min="0">

      <br>
      <button type="submit" name="action" value="even_odd">Apostar</button>
    </div>
    <div>
      <h2>APOSTAR A DOCENAS</h2>
      <input type="radio" id="dozen_none" name="dozen" checked />
      <label for="dozen_none">Ninguno</label>

      <input type="radio" id="dozen_one" name="dozen" />
      <label for="dozen_one">Primer tramo 1-12</label>

      <input type="radio" id="dozen_two" name="dozen" />
      <label for="dozen_two">Segundo tramo 13-24</label>

      <input type="radio" id="dozen_three" name="dozen" />
      <label for="dozen_three">Tercer tramo 25-36</label>
      <br>
      <label for="dozen_bet">Cantidad apostada:</label>
      <input type="number" id="dozen_bet" name="dozen_bet" min="0">

      <br>
      <button type="submit" name="action" value="dozen">Apostar</button>
    </div>
    <div>
      <h2>APOSTAR A ALTO O BAJO</h2>
      <input type="radio" id="high_low_none" name="high_low" checked />
      <label for="high_low_none">Ninguno</label>

      <input type="radio" id="high" name="high_low" />
      <label for="high">Alto</label>

      <input type="radio" id="low" name="high_low" />
      <label for="low">Bajo</label>

      <br>
      <label for="high_low_bet">Cantidad apostada:</label>
      <input type="number" id="high_low_bet" name="high_low_bet" min="0">

      <br>
      <button type="submit" name="action" value="high_low">Apostar</button>
    </div>
    <button type="submit" name="action" value="girar">Girar ruleta</button>
    <br>
  </form>

  <?php
  var_dump($_POST);
  mostrarPartidaEnJuego($_SESSION["partidaEnJuego"], $_SESSION["saldo"]);
  switch ($_POST["action"]) {
    case "number":
      apostarNumero($_SESSION["partidaEnJuego"], $_SESSION["saldo"]);
      break;
    case "color":
      apostarColor($_SESSION["partidaEnJuego"], $_SESSION["saldo"]);
      break;
    case "even_odd":
      apostarParImpar($_SESSION["partidaEnJuego"], $_SESSION["saldo"]);
      break;
    case "dozen":
      apostarDocenas($_SESSION["partidaEnJuego"], $_SESSION["saldo"]);
      break;
    case "high_low":
      apostarAltoBajo($_SESSION["partidaEnJuego"], $_SESSION["saldo"]);
      break;
    case 6:
      mostrarHistorial($_SESSION["historial"]);
      break;
    case 7:
      jugar($_SESSION["partidaEnJuego"], $_SESSION["saldo"], $_SESSION["historial"]);
      break;
    case 8:
      echo "Saliendo...\n";
      break;
    default:
      echo "Esa opcion no esta en el menu \n";
      break;
  }
  mostrarHistorial($_SESSION["historial"]);
  ?>


</body>

</html>