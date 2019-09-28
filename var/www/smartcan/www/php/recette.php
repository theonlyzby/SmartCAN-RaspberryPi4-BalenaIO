<?php


  $titre = "Gestion de la musique";

  /* CONNEXION BDD */
  $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
  mysqli_select_db($DB,'recettes');

  /* RECUPERATION DE LA RECETTE ET AFFICHAGE */
  $retour = mysqli_query($DB,"SELECT `TxtRecette` FROM `RECETTES` WHERE `NumRecette` = '" . $_GET['idrecette'] . "'");
  $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
  mysqli_close($DB);

  /* NETTOYAGE ET MISE EN FORME */
  $texte = str_replace('.', '.<br/><br/>', $row['TxtRecette']);
  $texte = str_replace('€', '', $texte);

  $_XTemplate->assign('TEXTERECETTE', $texte);

?>
