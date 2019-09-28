<?php

class DomoCAN3_in16 extends DomoCAN3_envoiTrame {

  function __construct() {

    $this->debug = new debug();
    $this->gradateur = new DomoCAN3_gradateur();

  }


  /*
    RECEPTION D'UNE TRAME A DESTINATION D'UNE CARTE IN16
  */
  function reception($COMMANDE, $CIBLE, $PARAMETRE, $D0, $D1, $D2, $D3, $D4, $D5, $D6, $D7) {

    switch ($COMMANDE) {

      /* EN CAS DACTION */
      case '18' :

        break;

    } // END SWITCH

  }

  /*
    LIRE LE STATUT D'UNE FONCTION EN EEEPROM

    $cible => NUMERO DE CARTE D'ENTREE
    $entree => NUMERO DE L'ENTREE DE CETTE CARTE

  */
  function lireStatut($cible = 0xfe, $entree = 0x01) {
    $IDCAN[DEST] = 0x60;
    $IDCAN[COMM] = 0x02;
    $IDCAN[CIBL] = $cible;
    $IDCAN[PARA] = $entree;
    $donnees     = array();
    $this->CAN(0x60,$IDCAN,$donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();
  }

  /*
    MODIFIER UNE FONCTION EN NORMALEMENT FERME

    $cible => NUMERO DE CARTE D'ENTREE
    $entree => NUMERO DE L'ENTREE DE CETTE CARTE

  */
  function normalementFerme($cible = 0xfe, $entree = 0x01) {
    $IDCAN[DEST] = 0x60;
    $IDCAN[COMM] = 0x01;
    $IDCAN[CIBL] = $cible;
    $IDCAN[PARA] = $entree;
    $donnees[0]  = 0xfc;
    $donnees[1]  = 0x03;
    $donnees[2]  = 0x00;
    $this->CAN(0x60,$IDCAN,$donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();
  }


}
?>
