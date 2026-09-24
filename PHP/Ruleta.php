<?php

// Que numero vamos a apostar y que cantidad ruleta numero aleatorio entre el 0 y el 36 si acertamos o
// no cuento ganamos o perdimos saldo inicial 1000 aciertas x36 historial de cantidad de ganancias/perdida y numero ganadores
// Numero *36, color x2, par/impar x2, docenas(1-12, 13-24, 25-36) *3, Alto/bajo (19-36, 1-18) *2
// tiradas sin apuestas

$saldo = 1000;
$partidaEnJuego = [];
$historial = [];

function mostrarMenu(): int{
  echo "==============================\n";
  echo "       RULETA\n";
  echo "1. Apostar a un número\n";
  echo "2. Apostar a un color\n";
  echo "3. Apostar a par o impar\n";
  echo "4. Apostar a docenas\n";
  echo "5. Apostar a alto o bajo\n";
  echo "8. Ver historial de apuestas\n";
  echo "7. Jugar\n";
  echo "8. Salir\n";
  echo "==============================\n";
  
  return readline("Introduce una opcion: ");
}

function apostarDinero(): float {
  global $saldo;
  
  do {
    $cantidadApuesta = (float) readline("Introduce la cantidad de dinero que quieres apostar (saldo actual: $saldo): ");
  } while ($cantidadApuesta > $saldo);
  
  return $cantidadApuesta;
}

function agregarApuesta(&$partidaEnJuego, &$saldo, $eleccion, $tipoDeApuesta) {
  $dineroApostado = apostarDinero();
  $saldo -= $dineroApostado;
  
  $partidaEnJuego[] = [
    "tipo" => $tipoDeApuesta,
    "eleccion" => $eleccion,
    "dinero" => $dineroApostado
  ];
}

function apostarNumero(&$partidaEnJuego, &$saldo) {
  do {
    $numeroJugado = (int) readline("Introduce a que numero quieres apostar (0-36): ");

  } while($numeroJugado < 0 || $numeroJugado > 36);
  
  agregarApuesta($partidaEnJuego, $saldo, $numeroJugado, "Numero");
}

function apostarColor(&$partidaEnJuego, &$saldo) {
  do {
    echo "1. Rojo \n";
    echo "2. Negro \n";
    
    $colorJugado = (int) readline("Elige un color: ");
    
    switch ($colorJugado) {
      case 1:
        agregarApuesta($partidaEnJuego, $saldo, "rojo", "Color");
        break;
        
      case 2:
        agregarApuesta($partidaEnJuego, $saldo, "negro", "Color");
        break;
        
      default:
        echo "Esa opcion no esta disponible";
        break;
    }

  } while($colorJugado < 1 || $colorJugado > 2);
}

function apostarParImpar(&$partidaEnJuego, &$saldo) {
  do {
    echo "1. Par \n";
    echo "2. Impar \n";
    
    $parImparJugado = (int) readline("Elige un par o impar: ");
    
    switch ($parImparJugado) {
      case 1:
        agregarApuesta($partidaEnJuego, $saldo, "par", "Par/Impar");
        break;
        
      case 2:
        agregarApuesta($partidaEnJuego, $saldo, "impar", "Par/Impar");
        break;
        
      default:
        echo "Esa opcion no esta disponible";
        break;
    }

  } while($parImparJugado < 1 || $parImparJugado > 2);
}

function apostarDocenas(&$partidaEnJuego, &$saldo) {
  do {
    echo "1. Primer tramo (1-12) \n";
    echo "2. Segundo tramo (13-24) \n" ;
    echo "3. Tercer tramo (25-36) \n";
    
    $docenaJugado = (int) readline("Elige una opcion: ");
    
    switch ($docenaJugado) {
      case 1:
        agregarApuesta($partidaEnJuego, $saldo, "1-12", "Docena");
        break;
        
      case 2:
        agregarApuesta($partidaEnJuego, $saldo, "13-24", "Docena");
        break;
        
      case 3:
        agregarApuesta($partidaEnJuego, $saldo, "25-36", "Docena");
        break;
        
      default:
        echo "Esa opcion no esta disponible";
        break;
    }

  } while($docenaJugado < 1 || $docenaJugado > 3);
}

function apostarAltoBajo(&$partidaEnJuego, &$saldo) {
  do {
    echo "1. Bajo (1-18) \n";
    echo "2. Alto (19-36) \n" ;
    
    $altoBajoJugado = (int) readline("Elige una opcion: ");
    
    switch ($altoBajoJugado) {
      case 1:
        agregarApuesta($partidaEnJuego, $saldo, "1-18", "Alto/Bajo");
        break;
        
      case 2:
        agregarApuesta($partidaEnJuego, $saldo, "19-36", "Alto/Bajo");
        break;
        
      default:
        echo "Esa opcion no esta disponible";
        break;
    }

  } while($altoBajoJugado < 1 || $altoBajoJugado > 2);
}

function mostrarPartidaEnJuego($partidaEnJuego) {
    if (empty($partidaEnJuego)) {
      echo "No tienes apuestas preparadas\n";
      return;
    }

    echo "\n==============================\n";
    echo "         APUESTAS \n";

    $total = 0;

    foreach ($partidaEnJuego as $key => $value) {
      echo ($key + 1) . ". " . $value['tipo'] . " -> " . $value['eleccion'] . " -> " . $value["dinero"] . "$ \n";

      $total += $value['dinero'];
    }

    echo "------------------------------\n";
    echo "Total apostado: $total$ \n";
}


function comprobarColor($colorJugado, $numeroGanador): boolean {
  $numerosRojos = [1, 3, 5, 7, 9, 11, 13, 15, 17, 19, 21, 23, 25, 27, 29, 31, 33, 35]
  $colorGanador;
  
  foreach ($numerosRojos as $numeroRojo) {
    $colorGanador = ($numeroGanador === $numeroRojo) ? "rojo" : "negro";
  }
  
  return $colorGanador === $colorJugado;
}

function jugar($partidaEnJuego) {
  $numeroGanador = random_int(0, 36);
  
  foreach ($partidaEnJuego as $key => $value) {
    
  }
}

do {
  echo "\033[2J\033[;H";
  
  mostrarPartidaEnJuego($partidaEnJuego);
  
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
      # code...
      break;
    case 7:
      # code...
      break;
    case 8:
      echo "Saliendo...\n";
      break;
    default:
      echo "Esa opcion no esta en el menu \n";
      break;
  }
  
  } while ($opcion != 8);
  
