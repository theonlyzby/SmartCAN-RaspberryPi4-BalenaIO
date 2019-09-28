<?php
// PHP Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Messages
   // EN
$msg["URL"]["PageTitle"]["en"] = "wiringPI Module";
$msg["URL"]["MngGPIOTitle"]["en"] = "Manage GPIOs";
$msg["URL"]["RaspPin"]["en"] = "Raspberry Pin #";
$msg["URL"]["Mode"]["en"] = "Mode";
$msg["URL"]["Trigger"]["en"] = "Trigger";
$msg["URL"]["Output"]["en"] = "Output";
$msg["URL"]["Input"]["en"] = "Input";
$msg["URL"]["HIGHTrigger"]["en"] = "HIGH";
$msg["URL"]["lowTrigger"]["en"] = "low";
$msg["URL"]["Info1"]["en"] = "For your information, you can modify the status of Outputs and Lamps, using the following URL:";

// $_SERVER['HTTP_REFERER']	https://f7b582544292cdc677fabd8ae17acfa9.balena-devices.com/smartcan/admin/index.php?page=Modules
//$msg["URL"]["Info2"]["en"] = "http://".$_SERVER['SERVER_ADDR']."/smartcan/class/wiringPI/remoteGPIO.php?GPIOpin=<i>[Raspberry HW Pin]</i>&Value=<i>[ON ou OFF]";
$msg["URL"]["Info2"]["en"] = substr($_SERVER['HTTP_REFERER'], 0, strpos($_SERVER['HTTP_REFERER'], "/smartcan"))."/smartcan/class/wiringPI/remoteGPIO.php?GPIOpin=<i>[Raspberry HW Pin]</i>&Value=<i>[ON ou OFF]";
$msg["URL"]["NoWiringPI"]["en"] = "No wiringPI Ouputs?";

   // FR
$msg["URL"]["PageTitle"]["fr"] = "Module wiringPI";
$msg["URL"]["MngGPIOTitle"]["fr"] = "G&eacute;rer les GPIOs";
$msg["URL"]["RaspPin"]["fr"] = "Raspberry Pin #";
$msg["URL"]["Mode"]["fr"] = "Mode";
$msg["URL"]["Trigger"]["fr"] = "Trigger";
$msg["URL"]["Output"]["fr"] = "Output";
$msg["URL"]["Input"]["fr"] = "Input";
$msg["URL"]["HIGHTrigger"]["fr"] = "HAUT";
$msg["URL"]["lowTrigger"]["fr"] = "bas";
$msg["URL"]["Info1"]["fr"] = "Pour information, vous pouvez modifier l'&eacute;tat de ces sorties en utilisant l'URL suivante:";
//$msg["URL"]["Info2"]["nl"] = "http://".$_SERVER['SERVER_ADDR']."/smartcan/class/wiringPI/remoteGPIO.php?GPIOpin=<i>[Raspberry HW Pin]</i>&Value=<i>[ON ou OFF]";
$msg["URL"]["Info2"]["fr"] = substr($_SERVER['HTTP_REFERER'], 0, strpos($_SERVER['HTTP_REFERER'], "/smartcan"))."/smartcan/class/wiringPI/remoteGPIO.php?GPIOpin=<i>[Raspberry HW Pin]</i>&Value=<i>[ON ou OFF]";
$msg["URL"]["NoWiringPI"]["fr"] = "Pas de sorties wiringPI?";

   // NL
$msg["URL"]["PageTitle"]["nl"] = "wiringPI Module";
$msg["URL"]["MngGPIOTitle"]["nl"] = "GPIOs Beheer";
$msg["URL"]["RaspPin"]["nl"] = "Raspberry Pin #";
$msg["URL"]["Mode"]["nl"] = "Mode";
$msg["URL"]["Trigger"]["nl"] = "Trigger";
$msg["URL"]["Output"]["nl"] = "Uitgang";
$msg["URL"]["Input"]["nl"] = "Ingang";
$msg["URL"]["HIGHTrigger"]["nl"] = "HOOGH";
$msg["URL"]["lowTrigger"]["nl"] = "laag";
$msg["URL"]["Info1"]["nl"] = "Ter info, je kunt de status van de uitgangen en lampen aan te passen via de volgende URL:";
//$msg["URL"]["Info2"]["nl"] = "http://".$_SERVER['SERVER_ADDR']."/smartcan/class/wiringPI/remoteGPIO.php?GPIOpin=<i>[Raspberry HW Pin]</i>&Value=<i>[ON or OFF]";
$msg["URL"]["Info2"]["nl"] = substr($_SERVER['HTTP_REFERER'], 0, strpos($_SERVER['HTTP_REFERER'], "/smartcan"))."/smartcan/class/wiringPI/remoteGPIO.php?GPIOpin=<i>[Raspberry HW Pin]</i>&Value=<i>[ON or OFF]";
$msg["URL"]["NoWiringPI"]["nl"] = "Geen wiringPI Uitgangen?";



?>