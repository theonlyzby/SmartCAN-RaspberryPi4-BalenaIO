<?php
  // Define Base URI
  $base_URI = substr($_SERVER['SCRIPT_FILENAME'],0,strpos(substr($_SERVER['SCRIPT_FILENAME'],1),"/")+1);

  /* POUR ENVOI DE TRAME A LA CARTE CAN/ETH */
  define('ADRESSE_INTERFACE', 'localhost');
  define('PORT_INTERFACE', '1470');
  define('LOCAL_IP', '192.168.1.100');

  /*
    DEBUG POUR TRAME CAN
      0 : Aucun
      1 : Bas (information)
      2 : Haut (affichage des trames)
  */
  define('DEBUG', 0 );
  define('DEMO', '0');

  /* INTERFACE WEB */
  define('URI',  '/smartcan/www');
  define('DEBUG_AJAX', FALSE);
  define('PATH', $base_URI.'/www/smartcan/www/');
  define('PATHBASE', $base_URI.'/www/smartcan');
  define('PATHUPLOAD', $base_URI.'/www/smartcan/uploads/');
  define('PATHCLASS', $base_URI.'/www/smartcan/class/');
  define('PATHVAR', $base_URI.'/www/smartcan/var/');
  define('PATHBIN', $base_URI.'/www/smartcan/bin/');
  define('DEFAUT_LOCALISATION', 'RDC');
  define('URIPUSH', 'http://127.0.0.1/smartcan/envoi');
  define('URIRECV', '/smartcan/reception');
  define('CHANGE_MIN', '1');
  
  /* SQLI */
  define('mysqli_HOST', '127.0.0.1');
  define('mysqli_LOGIN', 'root');
  define('mysqli_DB', 'domotique');
  define('mysqli_PWD', 'SmartCAN');
  
  define('TABLE_ENTREE', 'entree');
  define('TABLE_LUMIERES', 'lumieres');
  define('TABLE_LUMIERES_STATUS', 'lumieres_status');
  define('TABLE_LUMIERES_CLEF', 'lumieres_clef');
  define('TABLE_CHAUFFAGE_SONDE', 'chauffage_sonde');
  define('TABLE_CHAUFFAGE_TEMP', 'chauffage_temp');
  define('TABLE_CHAUFFAGE_CLEF', 'chauffage_clef');
  define('TABLE_CHAUFFAGE_CLEF_TEMP', 'chauffage_clef_TEMP');
  define('TABLE_LOCALISATION', 'localisation');
  define('TABLE_METEO_FETE', 'meteo_fete');
  define('TABLE_HEATING_TIMSESLOTS', 'ha_thermostat_timeslots');
  define('TABLE_MEASURE', 'ha_measures');
  define('TABLE_VARIABLES', 'ha_settings');
  define('TABLE_ELEMENTS', 'ha_element');
  

  /* Admin Interface */
  define('ADMIN_DEBUG', '0'); // 0 = NO Debug, 1 = Outputs debug and error messages on Screen
  define('CRLF', chr(10).chr(13));
  define('PATHWEBADMIN', $base_URI.'/www/smartcan/admin/');
  define('ONEWIRE_OWSERVER_PORT', '4304');
  define('ADMIN_INTERFACE_NAME', 'SmartCAN Admin');
  define('ADMIN_LIGHT_PAGE_NAME', 'Position des points lumineux et Prises');
  define('ADMIN_LIGHT_SIDE_TITLE', 'Points Lumineux/Prises :');
  define('ADMIN_THERM_PAGE_NAME', 'Gestion de la Thermie');
  define('WEB_TEMP_SOURCES', '../www/lib/stations.txt'); //http://weather.rap.ucar.edu/surface/stations.txt
  define('WEB_TEMP_URL', 'http://tgftp.nws.noaa.gov/data/observations/metar/stations/');
  define('ADMIN_TEMP_PAGE_NAME', 'Position des Sondes de Temp&eacute;rature');
  define('ADMIN_TEMP_SIDE_TITLE', 'Sondes :');
  define('ADMIN_OUTPUT_PAGE_NAME', 'Gestion des Sorties');
  define('ADMIN_SYSMAP_PAGE_NAME', 'Composants du Syst&egrave;me');
  define('ADMIN_SYSMAP_SIDE_TITLE', 'Op&eacute;rations');
  define('ADMIN_VARIABLES_PAGE_NAME', 'G&eacute;rer les Variables');
  define('ADMIN_VIBE_PAGE_NAME', 'G&eacute;rer les Ambiances');
  define('ADMIN_VIBE_SIDE_TITLE', 'Ambiances :');
  
  
  /* 1WIRE & Temp Graphs*/
  define('ONEWIRE_MODE', 'RPI'); // Valuers possibles: OWFS , RPI
  define('RRDPATH', ''); // If RRDTool Installed: '/data/www/smartcan/rrdtool/'
  define('EXCELTEMPLOGSPATH', ''); // if Excell Temp Logs Active: '/data/www/smartcan/bin/tests/'
  define('SONDE_EXTERIEURE', '1');
  define('PATHOWFS', '/mnt/1wire');
  

?>
