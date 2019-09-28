<?php


  $titre = "Recettes de cuisine";

  /* DECLARATION DES FONCTIONS EN AJAX */
  $xajax->register(XAJAX_FUNCTION, 'listerCategories');
  $xajax->register(XAJAX_FUNCTION, 'listerRecettes');

  /* LISTER LES CATEGORIES */
  function listerCategories($suite = '0') {
    $objResponse = new xajaxResponse();

    $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
    mysqli_select_db($DB,'recettes');
    $retour = mysqli_query($DB,"SELECT `NumAbrev`,`Libelle` FROM `Abreviations` LIMIT " . $suite . ",10");
    $i = 0;

    while ( $row = mysqli_fetch_array($retour, MYSQLI_BOTH) ) {
      $classe = ($i % 2) ? ' class="paire"' : '';
      $contenu .= "<p" . $classe . "><span onClick=\"traitement(); xajax_listerRecettes('" . $row['NumAbrev'] . "');\">" . $row['Libelle'] . "</span></p>";
      $i++;
    }

    if ( $suite != '0' ) {
      $v = $suite - 1;
      $c = $suite - 10;
      $retour = mysqli_query($DB,"SELECT `NumAbrev` FROM `Abreviations` LIMIT " . $v . ",1");
      $row2 = mysqli_fetch_array($retour, MYSQLI_BOTH);
      if ( $row2['NumAbrev'] != "" ) {
        $contenu .= "<img class=\"direction\" style=\"left: 40px;\" src=\"./images/precedent.png\" onClick=\"traitement(); xajax_listerCategories('" . $c . "');\">";
      }
    }

    $a = $suite + 11;
    $b = $suite + 10;
    $retour = mysqli_query($DB,"SELECT `NumAbrev` FROM `Abreviations` LIMIT " . $a . ",1");
    $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
    if ( $row['NumAbrev'] != "" ) {
      $contenu .= "<img class=\"direction\" style=\"left: 460px;\" src=\"./images/suivant.png\" onClick=\"traitement(); xajax_listerCategories('" . $b . "');\">";
    }

    $objResponse->assign("categories","innerHTML", $contenu);
    $objResponse->script("$('#traitement').css('display', 'none')");
    mysqli_close($DB);
    return $objResponse;
  }

  /* LISTER LES RECETTES */
  function listerRecettes($cat, $suite = '0') {
    $objResponse = new xajaxResponse();

    $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
    mysqli_select_db($DB,'recettes');
    $retour = mysqli_query($DB,"SELECT `NumRecette` FROM `RECETTES_CATEGORIES` WHERE `NumAbrev` = '" . $cat . "' LIMIT " . $suite . ",10");
    $i = 0;

    while ( $row = mysqli_fetch_array($retour, MYSQLI_BOTH) ) {
      $retour2 = mysqli_query($DB,"SELECT `TitreRecette` FROM `RECETTES` WHERE `NumRecette` = '" . $row['NumRecette'] . "'");
      $row2 = mysqli_fetch_array($retour2, MYSQLI_BOTH);
      $classe = ($i % 2) ? ' class="paire"' : '';
      $contenu .= "<p" . $classe . "><span onClick=\"traitement(); go('recette&idrecette=" . $row['NumRecette'] . "');\">" . $row2['TitreRecette'] . "</span></p>";
      $i++;
    }
    if ( $suite != '0' ) {
      $c = $suite - 10;
      $v = $suite - 1;
      $retour = mysqli_query($DB,"SELECT `NumRecette` FROM `RECETTES_CATEGORIES` WHERE `NumAbrev` = '" . $cat . "' LIMIT " . $v . ",1");
      $row2 = mysqli_fetch_array($retour, MYSQLI_BOTH);
      if ( $row2['NumRecette'] != "" ) {
        $contenu .= "<img class=\"direction\" style=\"left: 40px;\" src=\"./images/precedent.png\" onClick=\"traitement(); xajax_listerRecettes($cat, '" . $c . "');\">";
      }
    }

    $a = $suite + 11;
    $b = $suite + 10;
    $retour = mysqli_query($DB,"SELECT `NumRecette` FROM `RECETTES_CATEGORIES` WHERE `NumAbrev` = '" . $cat . "' LIMIT " . $a . ",1");
    $row = mysqli_fetch_array($retour, MYSQLI_BOTH);
    if ( $row['NumRecette'] != "" ) {
      $contenu .= "<img class=\"direction\" style=\"left: 460px;\" src=\"./images/suivant.png\" onClick=\"traitement(); xajax_listerRecettes($cat, '" . $b . "');\">";
    }

    $objResponse->assign("categories","innerHTML", $contenu);
    $objResponse->script("$('#traitement').css('display', 'none')");
    mysqli_close($DB);
    return $objResponse;
  }

  $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
  mysqli_select_db($DB,'recettes');
  $retour = mysqli_query($DB,"SELECT `NumAbrev`,`Libelle` FROM `Abreviations` LIMIT 0,10");
  $i = 0;
  while ( $row = mysqli_fetch_array($retour, MYSQLI_BOTH) ) {

    $classe = ($i % 2) ? ' class="paire"' : '';
    $_XTemplate->assign('PAIRE', $classe);
    $_XTemplate->assign('IDCATEGORIE', $row['NumAbrev']);
    $_XTemplate->assign('CATEGORIE', $row['Libelle']);
    $_XTemplate->parse('main.CATEGORIES');
    $i++;
  }
  mysqli_close($DB);
?>
