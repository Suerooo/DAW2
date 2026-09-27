<?php

// Que numero vamos a apostar y que cantidad ruleta numero aleatorio entre el 0 y el 36 si acertamos o
// no cuento ganamos o perdimos saldo inicial 1000 aciertas x36 historial de cantidad de ganancias/perdida y numero ganadores
// Numero *36, color x2, par/impar x2, docenas(1-12, 13-24, 25-36) *3, Alto/bajo (19-36, 1-18) *2
// tiradas sin apuestas

$saldo = 1000;
$partidaEnJuego = [];
$historial = [];

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

function mostrarMenu(): int
{
  echo "==============================\n";
  echo "       RULETA\n";
  echo "1. Apostar a un número\n";
  echo "2. Apostar a un color\n";
  echo "3. Apostar a par o impar\n";
  echo "4. Apostar a docenas\n";
  echo "5. Apostar a alto o bajo\n";
  echo "6. Ver historial de apuestas\n";
  echo "7. Jugar\n";
  echo "8. Salir\n";
  echo "==============================\n";

  return readline("Introduce una opcion: ");
}

function validarApuesta(): float
{
  global $saldo;

  do {
    $cantidadApuesta = (float) readline("Introduce la cantidad de dinero que quieres apostar (saldo actual: $saldo): ");
  } while ($cantidadApuesta > $saldo);

  return $cantidadApuesta;
}

function agregarApuesta(array &$partidaEnJuego, float &$saldo, string $eleccion, string $tipoDeApuesta)
{
  $dineroApostado = validarApuesta();
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
    echo "1. Rojo \n";
    echo "2. Negro \n";

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
    echo "1. Par \n";
    echo "2. Impar \n";

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
    echo "1. Primer tramo (1-12) \n";
    echo "2. Segundo tramo (13-24) \n";
    echo "3. Tercer tramo (25-36) \n";

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
    echo "1. Bajo (1-18) \n";
    echo "2. Alto (19-36) \n";

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

  echo "\n==============================\n";
  echo "         APUESTAS \n";


  foreach ($partidaEnJuego as $key => $apuesta) {
    echo ($key + 1) . ". " . $apuesta["tipo"] . " -> " . $apuesta["eleccion"] . " -> " . $apuesta["dinero"] . "$ \n";

    $total += $apuesta["dinero"];
  }

  echo "------------------------------\n";
  echo "Saldo actual: $saldo$ \n";
  echo "Total apostado: $total$ \n";
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
    echo ($key + 1) . ". " . $apuesta["tipo"] . " -> " . $apuesta["eleccion"] . " -> " . "Ganaste" . " -> " . "+" . "$ganancia" . "$ \n";
  } else {
    echo ($key + 1) . ". " . $apuesta["tipo"] . " -> " . $apuesta["eleccion"] . " -> " . "Perdiste" . " -> " . "-" . $apuesta["dinero"] . "$ \n";
  }
}

function jugar(array &$partidaEnJuego, float &$saldo, array &$historial)
{
  $numeroGanador = random_int(0, 36);
  echo "\n      NUMERO GANADOR $numeroGanador \n";

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
  echo "\n==============================\n";
  echo "         HISTORIAL \n";
  echo "==============================\n \n";

  foreach ($historial as $key => $partida) {
    echo ($key + 1) . ". Saldo inicial: " . $partida["saldoInicial"] . "$ \n";
    echo ($key + 1) . ". Saldo final: " . $partida["saldoFinal"] . "$ \n";
    echo ($key + 1) . ". Ganancias/Perdidas: " . $partida["gananciaPerdida"] . "$ \n";
    echo ($key + 1) . ". Numero ganador: " . $partida["numeroGanador"] . " \n";
    echo "---------------------------------- \n \n";
  }
}

do {
  mostrarPartidaEnJuego($partidaEnJuego, $saldo);

  switch ($opcion = mostrarMenu()) {
    case 1:
      apostarNumero($partidaEnJuego, $saldo);
      break;
    case 2:
      apostarColor($partidaEnJuego, $saldo);
      break;
    case 3:
      apostarParImpar($partidaEnJuego, $saldo);
      break;
    case 4:
      apostarDocenas($partidaEnJuego, $saldo);
      break;
    case 5:
      apostarAltoBajo($partidaEnJuego, $saldo);
      break;
    case 6:
      mostrarHistorial($historial);
      break;
    case 7:
      jugar($partidaEnJuego, $saldo, $historial);
      break;
    case 8:
      echo "Saliendo...\n";
      break;
    default:
      echo "Esa opcion no esta en el menu \n";
      break;
  }
} while ($opcion != 8);
