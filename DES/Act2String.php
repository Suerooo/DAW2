<?php

$text1 = strtolower(readline("Introduce la primera frase: "));
$text2 = strtolower(readline("Introduce la segunda frase: "));

if (str_contains($text1, $text2)) {
  echo "La segunda frase si esta contenida en la primera \n";
  echo str_replace($text2, "", $text1) . "\n";
} else {
  echo "La segunda frase no esta contenida en la primera \n";
}

echo str_word_count("La primera frase tiene: " . $text1) . " palabras \n";
echo mb_strlen("La primera frase tiene " . $text1 . " caracteres \n");
