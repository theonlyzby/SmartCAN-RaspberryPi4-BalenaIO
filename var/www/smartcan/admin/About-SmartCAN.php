<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" dir="ltr" lang="fr-FR">



<META HTTP-EQUIV="expires" CONTENT="Wed, 09 Aug 2000 08:21:57 GMT">

<META HTTP-EQUIV="Pragma" CONTENT="no-cache">

<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>About SmartCAN</title>



<?php

// Includes

include_once '../www/conf/config.php';



echo("<p><h1 style=\"text-align: center;\">SmartCAN<br>Version 0.9.7-Beta</h1></p>");

echo("<p>SmartCAN is an Open Source (Apache v2 licnese) Home Automation Compagnion Web Pioject, at first and still web monitoring functions are the priority, but next release should include coding and automation features (probaly based upon blickly, not yet committed feature, ... don't ask for a ETA! ;-) ).</p>" . CRLF);



echo("<p><h2>Project Evolutions</h2></p>" . CRLF);

echo("<p>This project started in 2010, initiated by benoitd54. The sources of domowebv1.0 where published in April 2011 (<a href src='http://domocan.akseo.fr/domowebv1.0.tgz'>here</a>)</p>" . CRLF);

echo("<p>In 2012, the project has been tacken over by Henry, at first a virtual machine was on diposal to test, but, for the Windows users, it was too complex to put together the entire system with all services configured and running correctly. <br> In Oct 2012, the development restarted, some work done on an first admin interfcae and a Thermostat module added (source: <a href src='http://homeautomategorilla.blogspot.be '>HomeAutomate Gorilla</a>)</p>" . CRLF);

echo("<p>In October 2013, a very first \"3 click Install\" Image is published, 2 major milesones will be made public after that, the system prooved to be function, but still a lot of Benoit54D's hardcoded code was still present, a major rework was needed to cope with initial idea!</p>" . CRLF);

echo("<p>After some missundertsandings in the DomoCAN community, also due to nature/control of closed specifications of DomoCAN (May-June 2014), development was put on hold (again?!?) for some time...</p>" . CRLF);



echo("<p>In April 2015, after development was restarted and lots of ground up changes were initiated, based upon new dynamic class concept, and with the aim to serve future systems evolution and more openness ... SmartCAN was initiated! It is not solely linked to DomoCAN anymore, but DomoCAN is considered as one possible module. With this modular concept, every module can be added dynamically(first POC with an URL module made available), the system is able to integrate DomoCAN v4 when made available, but also Philips Hue, Arduino, WiFi Plugs, Bluetooth LE Devices, RF433 Devices, IR devices, SMS communications (IN and OUT), and ... much much more ...</p>" . CRLF);



echo("<p><h2>Thank you!</h2></p>" . CRLF);

echo("<p>Enormous thank you to <a href src='http://www.abcelectronique.com/bigonoff/' target='_blank'>Bigonoff</a> which created the DomoCAN, an Open Source Home Automation system, based upon CAN bus and PIC microcontrollers. This system prooved to be very well designed and implemented. Its de-centralized nature and very well written code makes it a top if you consider implementing such at system at home or for commercial purposes. It is provided with all sources, I/O cards and a Domogest programmation Windows PC program. Web monitoring has never been considered officially, so several Web monitoring/actions frameworks have been developped (ex.: Napo7, <a href src='http://domocan.heberg-forum.net/ftopic21-0-asc-0.html' target='_blank'>Here</a>).</p>" . CRLF);



echo("<p>Big Thank you to <a href src='http://www.abcelectronique.com/bigonoff/forum/member.php?action=profile&uid=11'>Pitchout</a> and <a href src='http://www.virzo.be/' target='_blank'>Virzo</a> for their tests and evaluations, sharing of ideas and ... motivations!</p>" . CRLF);



echo("<p><h1 style=\"text-align: center;\">Release Notes</h1></p>");


// Version 0.9.7 Beta

echo("<p><h2>Version: 0.9.7 Beta</h2></p>" . CRLF);

echo("<p><h3>Added/Modified:</h3></p>" . CRLF);

echo("<p>- New install, based upon Raspbian Jessie (Minibian), PHP7, MySQL5.6, kernel 4.4, ... <br> - Internet Temperature retry fixed and new source for those. </p>" . CRLF);

echo("<p>&nbsp;</p>" . CRLF);



// Version 0.9.6 Beta

echo("<p><h2>Version: 0.9.6 Beta</h2></p>" . CRLF);

echo("<p><h3>Added/Modified:</h3></p>" . CRLF);

echo("<p>- Update distri, kernel now in version 4.1.21+. <br> - Interface Internationalization: 3 Languages Supported: EN, FR, and NL. </p>" . CRLF);

echo("<p>&nbsp;</p>" . CRLF);



// Version 0.9.5 Beta

echo("<p><h2>Version: 0.9.5 Beta</h2></p>" . CRLF);

echo("<p><h3>Added/Modified:</h3></p>" . CRLF);

echo("<p>- Update distri, kernel now in version 4.1.16+. <br> - Admin Install Module fixed. <br> - Temperature RRDTool update fixed. <br> - Added Backup and restore. <br> - Fixed chauffage.php bug in controller less mode. <br> - Added possibility to move graphs to ftp or stp (every 5 minutes) <br> - Added: Thermostat name made variable. </p>" . CRLF);

echo("<p>&nbsp;</p>" . CRLF);



// Version 0.9.1 Alpha

echo("<p><h2>Version: 0.9.1 Alpha</h2></p>" . CRLF);

echo("<p><h3>Added/Modified:</h3></p>" . CRLF);

echo("<p>- Bug Config Thermostat. <br> - Changement &eacute;tat Lampes, ON > 0%. <br> - Added WebApp Icons for iOS & Android.</p>" . CRLF);

echo("<p>&nbsp;</p>" . CRLF);



// Version 0.9.0 Alpha

echo("<p><h2>Version: 0.9.0 Alpha</h2></p>" . CRLF);

echo("<p><h3>Added/Modified:</h3></p>" . CRLF);

echo("<p>First Public Alpha release.</p>" . CRLF);

echo("<p>- Added Surveillance Camera Page and admin<br>- Declares as Native App function on Android and iOS<br>- Some more bugs fixes.</p>" . CRLF);

echo("<p>&nbsp;</p>" . CRLF);



// Version 0.8.6 Alpha

echo("<p><h2>Version: 0.8.6 Alpha</h2></p>" . CRLF);

echo("<p><h3>Added/Modified:</h3></p>" . CRLF);

echo("<p>Third controlled Alpha release.</p>" . CRLF);

echo("<p>- Migrated from PHP mysql to PHP mysqli<br>- Cosmectic color changes in virtual Nest.<br>- Some minor/cosmetic bugs fixes.</p>" . CRLF);

echo("<p>&nbsp;</p>" . CRLF);



// Version 0.8.5 Alpha

echo("<p><h2>Version: 0.8.5 Alpha</h2></p>" . CRLF);

echo("<p><h3>Added/Modified:</h3></p>" . CRLF);

echo("<p>Second controlled Alpha release.</p>" . CRLF);

echo("<p>- Graph/Excell Activation in Admin Interface.<br>- 1-Wire GPIO Config in Admin Interface.<br>- Full DomoCAN V3 Server config and Monitoring in Admin Interface.<br>" .

	"- New Lamp/Power Plugs rendering engine, re-writen.<br>- New IN & OUT software bus to allow full modularity using \"Manufacturer\" modules.<br>- URL Module Proof of Concept (for the com bus).<br>" .

	"- Distribution updated on 20150429.</p>" . CRLF);

echo("<p>&nbsp;</p>" . CRLF);



echo("<p><h3>Bugs:</h3></p>" . CRLF);

// Version 0.8.0 Alpha

echo("<p><h2>Version: 0.8.0 Alpha</h2></p>" . CRLF);

echo("<p><h3>Added/Modified:</h3></p>" . CRLF);

echo("<p>Initial controlled Alpha release.</p>" . CRLF);



echo("<p><h3>Bugs:</h3></p>" . CRLF);

echo("<table width=100%><tr><td>ID</td><td>Component</td><td>Function</td><td>Assignee</td><td>Status</td><td>Version</td><td>Fixed in</td><td>Summary</td><td>Date</td></tr>" . CRLF);



echo("<tr><td>SR0001</td><td>Module/DomoCANv3/SysMap</td><td>SysMap Import</td><td>Henry</td><td>Fixed</td><td>Alpha 0.8.0</td><td>Alpha 0.8.1</td><td>When submiting files for DomoCANv3 Sysmap, \"Files Transfer ERROR or Incorrect FileS!\" error appears, operation aborded.</td><td>20150403</td></tr>" . CRLF);



echo("<tr><td>SR0002</td><td>Module/DomoCANv3/SysMap</td><td>SysMap Import</td><td>Henry</td><td>Fixed</td><td>Alpha 0.8.0</td><td>Alpha 0.8.1</td><td>When submiting files for DomoCANv3 Sysmap, \"Notice: Undefined index: Cartes_CFG\" happens, operation aborded.</td><td>20150403</td></tr>" . CRLF);



echo("<tr><td>SR0003</td><td>System</td><td>raspi-config</td><td>Henry</td><td>Fixed</td><td>Alpha 0.8.0</td><td>Alpha 0.8.1</td><td>No UDP Serveur is Selected, due to Incorrect directory in /usr/bin/raspi-config</td><td>20150404</td></tr>" . CRLF);



echo("<tr><td>SR0004</td><td>Admin/Lamps</td><td>Lamp Add</td><td>Henry</td><td>Need more info</td><td>Alpha 0.8.0</td><td>&nbsp;</td><td>Problem of position when adding the 10th Lamp on the plan. At first, it does'nt position correctly.</td><td>20150404</td></tr>" . CRLF);



echo("<tr><td>SR0005</td><td>Admin/Lamps</td><td>Lamp Add</td><td>Henry</td><td>Fixed</td><td>Alpha 0.8.0</td><td>Alpha 0.8.1</td><td>Text box too large, when adding the 10th Lamp on the plan. Creates missalignment when  scroll bar appears.</td><td>20150404</td></tr>" . CRLF);



echo("<tr><td>SR0006</td><td>Module/URL</td><td>URL Module</td><td>Henry</td><td>Fixed</td><td>Alpha 0.8.0</td><td>Alpha 0.8.1</td><td>Fatal Error on Moidule Call: \"Fatal error: Cannot redeclare ModConfig() (previously declared\"</td><td>20150412</td></tr>" . CRLF);



echo("<tr><td>SR0007</td><td>bon/temperatures</td><td>RRDTool Graphs</td><td>Henry</td><td>Fixed</td><td>Alpha 0.8.0</td><td>Alpha 0.8.1C</td><td>No sensor Graph created, due to depreciated reference to DB query.</td><td>20150414</td></tr>" . CRLF);





// 

echo("</table>" . CRLF);

?>
