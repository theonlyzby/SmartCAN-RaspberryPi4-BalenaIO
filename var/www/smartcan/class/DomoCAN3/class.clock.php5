<?php

class DomoCAN3_clock extends DomoCAN3_envoiTrame {


  /*

    SET CLOCK TO CORRECT YEAR, MONTH, DAY, HOUR, MINUTE AND DAYLIGHT SAVING

	Commande : 0x01 (Cmd_1)
	Paramètre : 0x00
	Sens : Réception
	Broadcast : Refusé
	Rôle : Mettre l’horloge à l’heure
	Data : 6 octets  
	D0 : Année en hexa sur 2 chiffres 0 = 2000
	
	D1 : Mois et jour de la semaine (1=lundi à 7=dimanche)
	b7 : 1 = heure d'été
	b6/b4 : jour de la semaine
	b3/b0 : mois de 1 à 12
	
	D2 : jour du mois en hexadécimal de 1 à 31
	D3 : Heure de 0 à 23
	D4 : minute de 0 à 59
	D5 : seconde de 0 à 59
	Réponse : aucune, l’heure est renvoyée via Cmd_Clock

  */
  function setclock() {
	$IDCAN['DEST'] = 0x20;
	$IDCAN['COMM'] = 0x01;
	$IDCAN['CIBL'] = 0x00;
    $IDCAN['PARA'] = 0x00;
	$donnees[0] = date('y');
	$donnees[1] = bindec(str_pad(date('I'),1,"0").str_pad(decbin(date('N')),3,"0", STR_PAD_LEFT).str_pad(decbin(date('n')),4,"0", STR_PAD_LEFT));
	$donnees[2] = bindec(str_pad(decbin(date('j')),8,"0", STR_PAD_LEFT));
	$donnees[3] = bindec(str_pad(decbin(date('H')),8,"0", STR_PAD_LEFT));
	$donnees[4] = bindec(str_pad(decbin(date('i')),4,"0", STR_PAD_LEFT));
	$donnees[5] = bindec(str_pad(decbin(date('s')),4,"0", STR_PAD_LEFT));
	//echo("Set DOMOCAN Clock" . CRLF . "D0=" . $donnees[0] . CRLF . "D1=" . $donnees[1] . CRLF . "D2=" . $donnees[2] . CRLF . "D3=" . $donnees[3] . CRLF ."D4=" . $donnees[4] . CRLF . "D5=" . $donnees[5] . CRLF);
	
    $this->CAN(0x60,$IDCAN,$donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();
	
  }


  /*
    SET DAY - START & END TIMES 

    Commande : 0x08 (Cmd_8)
	Paramètre : 0x00
	Sens : Réception
	Broadcast : Refusé
	Rôle : Ecrire les heures de début et de fin de journée.
	Data : 4 octets
		D0 : Heure de début de journée, de 0 à 23
		D1 : Minute de début de journée, de 0 à 59
		D2 : Heure de fin de journée, de 0 à 23
		D3 : Minute de fin de journée, de 0 à 59 
  */
  function ConfSunRiseAndSet($sunriseHour,$sunriseMin,$sunsetHour,$sunsetMin) {
    $IDCAN['DEST'] = 0x20;
	$IDCAN['COMM'] = 0x08;
	$IDCAN['CIBL'] = 0x00;
    $IDCAN['PARA'] = 0x00;
	$donnees[0] = $sunriseHour;
	$donnees[1] = $sunriseMin;
	$donnees[2] = $sunsetHour;
	$donnees[3] = $sunsetMin;
	//echo("Config SunSet and SunRise".CRLF);
	
    $this->CAN(0x60,$IDCAN,$donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();
  }


  

  
  
}
?>
