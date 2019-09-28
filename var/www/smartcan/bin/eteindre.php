<?php

  /*
    SCRIPT POUR ETEINDRE LES LUMIERES SOUS TIMER (EXECUTION A LA FIN DU TIMER)
  */

  /* DEPENDANCES */
  $base_URI = substr($_SERVER['SCRIPT_FILENAME'],0,strpos(substr($_SERVER['SCRIPT_FILENAME'],1),"/")+1);
  include_once($baes_URI'/www/smartcan/www/conf/config.php');
  include_once(PATHCLASS . '/class.envoiTrame.php5');
  include_once(PATHCLASS . '/class.gradateur.php5');

  /* CONNEXION */
  $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
  mysqli_select_db($DB,mysqli_DB);

  /* LIBERER LE TIMER EN SQL ET ETEINDRE */
  mysqli_query($DB,"UPDATE `lumieres` SET `timer_pid` = '0' WHERE `id` = '" . $argv[3] . "'");
  $gradateur = new gradateur();
  $gradateur->eteindre($argv[1], hexdec($argv[2]));
  mysqli_close();

?>
