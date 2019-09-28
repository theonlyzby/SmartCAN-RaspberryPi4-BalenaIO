<?php

  /*
    PASSAGE DE LA STRUCTURE EN MODE NUIT (POUR BAISSER CHAUFFAGE OU ALLUMAGE DES LUMIERES A 5%)
  */

  /* DEPENDANCES */
  $base_URI = substr($_SERVER['SCRIPT_FILENAME'],0,strpos(substr($_SERVER['SCRIPT_FILENAME'],1),"/")+1);
  include_once($base_URI.'/www/smartcan/www/conf/config.php');

  /* ACTIVATION DU MODE NUIT */
  if ( $argv['1'] == 'on' ) {
    $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
    mysqli_select_db($DB,mysqli_DB);
    mysqli_query($DB,"UPDATE `" . TABLE_LUMIERES . "` SET `valeur_souhaitee` = '0'");
    mysqli_query($DB,"UPDATE `" . TABLE_LUMIERES_CLEF . "` SET `valeur` = '1' WHERE `clef` = 'nuit'");

    $retour = mysqli_query($DB,"SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF . "` WHERE `clef` = 'temperature'");
    $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
    /* - 2 degrés */
    $nouvelle = $row[0] - 2;
    mysqli_query($DB,"UPDATE `" . TABLE_CHAUFFAGE_CLEF . "` SET `valeur` = '" . $nouvelle . "' WHERE `clef` = 'temperature'");

    mysqli_close($DB);
  }

  /* DESACTIVATION DU MODE NUIT */
  if ( $argv['1'] == 'off' ) {
    $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
    mysqli_select_db($DB,mysqli_DB);
    mysqli_query($DB,"UPDATE `" . TABLE_LUMIERES . "` SET `valeur_souhaitee` = '0'");
    mysqli_query($DB,"UPDATE `" . TABLE_LUMIERES_CLEF . "` SET `valeur` = '0' WHERE `clef` = 'nuit'");

    $retour = mysqli_query($DB,"SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF . "` WHERE `clef` = 'temperature'");
    $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
    /* + 2 degrés */
    $nouvelle = $row[0] + 2;
    mysqli_query($DB,"UPDATE `" . TABLE_CHAUFFAGE_CLEF . "` SET `valeur` = '" . $nouvelle . "' WHERE `clef` = 'temperature'");

    mysqli_close($DB);
  }

  /* PROCESSUS D'ENVOI */
  $ch = curl_init(URIPUSH);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, "modenuit;" . $argv[1]);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $ret = curl_exec($ch);
  curl_close($ch);

  $ch = curl_init(URIPUSH);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, "temperaturevoulue;" . $nouvelle);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $ret = curl_exec($ch);
  curl_close($ch);

?>
