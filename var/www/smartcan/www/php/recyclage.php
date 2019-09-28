<?php

  include '../class/class.envoiTrame.php5';
  include '../class/class.gradateur.php5';

  $titre = "Recyclage d'air";

  /* DECLARATION DES FONCTIONS EN AJAX POUR RECYCLAGE D'AIR */
  $xajax->register(XAJAX_FUNCTION, 'recyclageon');
  $xajax->register(XAJAX_FUNCTION, 'recyclageoff');

  /* ALLUMAGE RECYCLAGE */
  function recyclageon() {
    $gradateur = new gradateur();
    $gradateur->allumer(CARTE_RECYCLAGE, SORTIE_RECYCLAGE, 0, dechex(16));
  }

  /* ALLUMAGE RECYCLAGE */
  function recyclageoff() {
    $gradateur = new gradateur();
    $gradateur->eteindre(CARTE_RECYCLAGE, SORTIE_RECYCLAGE, 0);
  }

  /* CONNEXION BDD */
  $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
  mysqli_select_db($DB,mysqli_DB);

  /* AFFICHAGE DE LA TEMPERATURE DE LA MEZANINE */
  $retour = mysqli_query($DB,"SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_SONDE . "` WHERE `id` = '7'");
  $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
  $_XTemplate->assign('MEZANINE', $row[0]);

  /* AFFICHAGE DE LA TEMPERATURE DE L'ENTREE */
  $retour = mysqli_query($DB,"SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_SONDE . "` WHERE `id` = '6'");
  $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
  $_XTemplate->assign('ENTREE', $row[0]);

  /* FERMETURE BDD */
  mysqli_close($DB);

?>
