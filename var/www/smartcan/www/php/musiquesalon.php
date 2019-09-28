<?php

  $titre = "Gestion de la musique (salon)";

  /* DECLARATION DES FONCTIONS EN AJAX */
  $xajax->register(XAJAX_FUNCTION, 'baisser');
  $xajax->register(XAJAX_FUNCTION, 'monter');
  $xajax->register(XAJAX_FUNCTION, 'action');
  $xajax->register(XAJAX_FUNCTION, 'ajouter');
  $xajax->register(XAJAX_FUNCTION, 'playlist');
  $xajax->register(XAJAX_FUNCTION, 'play');
  $xajax->register(XAJAX_FUNCTION, 'supprimer');
  $xajax->register(XAJAX_FUNCTION, 'radio');
  $xajax->register(XAJAX_FUNCTION, 'lancerradio');
  $xajax->register(XAJAX_FUNCTION, 'playlistvider');
  $xajax->register(XAJAX_FUNCTION, 'lancer');
  $xajax->register(XAJAX_FUNCTION, 'balancetoggle');

  function balancetoggle($direction) {

    $tmp = 'cat ' . PATHVAR . 'volume';
    $volume = exec($tmp);
    $objResponse = new xajaxResponse();
    switch ($direction) {

      case 'Left':
        $tmp = 'cat ' . PATHVAR . 'audio_left';
        $left = exec($tmp);
        if ( $left == 1 ) {
          $tmp = `amixer set Front 0,`;
          $tmp = 'echo 0 > ' . PATHVAR . 'audio_left';
          exec($tmp);
          $objResponse->assign("salon","src", "./images/salonoff.png");
        }
        else {
          $tmp = 'amixer set Front ' . $volume . ',';
          exec($tmp);
          $tmp = 'echo 1 > ' . PATHVAR . 'audio_left';
          exec($tmp);
          $objResponse->assign("salon","src", "./images/salonon.png");
        }
        break;

      case 'Right':
        $tmp = 'cat ' . PATHVAR . 'audio_right';
        $right = exec($tmp);
        $tmp = 'cat ' . PATHVAR . 'audio_left';
        $left = exec($tmp);
        if ( $left == 1 ) {
          $volume_left = $volume;
	}
        else {
          $volume_left = '0';
        }
        if ( $right == 1 ) {
          $tmp = 'amixer set Front ' . $volume_left . ',0';
          exec($tmp);
          $tmp = 'echo 0 > ' . PATHVAR . 'audio_right';
          exec($tmp);
          $objResponse->assign("cuisine","src", "./images/cuisineoff.png");
        }
        else {
          $tmp = 'amixer set Front ' . $volume_left . ',' . $volume;
          exec($tmp);
          $tmp = 'echo 1 > ' . PATHVAR . 'audio_right';
          exec($tmp);
          $objResponse->assign("cuisine","src", "./images/cuisineon.png");
        }
        break;
    }
    $objResponse->script("$('#traitement').css('display', 'none')");
    return $objResponse;

    
  }

  function baisser() {
    $reponse = new XajaxResponse();
    $tmp = 'cat ' . PATHVAR . 'audio_left';
    $left = exec($tmp);
    $tmp = 'cat ' . PATHVAR . 'audio_right';
    $right = exec($tmp);
    $tmp = 'cat ' . PATHVAR . 'volume';
    $volume = exec($tmp);
    if ( $volume > 0 ) {
      $volume_new = $volume - 5;
      if ( $left == 1 ) {
        $volumeamixer = '' . $volume_new;
      }
      else {
        $volumeamixer = '0';
      }
      if ( $right == 1 ) {
        $volumeamixer .= ',' . $volume_new;
      }
      else {
        $volumeamixer .= ',0';
      }
      $tmp = 'amixer set Front ' . $volumeamixer;
      exec($tmp);
      $tmp = 'echo ' . $volume_new . ' > ' . PATHVAR . 'volume';
      exec($tmp);
      $tmp = $volume_new * 100;
      $tmp2 = $tmp / 65;
      $reponse->script("$('#message').text('Volume : " . round($tmp2,0) . " %')");
    }
    $reponse->script("$('#traitement').css('display', 'none')");
    return $reponse;
  }

  function lancerradio($valeur) {
    $reponse = new XajaxResponse();    
    $a = '/usr/local/mpc/bin/mpc clear';
    exec($a);
    $a = '/usr/local/mpc/bin/mpc add ' . $valeur;;
    exec($a);
    $a = '/usr/local/mpc/bin/mpc play';
    exec($a);
    $reponse->script("$('#traitement').css('display', 'none')");
    return $reponse;
  }


  function play($valeur) {
    $reponse = new XajaxResponse();
    $a = '/usr/local/mpc/bin/mpc play ' . $valeur;
    exec($a);
    $reponse->script("$('#traitement').css('display', 'none')");
    return $reponse;
  }


  function monter() {
    $reponse = new XajaxResponse();
    $tmp = 'cat ' . PATHVAR . 'audio_left';
    $left = exec($tmp);
    $tmp = 'cat ' . PATHVAR . 'audio_right';
    $right = exec($tmp);
    $tmp = 'cat ' . PATHVAR . 'volume';
    $volume = exec($tmp);
    if ( $volume < 65 ) {
      $volume_new = $volume + 5;
      if ( $left == 1 ) {
        $volumeamixer = '' . $volume_new;
      }
      else {
        $volumeamixer = '0';
      }
      if ( $right == 1 ) {
        $volumeamixer .= ',' . $volume_new;
      }
      else {
        $volumeamixer .= ',0';
      }
      $tmp = 'amixer set Front ' . $volumeamixer;
      exec($tmp);
      $tmp = 'echo ' . $volume_new . ' > ' . PATHVAR . 'volume';
      exec($tmp);
      $tmp = $volume_new * 100;
      $tmp2 = $tmp / 65;
      $reponse->script("$('#message').text('Volume : " . round($tmp2,0) . " %')");
    }
    $reponse->script("$('#traitement').css('display', 'none')");
    return $reponse;
  }

  function ajouter($musique) {
    $reponse = new XajaxResponse();
    $a = '/usr/local/mpc/bin/mpc add "' . $musique . '"';
    exec($a);
    $reponse->script("$('#traitement').css('display', 'none')");
    return $reponse;
  }

  function supprimer($musique) {
    $reponse = new XajaxResponse();
    $a = '/usr/local/mpc/bin/mpc del "' . $musique . '"';
    exec($a);
    $reponse->script("$('#traitement').css('display', 'none')");
    return $reponse;
  }

  function playlistvider() {
    $a = '/usr/local/mpc/bin/mpc clear';
    exec($a);
    $objResponse = new xajaxResponse();
    $objResponse->call("xajax_lancer('')");
    $objResponse->script("$('#traitement').css('display', 'none')");
    return $objResponse;
  }

  function action($action) {
    switch ($action) {

      case 'lire':
        $a = '/usr/local/mpc/bin/mpc play';
        break;

      case 'stop':
        $a = '/usr/local/mpc/bin/mpc stop';
        break;

      case 'precedent':
        $a = '/usr/local/mpc/bin/mpc prev';
        break;

      case 'suivant':
        $a = '/usr/local/mpc/bin/mpc next';
        break;

      case 'pause':
        $a = '/usr/local/mpc/bin/mpc pause';
        break;

    }
    exec($a);
    $objResponse = new xajaxResponse();
    $encours = who_play();
    if ( substr($encours, 0, 6) == 'volume' ) {
      $encours = "Aucune lecture";
    }
    $objResponse->assign("encours","innerHTML", $encours);
    $objResponse->script("$('#traitement').css('display', 'none')");
    return $objResponse;
  }

  function who_play() {
    $tmp = `/usr/local/mpc/bin/mpc | head -n1`;
    return $tmp;
  }

  function lancer($musique, $suite = '0') {
    $objResponse = new xajaxResponse();
    if ( substr($musique, -4) != '.mp3' ) {
      $b = $suite + 10;
      exec('/usr/local/mpc/bin/mpc ls "' . $musique . '"', $a);
      for ($i=$suite; $i<$b; $i++) {
        if ( $a[$i] != '' ) {
          if ( substr($a[$i], -4) == '.mp3' ) {
            $c = explode('/', $a[$i]);
            $tmp = count($c) - 1;
            $texte = $c[$tmp];
          }
          else {
            $texte = $a[$i];
          }
          if ( strlen($texte) > 70 ) {
            $texte = substr($texte, 0, 60) . "...";
          }
          $classe = ($i % 2) ? ' class="paire"' : '';
          $contenu .= "<p" . $classe . "><span onClick=\"traitement(); xajax_lancer('" . $a[$i] . "');\">" . $texte . "</span><img onClick=\"traitement(); xajax_ajouter('" . addslashes($a[$i]) . "');\" src=\"./images/ajouter.png\" style=\"float: right;\" /></p>";
        }
      }
      $v = $suite - 10;
      if ( isset($a[$v]) ) {
        $contenu .= "<img class=\"direction\" style=\"left: 40px;\" src=\"./images/precedent.png\" onClick=\"traitement(); xajax_lancer('" . $musique . "', '" . $v . "');\">";
      }
      if ( isset($a[$i]) ) {
        $contenu .= "<img class=\"direction\" style=\"left: 460px;\" src=\"./images/suivant.png\" onClick=\"traitement(); xajax_lancer('" . $musique . "', '" . $i . "');\">";
      }
      $objResponse->assign("musiques","innerHTML", $contenu);
      if ( $musique != '' ) {
        $objResponse->assign("revenir","style.visibility","visible");
      }
      else {
        $objResponse->assign("revenir","style.visibility","hidden");
      }
      $objResponse->assign("vider","style.visibility","hidden");
      $objResponse->assign("page","innerHTML", "BIBLIOTHEQUE");
      $objResponse->script("$('#traitement').css('display', 'none')");
      return $objResponse;
    }
  }


  function playlist($suite = '0') {
    $objResponse = new xajaxResponse();
      $b = $suite + 10;
      exec('/usr/local/mpc/bin/mpc playlist', $a);
      for ($i=$suite; $i<$b; $i++) {
        if ( $a[$i] != '' ) {
          if ( substr($a[$i], -4) == '.mp3' ) {
            $c = explode('/', $a[$i]);
            $tmp = count($c) - 1;
            $texte = $c[$tmp];
          }
          else {
            $texte = $a[$i];
          }
          $classe = ($i % 2) ? ' class="paire"' : '';
          $t = $i + 1;

          if ( strlen($texte) > 70 ) {
            $texte = substr($texte, 0, 60) . "...";
          }

          $contenu .= "<p" . $classe . "><span onClick=\"traitement(); xajax_play('" . $t . "');\">". $t . " : " . $texte . "</span><img onClick=\"traitement(); xajax_supprimer('" . $t . "'); xajax_playlist();\" src=\"./images/supprimer.png\" style=\"float: right;\" /></p>";
        }
      }
      $v = $suite - 10;
      if ( isset($a[$v]) ) {
        $contenu .= "<img style=\"left: 40px;\" class=\"direction\" src=\"./images/precedent.png\" onClick=\"traitement(); xajax_playlist('" . $v . "');\">";
      }
      if ( isset($a[$i]) ) {
        $contenu .= "<img style=\"left: 460px;\" class=\"direction\" src=\"./images/suivant.png\" onClick=\"traitement(); xajax_playlist('" . $i . "');\">";
      }
      $objResponse = new xajaxResponse();
      if ( $contenu != "" ) {
        $objResponse->assign("vider","style.visibility","visible");
      }
      else {
        $contenu = "<p style=\"text-align: center; font-weight: bold;\">Aucune musique !</p>";
      }
      $objResponse->assign("musiques","innerHTML", $contenu);
      $objResponse->assign("page","innerHTML", "LISTE DE LECTURE");
      $objResponse->script("$('#traitement').css('display', 'none')");
      return $objResponse;
  }


  function radio() {
    $objResponse = new xajaxResponse();
    $radio['RTL2'] = 'http://streaming.radio.rtl2.fr:80/rtl2-1-44-128';
    $radio['RFM'] = 'http://vipicecast.yacast.net:80/rfm';
    $radio['NOSTALGIE'] = 'mms://vipnrj.yacast.net/encodernostalgie_sat';
    $radio['FUNRADIO'] = 'http://streaming.radio.funradio.fr/fun-1-44-96';
    $radio['RIRE ET CHANSONS'] = 'http://vipicecast.yacast.net:80/rire_chansons';
    $radio['VIRGIN RADIO'] = 'http://vipicecast.yacast.net:80/virginradio';
    $radio['RTL'] = 'http://radio.rtl.fr/rtl.pls';
    $radio['FRANCE INFO'] = 'http://str30.creacast.com/radio_vide';
    $radio['RADIO CLASSIQUE'] = 'http://broadcast.infomaniak.net/radioclassique-high.mp3';
    $i = 0;
    foreach ($radio as $key => $value ) {
      $classe = ($i % 2) ? ' class="paire"' : '';
      $contenu .= "<p" . $classe . " onClick=\"traitement(); xajax_lancerradio('" . $value . "');\">" . $key . "</p>";
      $i++;
    }
    $objResponse->assign("vider","style.visibility","hidden");
    $objResponse->assign("musiques","innerHTML", $contenu);
    $objResponse->assign("page","innerHTML", "RADIO");
    $objResponse->script("$('#traitement').css('display', 'none')");
    return $objResponse;
  }



  exec('/usr/local/mpc/bin/mpc ls', $a);

  for ($i=0; $i<10; $i++) {
    if ( $a[$i] != '' ) {
      $classe = ($i % 2) ? ' class="paire"' : '';
      $_XTemplate->assign('PAIRE', $classe);
      $_XTemplate->assign('MUSIQUECOMPLET', $a[$i]);
      $_XTemplate->assign('MUSIQUE', $a[$i]);
      $_XTemplate->parse('main.MUSIQUES');
    }
  }

  $tmp = 'cat ' . PATHVAR . 'audio_left';
  $left = exec($tmp);
  $tmp = 'cat ' . PATHVAR . 'audio_right';
  $right = exec($tmp);

  if ( $right == 0 ) {
    $_XTemplate->assign('CUISINEIMG', 'cuisineoff');
  }
  else {
    $_XTemplate->assign('CUISINEIMG', 'cuisineon');
  }
  if ( $left == 0 ) {
    $_XTemplate->assign('SALONIMG', 'salonoff');
  }
  else {
    $_XTemplate->assign('SALONIMG', 'salonon');
  }


?>
