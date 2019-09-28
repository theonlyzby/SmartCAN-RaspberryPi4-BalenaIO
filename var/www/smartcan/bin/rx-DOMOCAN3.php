<?php
//error_reporting(E_ALL);
  /*
    SCRIPT DE RECEPTION DES TRAMES, TRAMES VENU DU BINAIRE (server_udp) QUI ECOUTE SUR LE PORT 1470
  */

  $head = substr($argv[1],0,2);
  if ($head=="70") {
    //$date = date('d/m/y : H:i:s');
    //echo "[" . $date . "] - Reception brute(recv.php) : " . $argv[1];
	
    /* DEPENDANCES */
    $base_URI = substr($_SERVER['SCRIPT_FILENAME'],0,strpos(substr($_SERVER['SCRIPT_FILENAME'],1),"/")+1);
    include_once($base_URI.'/www/smartcan/www/conf/config.php');
    include_once(PATHCLASS . '/DomoCAN3/class.receptionTrame.php5');
    include_once(PATHCLASS . '/DomoCAN3/class.debug.php5');

    $debug = new debug();

    $receptionTrame = new receptionTrame();
    $receptionTrame->traiter(substr(strtolower($argv[1]), 0, 32));
    unset($receptionTrame);
  //} else {
    // echo("Frame not 70 type or malformed!" . CRLF);
  } // END IF

?>
