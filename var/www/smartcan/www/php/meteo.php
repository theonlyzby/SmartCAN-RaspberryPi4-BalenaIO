<?php

  $titre = 'Meteo';

  /* CONNEXION BDD */
  $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
  mysqli_select_db($DB,mysqli_DB);

  /* SELECTION ET AFFICHAGE DE L'ANNIVERSAIRE ET DE LA FETE DU JOUR */
  $retour = mysqli_query($DB,"SELECT `Fete` FROM `" . TABLE_METEO_FETE . "` WHERE `JourMois` = '" . date('d/m') . "'");
  $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
  $_XTemplate->assign('FETE', utf8_encode($row['Fete']));
  $retour = mysqli_query($DB,"SELECT prenom,DATE_FORMAT(date, '%d/%m'), mod( DATE_FORMAT( `date` , '%m%d' ) - DATE_FORMAT( CURDATE( ) , '%m%d' ) , 1231 ) + IF( mod( DATE_FORMAT( `date` , '%m%d' ) - DATE_FORMAT( CURDATE( ) , '%m%d' ) , 1231 ) >0, -1, 2000 ) AS poids FROM `meteo_anniversaire` WHERE YEAR( `date` ) <> '0000' ORDER BY poids ASC LIMIT 1");
  $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
  $_XTemplate->assign('PRENOM', utf8_encode($row['prenom']));
  $_XTemplate->assign('DATE', $row[1] . '/' . date(Y));

  /* FERMETURE BDD */
  mysqli_close($DB);

?>
