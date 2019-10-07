<?php

  //$titre = 'Gestion des points lumineux et Prises';

  //include '../class/DomoCAN3/class.envoiTrame.php5';
  //include '../class/DomoCAN3/class.gradateur.php5';
  include_once('../class/class.triggers.php5');

  /* DECLARATION DES FONCTIONS EN AJAX */
  $xajax->register(XAJAX_FUNCTION, 'inverser');
  $xajax->register(XAJAX_FUNCTION, 'allumerall');
  $xajax->register(XAJAX_FUNCTION, 'eteindreall');
  $xajax->register(XAJAX_FUNCTION, 'modenuit');
  
  //$xajax->configure('debug',true);

  /* FONCTIONS PHP AJAX */
  function inverser($manufacturer,$lamp_id, $delai, $consigne) {
    $reponse = new XajaxResponse();
    $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
    mysqli_select_db($DB,mysqli_DB);
    $retour = mysqli_query($DB,"SELECT * FROM `" . TABLE_LUMIERES . "` WHERE `id` = '".$lamp_id."';");
	$row = mysqli_fetch_array($retour, MYSQLI_BOTH);
	$grad_FullName = $manufacturer;
	include_once '../class/'.$grad_FullName.'/class.envoiTrame.php5';
	include_once '../class/'.$grad_FullName.'/class.gradateur.php5';
	$grad_FullName = $grad_FullName . "_gradateur";
    $gradateur = new $grad_FullName();
    //$gradateur->inverser($carte, $sortie, $consigne , hexdec($delai));
	$gradateur->inverser($row['carte'], $row['sortie'] , $delai, $consigne);
	mysqli_close($DB);
    return $reponse;
  }

  function allumerall($localisation) {
    $reponse = new XajaxResponse();
    //$gradateur = new gradateur();
    $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
    mysqli_select_db($DB,mysqli_DB);
    $retour = mysqli_query($DB,"SELECT * FROM `" . TABLE_LUMIERES . "` WHERE `localisation` = '" . $localisation . "';");
    while ( $row = mysqli_fetch_array($retour, MYSQLI_BOTH) ) {
      //$gradateur->allumer($row['carte'], hexdec(substr($row['sortie'], 2, 2)), hexdec($row['delai']), hexdec($row['valeur_souhaitee']));
	  $grad_FullName = $row['Manufacturer'];
	  include_once '../class/'.$grad_FullName.'/class.envoiTrame.php5';
	  include_once '../class/'.$grad_FullName.'/class.gradateur.php5';
	  $grad_FullName = $grad_FullName . "_gradateur";
      $gradateur = new $grad_FullName();
	  $gradateur->allumer($row['carte'], $row['sortie'], $row['delai'], hexdec($row['valeur_souhaitee']));
	  //$gradateur->inverser($row['carte'], $row['sortie'] , $row['delai'], $row['valeur_souhaitee']);
      sleep(1);
    }
    mysqli_close($DB);
    $reponse->script("$('#traitement').css('display', 'none')");
    return $reponse;
  }

  function eteindreall($localisation) {
    $reponse = new XajaxResponse();
    //$gradateur = new gradateur();
    $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
    mysqli_select_db($DB,mysqli_DB);
    $retour = mysqli_query($DB,"SELECT * FROM `" . TABLE_LUMIERES . "` WHERE `localisation` = '" . $localisation . "';");
    while( $row = mysqli_fetch_array($retour, MYSQLI_BOTH) ) {
      //$gradateur->eteindre($row['carte'], hexdec(substr($row['sortie'], 2, 2)), hexdec($row['delai']));
	  $grad_FullName = $row['Manufacturer'];
	  include_once '../class/'.$grad_FullName.'/class.envoiTrame.php5';
	  include_once '../class/'.$grad_FullName.'/class.gradateur.php5';
	  $grad_FullName = $grad_FullName . "_gradateur";
      $gradateur = new $grad_FullName();
	  $gradateur->eteindre($row['carte'], $row['sortie'], $row['delai']);
      sleep(1);
    }
    mysqli_close($DB);
    $reponse->script("$('#traitement').css('display', 'none')");
    return $reponse;
  }

  /* ACTIVATION - DESACTIVATION MODE NUIT FORCEE */
  function modenuit() {
    $reponse = new XajaxResponse();
    $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
    mysqli_select_db($DB,mysqli_DB);
    $retour = mysqli_query($DB,"SELECT `valeur` FROM `" . TABLE_LUMIERES_CLEF . "` WHERE `clef` = 'nuit'");
    $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
    if ( $row['valeur'] == '1' ) {
      $tmp = 'php ' . PATHBIN . 'mode_nuit.php off';
      exec($tmp);
    } else if ( $row['valeur'] == '0' ) {
      $tmp = 'php ' . PATHBIN . 'mode_nuit.php on';
      exec($tmp);
    }
    mysqli_close($DB);
    $reponse->script("$('#traitement').css('display', 'none')");
    return $reponse;
  }

  /* CONNEXION BDD */
  $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
  mysqli_select_db($DB,mysqli_DB);

  /* AFFICHAGE DES PLANS ET DES POINTS LUMINEUX */
  $retour0 = mysqli_query($DB,"SELECT `lieu` FROM `" . TABLE_LOCALISATION . "`;");
  while( $row0 = mysqli_fetch_array($retour0, MYSQLI_BOTH) ) {

    if ( $row0['lieu'] == DEFAUT_LOCALISATION ) {
      $_XTemplate->assign('CACHER', 'display: block;');
    }
    else {
      $_XTemplate->assign('CACHER', 'display: none;');
    }


    // $sql = "SELECT * FROM `" . TABLE_LUMIERES . "` WHERE `localisation` = '" . $row0['lieu'] . "';";
	$sql = "SELECT * FROM `" . TABLE_LUMIERES . "` JOIN `" . TABLE_LUMIERES_STATUS . "` ON `" . TABLE_LUMIERES . "`.`id`=`" . TABLE_LUMIERES_STATUS . "`.`id` WHERE `localisation` = '" . $row0['lieu'] . "' ORDER BY `" .
			TABLE_LUMIERES . "`.`id`;";
	$retour = mysqli_query($DB,$sql);
	$j=1;$k=1;$ky=0;
    while( $row = mysqli_fetch_array($retour, MYSQLI_BOTH) ) {
      if ( $row['valeur'] != '0' ) {
        $lumiere = "on";
      } else {
        $lumiere = "off";
      } // END IF
	  if ($j==15) {$k=$k+14; $ky=$ky+45;}
      $_XTemplate->assign('LUMIERE',         $lumiere);
	  $_XTemplate->assign('MANUFACTURER','"'.$row['Manufacturer'].'"');
      $_XTemplate->assign('IMG_X',           $ky+$row['img_x']); 
	  $_XTemplate->assign('IMG_Y',          ((38*($j-$k))+intval($row['img_y'])));
	  //$_XTemplate->assign('IMG_Y',           $row['img_y']);
	  $_XTemplate->assign('ICON',            $row['icon']);
	  $_XTemplate->assign('LAMP_ID',         $row['id']);
	  $_XTemplate->assign('DELAI',           $row['delai']);
	  $_XTemplate->assign('CONSIGNE',   '0x'.$row['valeur_souhaitee']);
      $_XTemplate->parse('main.PLAN.AMPOULE');
	  $j++;
    } // END WHILE
    $_XTemplate->assign('LOCALISATION', str_replace(" ","",$row0['lieu']));
	$_XTemplate->assign('PLANNAME', $row0['lieu']);
    $_XTemplate->parse('main.PLAN');
	$k++;
  } // END WHILE

  /* AFFICHAGE DES NIVEAUX */
  $retour = mysqli_query($DB,"SELECT * FROM `localisation`;");
  while( $row = mysqli_fetch_array($retour, MYSQLI_BOTH) ) {
    $_XTemplate->assign('LOCALISATION', str_replace(" ","", $row['lieu']));
	$_XTemplate->assign('PLANNAME', $row['lieu']);
    $_XTemplate->parse('main.NIVEAU');
  }

  /* AFFICHAGE ETAT DU MODE NUIT */
  $retour = mysqli_query($DB,"SELECT `valeur` FROM `" . TABLE_LUMIERES_CLEF . "` WHERE `clef` = 'nuit';");
  $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
  if ( $row['valeur'] == '1' ) {
    $_XTemplate->assign('MODENUIT', 'Activé');
  } else if ( $row['valeur'] == '0' ) {
    $_XTemplate->assign('MODENUIT', 'Désactivé');
  }

  /* FERMETURE BDD */
  mysqli_close($DB);

?>
