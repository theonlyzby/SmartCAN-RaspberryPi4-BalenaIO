<?php


$json = exec("python3 ./test.py -n 4 5");

//echo("Out=" . $json);
$decoded = json_decode($json);

echo("1=" . $decoded->{"Sensor4"}->{"ID"});

?>