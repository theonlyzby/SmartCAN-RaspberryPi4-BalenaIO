<?php

class wiringPI_gradateur extends wiringPI_envoiTrame {


  /*
    ALLUMER UNE SORTIE
    $progression => TEMPS ENTRE L'ETAT FERME ET L'ETAT OUVERT (0 - 2550 ms)

  */
  function allumer($var1, $var2, $progression = 0, $intensite = 0x32) {
	$this->GPIO_Set($var1,$var2,1,$progression);
  }


  /*
    ETEINDRE UNE SORTIE
    $progression => TEMPS ENTRE L'ETAT OUVERT ET L'ETAT FERME (0 - 2550 ms)
  */
  function eteindre($var1, $var2, $progression = "0x00") {
    $this->GPIO_Set($var1,$var2,0,$progression);
  }


  /*
    INVERSER UNE SORTIE
  */
  function inverser($var1, $var2, $intensite = "0x32", $progression = "0x00") {
    $this->GPIO_Toggle($var1,$var2,$intensite,$progression);
  }

  
  
} // END CLASS wiringPI_gradateur
?>
