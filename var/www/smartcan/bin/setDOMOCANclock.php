<?php

  /*
    SCRIPT POUR ALLUMAGE OU EXTINCTION DE LA CHAUDIERE
    SELON TEMPERATURE DE LA MAISON
    DOIT ETRE LANCE EN CRON
  */

  /* DEPENDANCES */
  $base_URI = substr($_SERVER['SCRIPT_FILENAME'],0,strpos(substr($_SERVER['SCRIPT_FILENAME'],1),"/")+1);
  include_once($base_URI.'/www/smartcan/www/conf/config.php');
  //include_once($base_URI.'/www/smartcan/class/class.triggers.php5');
  include_once(PATHCLASS . 'DomoCAN3/class.envoiTrame.php5');
  include_once(PATHCLASS . 'DomoCAN3/class.clock.php5');

  $clock   = new DomoCAN3_clock();
  
  /* CONNEXION A LA BDD */
  $DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
  mysqli_select_db($DB,mysqli_DB);


  // Set DOMOCAN Clock
  $clock->setclock();
  
  // Set SunRise and SunSet Times
  $sunrise     = date_sunrise(time(), SUNFUNCS_RET_STRING, (float)getenv("LONG"), (float)getenv("LAT"), 90, (1+date('I')));
  $sunriseHour = substr($sunrise,0,2);
  $sunriseMin  = substr($sunrise,3,2);
  //echo(CRLF."Lat=".(float)getenv("LAT").", Long".(float)getenv("LONG").CRLF."Sunrise=".$sunriseHour.", Min=".$sunriseMin.CRLF);
  
  $sunset      = date_sunset(time(), SUNFUNCS_RET_STRING, (float)getenv("LONG"), (float)getenv("LAT"), 90, (1+date('I')));
  $sunsetHour  = substr($sunset,0,2);
  $sunsetMin   = substr($sunset,3,2);
  //echo("Sunset=".$sunsetHour.", Min=".$sunsetMin.CRLF);
  
  $clock->ConfSunRiseAndSet($sunriseHour,$sunriseMin,$sunsetHour,$sunsetMin);
  
  
  // Closes DB
  mysqli_close($DB);

?>
