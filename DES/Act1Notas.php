<?php

const CANTIDAD_DE_UNIDADES = 5;

$media = 0;
$unidad_suspensa = false;


for ($i = 0; $i < CANTIDAD_DE_UNIDADES; $i++) {
  $nota = (float) readline("Introduce la nota numero " . $i + 1 . ": ");
  $unidad_suspensa = ($nota < 5);
  $media += $nota;
}

$media = round($media/CANTIDAD_DE_UNIDADES);

if ($unidad_suspensa) {
  echo min($media, 4);
} else {
  echo $media;
}
