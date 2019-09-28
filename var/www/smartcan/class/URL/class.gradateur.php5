<?php

class URL_gradateur extends URL_envoiTrame {


  /*
    ALLUMER UNE SORTIE
    $progression => TEMPS ENTRE L'ETAT FERME ET L'ETAT OUVERT (0 - 2550 ms)

  */
  function allumer($var1, $var2, $progression = 0, $intensite = 0x32) {
	$this->envoiTrame("onURL",$var1,$var2,$intensite,$progression);
  }


  /*
    ETEINDRE UNE SORTIE
    $progression => TEMPS ENTRE L'ETAT OUVERT ET L'ETAT FERME (0 - 2550 ms)
  */
  function eteindre($var1, $var2, $progression = "0x00") {
    $this->envoiTrame("offURL",$var1,$var2,0,$progression);
  }


  /*
    INVERSER UNE SORTIE
  */
  function inverser($var1, $var2, $intensite = "0x32", $progression = "0x00") {
	$this->envoiTrame("InvertURL",$var1,$var2,$intensite,$progression);
  }

  
  
} // END CLASS URL_gradateur
?>
