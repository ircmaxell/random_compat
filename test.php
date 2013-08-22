<?php

require_once __DIR__ . '/vendor/autoload.php';

$random = new \PHP\Random(true);

echo "Generating 8 random bytes:\n\n";
var_dump(bin2hex($random->bytes(8)));

echo "\nGenerating a random int between 0 and 128\n\n";

var_dump($random->int(0, 128));

echo "\nGenerating a random float\n\n";

var_dump($random->float());

echo "\nChoosing a random string element\n\n";

var_dump($random->choose("abcdefghijklmnopqrstuvwxyz"));

echo "\nChoosing a random array element\n\n";

var_dump($random->choose(array(1, 2, 3, 4, 5, 6, 7, 8, 9)));

echo "\nShuffling a string\n\n";

var_dump($random->shuffle("abcdefghijklmnopqrstuvwxyz"));

echo "\nShuffling an array\n\n";

var_dump($random->shuffle(array(1, 2, 3, 4, 5, 6, 7, 8, 9)));

echo "\nGenerating a token\n\n";
var_dump($random->token(32));

echo "\nGenerating a token (a,b,c,d)\n\n";
var_dump($random->token(8, array('a', 'b', 'c', 'd')));

