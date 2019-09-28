<?php
// SELECT * FROM `chauffage_sonde` JOIN `chauffage_temp` ON `chauffage_temp`.`id`=`chauffage_sonde`.`id`
  //$titre = 'Gestion des Températures';

  include '../class/class.envoiTrame.php5';

  /* DECLARATION DES FONCTIONS EN AJAX */
  $xajax->register(XAJAX_FUNCTION, 'descendreTemperature');
  $xajax->register(XAJAX_FUNCTION, 'monterTemperature');
  $xajax->register(XAJAX_FUNCTION, 'moyenne');
  global $Lang;
  global $msg;

  /* FONCTIONS PHP AJAX */
  function moyenne() {
    $objResponse = new xajaxResponse();
    $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
    mysqli_select_db($DB,mysqli_DB);
    $retour = mysqli_query($DB,"SELECT AVG(`valeur`) FROM `" . TABLE_CHAUFFAGE_TEMP . "` WHERE `moyenne` = '1'");
    $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
    mysqli_close($DB);
    $objResponse->assign("moyenne","innerHTML", round($row[0],1));
    return $objResponse;    
  }

  function descendreTemperature() {
    $objResponse = new xajaxResponse();
    $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
    mysqli_select_db($DB,mysqli_DB);    
    $retour = mysqli_query($DB,"SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF . "` WHERE `clef` = 'temperature'");
    $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
    $nouvelle = $row[0] - 1;
    mysqli_query($DB,"UPDATE `" . TABLE_CHAUFFAGE_CLEF . "` SET `valeur` = '" . $nouvelle . "' WHERE `clef` = 'temperature'");
    mysqli_close($DB);
    $objResponse->assign("temperature","innerHTML", $nouvelle);
    return $objResponse;    
  }

  function monterTemperature() {
    $objResponse = new xajaxResponse();
    $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
    mysqli_select_db($DB,mysqli_DB);
    $retour = mysqli_query($DB,"SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF . "` WHERE `clef` = 'temperature'");
    $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
    $nouvelle = $row[0] + 1;
    mysqli_query($DB,"UPDATE `" . TABLE_CHAUFFAGE_CLEF . "` SET `valeur` = '" . $nouvelle . "' WHERE `clef` = 'temperature'");
    mysqli_close($DB);
    $objResponse->assign("temperature","innerHTML", $nouvelle);
    return $objResponse;    
  }

  /* CONNEXION SQL */
  $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
  mysqli_select_db($DB,mysqli_DB);

  /* AFFICHAGE DES TEMPERATURES SUR LES PLANS */
  $retour0 = mysqli_query($DB,"SELECT `lieu` FROM `localisation`");
  while( $row0 = mysqli_fetch_array($retour0, MYSQLI_BOTH) ) {
    $sql = "SELECT `" . TABLE_CHAUFFAGE_SONDE . "`.`id` , `" . TABLE_CHAUFFAGE_SONDE . "`.`id_sonde` , `" . TABLE_CHAUFFAGE_SONDE . "`.`moyenne` , `" . TABLE_CHAUFFAGE_SONDE . "`.`localisation` , `" . TABLE_CHAUFFAGE_SONDE . "`.`img_x` , `" . 
			TABLE_CHAUFFAGE_SONDE . "`.`img_y` , `" . TABLE_CHAUFFAGE_SONDE . "`.`description` , `" . TABLE_CHAUFFAGE_TEMP . "`.`valeur`, `" . TABLE_CHAUFFAGE_TEMP . "`.`update` " . 
	        " FROM `" . TABLE_CHAUFFAGE_SONDE . "` JOIN `" . TABLE_CHAUFFAGE_TEMP . "` ON `" . TABLE_CHAUFFAGE_SONDE . "`.`id`=`" . TABLE_CHAUFFAGE_TEMP . "`.`id` WHERE `" . TABLE_CHAUFFAGE_SONDE . "`.`localisation` = '" . $row0['lieu'] . "';";
	$retour = mysqli_query($DB,$sql);
    while( $row = mysqli_fetch_array($retour, MYSQLI_BOTH) ) {
      $_XTemplate->assign('ID_SONDE', 	$row['id_sonde']);
      $_XTemplate->assign('IMGX', $row['img_x']);
      $_XTemplate->assign('IMGY', $row['img_y']);
      $_XTemplate->assign('TEMPERATURE', round($row['valeur'], 1));
      $_XTemplate->parse('main.PLAN.THERMOMETRE');
    }
    $_XTemplate->assign('LOCALISATION', $row0['lieu']);
    if ( $row0['lieu'] == DEFAUT_LOCALISATION ) {
      $_XTemplate->assign('CACHER', 'display: block;');
    }
    else {
      $_XTemplate->assign('CACHER', 'display: none;');
    }
    $_XTemplate->parse('main.PLAN');

  }

  /* AFFICHAGE DES NIVEAUX */
  $retour = mysqli_query($DB,"SELECT * FROM `" . TABLE_LOCALISATION . "`");
  while( $row = mysqli_fetch_array($retour, MYSQLI_BOTH) ) {
    $_XTemplate->assign('LOCALISATION', $row['lieu']);
    $_XTemplate->parse('main.NIVEAU');
  }

  /* AFFICHAGE DE LA TEMPERATURE MOYENNE DE LA MAISON */
  $retour = mysqli_query($DB,"SELECT AVG(`valeur`) FROM `" . TABLE_CHAUFFAGE_TEMP . "` WHERE `moyenne` = '1'");
  $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
  $_XTemplate->assign('MOYENNEMAISON', round($row[0],1));

  /* AFFICHAGE DE LA TEMPERATURE VOULUE */
  $retour = mysqli_query($DB,"SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF . "` WHERE `clef` = 'temperature'");
  $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
  $_XTemplate->assign('TEMPERATURE', $row[0]);

  /* AFFICHAGE DE L'ETAT DE LA CHAUDIERE */
  $retour = mysqli_query($DB,"SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` WHERE `clef` = 'chaudiere'");
  $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
  if ( $row[0] == '0' ) {
    $chaudiere = $msg["heater"]["Stopped"][$Lang];
  }
  else if ( $row[0] == '1' ) {
    $chaudiere = $msg["heater"]["Working"][$Lang];
  }
  $_XTemplate->assign('CHAUDIERE', $chaudiere);

  /* AFFICHAGE TEMPERATURE EXTERIEURE */
  $retour = mysqli_query($DB,"SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_TEMP . "` WHERE `id` = '1';");
  $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
  $_XTemplate->assign('TEMPERATUREEXTERIEURE', round($row[0], 1));

  /* FERMETURE SQL */
  mysqli_close($DB);

?>
