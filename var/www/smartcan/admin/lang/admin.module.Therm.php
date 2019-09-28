<?php
// PHP Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Messages
   // EN
$msg["THERM"]["ThermTitle"]["en"] = "Thermic Config Management";
$msg["THERM"]["InvalidSensor"]["en"] = "WARNING: Invalid or Already in use Sensor";
$msg["THERM"]["PleaseReload"]["en"] = "Please Reload this page within 120";
$msg["THERM"]["ErrorLoadFile"]["en"] = "ERROR: Impossible to load file";
$msg["THERM"]["WeatherModule"]["en"] = "Weather Module Configuration (Yahoo Weather - Where on Earth ID):";
$msg["THERM"]["FindItOn"]["en"] = "Find it on: ";
$msg["THERM"]["OutsideTemp"]["en"] = "Outside Temperature:";
$msg["THERM"]["SelectSource"]["en"] = "Select source of measure";
$msg["THERM"]["OWSensor"]["en"] = "1 Wire Sensor";
$msg["THERM"]["AirportNear"]["en"] = "&nbsp;Airport near You? ";
$msg["THERM"]["AirportSearchBase"]["en"] = "GB/";
$msg["THERM"]["ConnectedOn"]["en"] = "Connected on ";
$msg["THERM"]["1WUsedList"]["en"] = "Sensors already configured:";
$msg["THERM"]["Identifier"]["en"] = "Identifier";
$msg["THERM"]["Available1WList"]["en"] = "Available 1-Wire Sensors List:";
$msg["THERM"]["ThermostatConfig"]["en"] = "Interactive Thermostat Configuration:";
$msg["THERM"]["HeaterConfig"]["en"] = "Heater Configuration:";
$msg["THERM"]["HeaterMode"]["en"] = "Heater Working Mode";
$msg["THERM"]["WOModuleDesc"]["en"] = "Boiler Contact=Heater(+Boiler), Heater Contact=Heater Circulation Pump";
$msg["THERM"]["WOModule"]["en"] = "WITHOUT Controller Module";
$msg["THERM"]["WithModuleDesc"]["en"] = "Boiler Contact=Boiler ( + Heater Triggered), Heater Contact=Heater (Including Heater Trigger)";
$msg["THERM"]["WithModule"]["en"] = "With Controller Module";
$msg["THERM"]["HeaterContact"]["en"] = "Heater Contact";
$msg["THERM"]["NothingORInactif"]["en"] = "Nothing Selected/INACTIF";

   // FR
$msg["THERM"]["ThermTitle"]["fr"] = "Gestion de la Thermie";
$msg["THERM"]["InvalidSensor"]["fr"] = "QTTENTION: Sonde Invalide ou d&eacute;j&agrave; utilis&eacute;es";   
$msg["THERM"]["PleaseReload"]["fr"] = "Merci de recharger cette page dans 120";
$msg["THERM"]["ErrorLoadFile"]["fr"] = "ERREUR: Impossible de charger le fichier";
$msg["THERM"]["WeatherModule"]["fr"] = "Configuration du Module M&eacute;t&eacute;o (Yahoo Weather - Where on Earth ID):";
$msg["THERM"]["FindItOn"]["fr"] = "Trouvez le sur: ";
$msg["THERM"]["OutsideTemp"]["fr"] = "Temp&eacute;rature Ext&eacute;rieure:";
$msg["THERM"]["SelectSource"]["fr"] = "Selectionnez la source de mesure";
$msg["THERM"]["OWSensor"]["fr"] = "Sonde 1 Wire";
$msg["THERM"]["AirportNear"]["fr"] = "&nbsp;Un A&eacute;roport pr&egrave;s de chez vous? ";
$msg["THERM"]["AirportSearchBase"]["fr"] = "FR/";
$msg["THERM"]["ConnectedOn"]["fr"] = "Connect&eacute; sur ";
$msg["THERM"]["1WUsedList"]["fr"] = "Liste des Sondes Attribu&eacute;es:";
$msg["THERM"]["Identifier"]["fr"] = "Identifiant";
$msg["THERM"]["Available1WList"]["fr"] = "Liste des Sondes 1-Wire disponibles:";
$msg["THERM"]["ThermostatConfig"]["fr"] = "Configuration du Thermostat Int&eacute;ractif:";
$msg["THERM"]["HeaterConfig"]["fr"] = "Configuration de la Chaudi&egrave;re:";
$msg["THERM"]["HeaterMode"]["fr"] = "Mode de Fonctionnement de la Chaudi&egrave;re";
$msg["THERM"]["WOModuleDesc"]["fr"] = "Contact Boiler=Chaudi&egrave;re(+Chauffe Eau), Contact Chauffage=Circulateur Chauffage";
$msg["THERM"]["WOModule"]["fr"] = "SANS Module de contr&ograve;le";
$msg["THERM"]["WithModuleDesc"]["fr"] = "Contact Boiler=Chauffe Eau (+Activation chaudi&egrave;re), Contact Chauffage=Chauffage (Activation Chaudi&egrave;re Incluse)";
$msg["THERM"]["WithModule"]["fr"] = "Avec Module de Contr&ograve;le";
$msg["THERM"]["HeaterContact"]["fr"] = "Contact Chauffage";
$msg["THERM"]["NothingORInactif"]["fr"] = "Rien S&eacute;lectionn&eacute;/INACTIF";

   // NL
$msg["THERM"]["ThermTitle"]["nl"] = "Thermiek Beheer";
$msg["THERM"]["InvalidSensor"]["nl"] = "WAARSCHUWING: Ongeldige of reeds in gebruik Sensor";
$msg["THERM"]["PleaseReload"]["nl"] = "Herlaad AUB eze pagina binnen 120";
$msg["THERM"]["ErrorLoadFile"]["nl"] = "OVERTREDING: Onmogelijk om bestand te laden";
$msg["THERM"]["WeatherModule"]["nl"] = "Weer Moduleconfiguratie (Yahoo Weather - Where on Earth ID):";
$msg["THERM"]["FindItOn"]["nl"] = "Vind het op: ";
$msg["THERM"]["OutsideTemp"]["nl"] = "Buiten Temperatuur:";
$msg["THERM"]["SelectSource"]["nl"] = "Selecteer bron van meting";
$msg["THERM"]["OWSensor"]["nl"] = "1 Wire Sensor";
$msg["THERM"]["AirportNear"]["nl"] = "&nbsp;Luchthaven dichtbij? ";
$msg["THERM"]["AirportSearchBase"]["nl"] = "NL/";
$msg["THERM"]["ConnectedOn"]["nl"] = "Geconnecteerd op ";
$msg["THERM"]["1WUsedList"]["nl"] = "Sensors al in dienst:";
$msg["THERM"]["Identifier"]["nl"] = "Identifier";
$msg["THERM"]["Available1WList"]["nl"] = "Beschikbaar 1-Wire Sensoren Lijst:";
$msg["THERM"]["ThermostatConfig"]["nl"] = "Interactive Thermostaat Beheer:";
$msg["THERM"]["HeaterConfig"]["nl"] = "Verwarming Configuratie:";
$msg["THERM"]["HeaterMode"]["nl"] = "Verwarming Werkmodus";
$msg["THERM"]["WOModuleDesc"]["nl"] = "Ketel Contact=Verwarming(+Ketel), Verwarming Contact=Verwarming Circulatiepomp";
$msg["THERM"]["WOModule"]["nl"] = "ZONDER Controller Module";
$msg["THERM"]["WithModuleDesc"]["nl"] = "Ketel Contact=Ketel, Verwarming Contact=Verwarming";
$msg["THERM"]["WithModule"]["nl"] = "Met Controller Module";
$msg["THERM"]["HeaterContact"]["nl"] = "Verwarming Contact";
$msg["THERM"]["NothingORInactif"]["nl"] = "Niks Geselecteerd / INACTIEF";




?>