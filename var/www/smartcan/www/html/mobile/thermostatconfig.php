<?php
// PHP Error Reporting
//error_reporting(E_ALL);
//ini_set('display_errors', '1');


  
  //$day_list["fr"] = array("Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi","Dimanche");
  // Posted variables?
  $FormAction = ""; if (isset($_POST["FormAction"])) { $FormAction = $_POST["FormAction"]; }
  
  // Open DB
	$DB = mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
	mysqli_set_charset($DB,'utf8'); 
	mysqli_select_db($DB,mysqli_DB);
   
  // POST? => Update DB
	if (isset($_POST["ID"])) { 
	  if (substr($FormAction,0,6)=="Delete") {
	    // DELETE
		$sql = "DELETE FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE `id` = ".substr($FormAction,6).";";
		echo("DELETE, SQL=$sql<br>".CRLF);
		$retour = mysqli_query($DB,$sql);
	  } else {
	    // UPDATE
	    $form_id = $_POST["ID"]; $form_days = $_POST["day"]; $form_function = $_POST["function"]; $form_start = $_POST["start"]; $form_stop = $_POST["stop"]; $form_active = $_POST["active"];
	    $nbr_lines = sizeof($form_id); $form_line = 0; //echo("<br>SizeOf=$nbr_lines<br>");
	    while ($nbr_lines>$form_line) {
	      $db_id       = $form_id[$form_line];
		  $db_day      = implode("",$form_days[$form_line])."0"; //echo("form Day=".$db_day);
		  $db_function = $form_function[$form_line]; //echo("Form_function=".$db_function);
		  $db_start    = $form_start[$form_line];
		  $db_stop     = $form_stop[$form_line]; //echo("ID=$db_id, Start=$db_start(".intval(substr($db_start,0,2).substr($db_start,3,2))."), Stop=$db_stop<br>");
		  $db_active   = $form_active[$form_line]; //echo("ID=$form_line, Active? ". $db_active."[".implode(",",$form_active)."]<br>".CRLF);
		  if ($db_id!="0") {
		    $sql = "UPDATE `" . TABLE_HEATING_TIMSESLOTS . "` SET `function` = '".$db_function."', `days` = '".$db_day."', `start` = '".$db_start."', `stop` = '".$db_stop."', `active` = '".$db_active."' WHERE `id` = ".$db_id.";";
		  } else {
		    if (($db_day!="00000000") && (intval(substr($db_start,0,2).substr($db_start,3,2))<intval(substr($db_stop,0,2).substr($db_stop,3,2)))) {
		      $sql = "INSERT INTO `" . TABLE_HEATING_TIMSESLOTS . "` (`id`, `function`, `days`, `start`, `stop`, `active`) VALUES (NULL, '".$db_function."', '".$db_day."', '".$db_start.":00', '".$db_stop.":00', '".$db_active."');";
		    } // END IF
		  } // END IF
	      $form_line++;
		  // Update DB
		  //echo("<br>SQL=$sql<br>");
		  $retour = mysqli_query($DB,$sql);
	    } // END WHILE
	  } // END IF DELETE?
	} // END IF POST?
  
  /// Count records in DB and Determine page record offset (Next Page?)
  $sql = "SELECT COUNT(*) as Count FROM `" . TABLE_HEATING_TIMSESLOTS . "`;";
  $retour   = mysqli_query($DB,$sql);
  $row=mysqli_fetch_array($retour, MYSQLI_BOTH);
  $nbrRec = $row["Count"];	
  $FormStart  = 0;  if (isset($_POST["FormStart"]))  { $FormStart  = $_POST["FormStart"];  }
	
  // Javascripts
  echo("<script type='text/javascript'>" .CRLF);
  echo("function submitform(code,pagepointer) {" .CRLF);
  echo("  var sbt = document.getElementById(\"FormAction\");" . CRLF);
  echo("  sbt.value = code;" .CRLF);
  echo("  document.getElementById('FormStart').value=pagepointer;" .CRLF);
  echo("  document.Config.submit();" .CRLF);
  echo("}" .CRLF);
  
  echo("function ConfirmSubmitform(code,pagepointer) {" .CRLF);
  echo("  if (confirm(\"".$msg["thermostat"]["AreYouSure"][$Lang]."\")) {" .CRLF);
  echo("    var sbt = document.getElementById(\"FormAction\");" . CRLF);
  echo("    sbt.value = code;" .CRLF);
  echo("    document.getElementById('FormStart').value=pagepointer;" .CRLF);
  echo("    document.Config.submit();" .CRLF);  
  echo("  }" .CRLF);
  echo("}" .CRLF);

  echo("</script>");
  
  // Heater Config Window build (Hidden)
  $bgcolor= "#242424";
  // Visible?
  $vis = "hidden; top: 65px; left: 15px;"; if (($FormAction=="Previous") || ($FormAction=="Add") || ($FormAction=="Next") || (substr($FormAction,0,6)=="Delete")) {	$vis = "visible; top: 70px; left: 5px;"; } // END IF
  echo("<div id='ConfigDIV' style='visibility:".$vis." z-index: 2; position: absolute; background: ".$bgcolor."; width: 785px; height: 450px; opacity: .95; filter: alpha(opacity=80); ");
  echo("-moz-opacity: .1; border-width: 2px; border-style: solid; border-color: #292929; '>" . CRLF);

  echo("<form name='Config' method='post' id='admin' action='./index.php?page=thermostat'>" . CRLF);
  echo("<table style='font-family: Calibri; font-style: normal; font-weight: 500; color: white' width='100%'>" . CRLF);
  
  echo("<tr><td width='35%' colspan=2>&nbsp;</td>" . CRLF);
  echo("<td width='35%' colspan=7 align='middle' valign='bottom'>".$msg["thermostat"]["Days"][$Lang]."</td>" . CRLF);
  echo("<td width='15%' colspan=3>&nbsp;</td>" . CRLF);
  echo("<td width='5%' align='right' valign='top'><a href='javascript:void(1);' onClick=\"hideOverlay('ConfigDIV');\"><img align='absmiddle' src='../www/images/close.png' /></a></td></tr>" . CRLF);
  
  echo("<tr><td width='15%'><font color=".$bgcolor.">Zone</font></td>" . CRLF);
  echo("<td width='20%'>".$msg["thermostat"]["Function"][$Lang]."</td>" . CRLF);
  $j=0;
  while ($j<=6) {
    echo("<td width='5%' align='left'>" . substr($day_list[$Lang][$j],0,3) . "</td>" . CRLF);
	$j++;
  } // END WHILE
  echo("<td width='10%'>".$msg["thermostat"]["Start"][$Lang]."</td>" . CRLF);
  echo("<td width='10%'>".$msg["thermostat"]["End"][$Lang]."</td>" . CRLF);
  echo("<td width='5%'>".$msg["thermostat"]["Active"][$Lang]."</td>" . CRLF);
  echo("<td width='5%'>&nbsp;</td></tr>" . CRLF);


   $line=0;
  $sql = "SELECT * FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE 1 ORDER BY function,start LIMIT ".$FormStart.",5;";
  $retour   = mysqli_query($DB,$sql);
  while ($row=mysqli_fetch_array($retour, MYSQLI_BOTH)) {
    $db_id       = $row['id'];
	$db_function = $row['function'];
	$db_day      = $row['days'];
	$db_start    = $row['start'];
	$db_stop     = $row['stop'];
	$db_active   = $row['active'];

    echo("<input type='hidden' name='ID[".$line."]'='" . $db_id . "' id='ID[".$line."]' value='" . $row['id'] . "'>" . CRLF);
    echo("<tr><td width='15%' valign='bottom'><font color=".$bgcolor.">&nbsp;</font></td>" . CRLF);
    echo("<td width='20%' valign='bottom'><select name='function[" . $line . "]' size='1' style='background: ".$bgcolor."; color: white; border-color: #292929;'>" .CRLF);
    echo("<option"); if ($db_function=="HEATER") { echo (" selected"); } 
	  echo(" value='HEATER'>".$msg["thermostat"]["Heater"][$Lang]."</option>" .CRLF);
    echo("<option"); if ($db_function=="BOILER") { echo (" selected"); } 
	  echo(" value='BOILER'>".$msg["thermostat"]["Boiler"][$Lang]."</option>" .CRLF);
    echo("</select></td>" . CRLF);
	
	$j=0;
	while ($j<=6) {
      echo("<td width='5%' align='left' valign='bottom'><input type=hidden name='day[".$line."][".$j."]' value='0'><input name='day[".$line."][".$j."]' type='checkbox' value='1' title='" . $day_list[$Lang][$j] . "'");
	  if (substr($db_day,$j,1)=="1") { echo("checked"); }
	  echo("/></td>" . CRLF);
	  $j++;
	} // END WHILE
  
    echo("<td width='10%' valign='bottom'><input type='time' name='start[" . $line . "]' value='" . $db_start . "' style='background: ".$bgcolor."; color: white; border-color: #292929;'></td>" . CRLF);
    echo("<td width='10%' valign='bottom'><input type='time' name='stop[" . $line . "]' value='" . $db_stop . "' style='background: ".$bgcolor."; color: white; border-color: #292929;'></td>" . CRLF);

    echo("<td width='5%' align='left' valign='top'><input type=hidden name='active[".$line."]' value='N'><input id='active[" . $line . "]' name='active[" . $line . "]' type='checkbox' value='Y' style='visibility: hidden; title='Active?'' ");
	if ($db_active=="Y") { echo("checked"); }
	echo("/><label for='active[" . $line . "]'><span class='ui'></span></label></td>" . CRLF);
    echo("<td width='5%' align='center' valign='bottom'><a href='javascript:ConfirmSubmitform(\"Delete".$db_id."\",".$FormStart.");'><img width='30' height='46' valign='bottom' src='../www/images/delete.png' /></a></td></tr>" . CRLF);
	$line++;
  } // END WHILE
 
  // ADD???
  if ($FormAction=="Add") {
    echo("<input type='hidden' name='ID[".$line."]' id='ID[".$line."]' value='0'>" . CRLF);
    echo("<tr><td width='15%' valign='bottom'><font color=".$bgcolor.">&nbsp;</font></td>" . CRLF);
    echo("<td width='20%' valign='bottom'><select name='function[" . $line . "]' size='1' title='Fonction' style='background: ".$bgcolor."; color: white; border-color: #292929;'>" .CRLF);
    echo("<option value='HEATER'>Chaudiere</option>" .CRLF);
    echo("<option value='BOILER'>Boiler</option>" .CRLF);
    echo("</select></td>" . CRLF);
	$j=0;
	while ($j<=6) {
      echo("<td width='5%' align='left' valign='bottom'><input type=hidden name='day[".$line."][".$j."]' value='0'><input name='day[".$line."][".$j."]' type='checkbox' value='1' title='" . $day_list[$Lang][$j] . "'/></td>" . CRLF);
	  $j++;
	} // END WHILE
  
    echo("<td width='10%' valign='bottom'><input type='time' name='start[" . $line . "]' value='12:00:00' style='background: ".$bgcolor."; color: white; border-color: #292929;'></td>" . CRLF);
    echo("<td width='10%' valign='bottom'><input type='time' name='stop[" . $line . "]' value='12:00:00' style='background: ".$bgcolor."; color: white; border-color: #292929;'></td>" . CRLF);
    echo("<td width='5%' align='left' valign='top'><input type=hidden name='active[".$line."]' value='N'><input name='active[" . $line . "]' id='active[" . $line . "]' type='checkbox' value='Y' style='visibility: hidden;' ");
	echo("/><label for='active[" . $line . "]'><span class='ui'></span></label></td>" . CRLF);
    echo("<td width='5%' align='center' valign='bottom'>&nbsp;</td></tr>" . CRLF);  
  } // ENDIF
  
 
  // Last line ...
  echo("<tr><td width='35%' colspan=2 align='right'>&nbsp;<br><input type=hidden id='FormStart' name='FormStart' value=''>");
  if ($FormStart!=0) {
    echo("<a href='javascript:submitform(\"Previous\",".($FormStart-5).");'><img width='56' height='56' align='absmiddle' src='../www/images/previous.png' /></a>");
  } else {
    echo("<img width='56' height='56' align='absmiddle' src='../www/images/no-arrow.png' />");
  } // END IF
  if (($FormStart+5)>=$nbrRec) {
    if (($FormStart+5)==$nbrRec) { $FormStart = $FormStart + 5; }
    echo("<a href='javascript:submitform(\"Add\",".$FormStart.");'><img width='56' height='56' align='absmiddle' src='../www/images/add-2.png' /></a>");
  } else {
    echo("<img width='56' height='56' align='absmiddle' src='../www/images/no-arrow.png' />");
  } // END IF
  if (($FormStart+5)<$nbrRec) {
    echo("  <a href='javascript:submitform(\"Next\",".($FormStart+5).");'><img width='56' height='56' align='absmiddle' src='../www/images/next.png' /></a>");
  } else {
    echo("<img width='56' height='56' align='absmiddle' src='../www/images/no-arrow.png' /></a>");
  } // END IF
  echo("  <br></td>" . CRLF);
  echo("<td width='35%' colspan=7 valign='bottom'>&nbsp;</td>" . CRLF);
  echo("<td width='15%' colspan=3><input type='hidden' name='FormAction' id='FormAction'><br><a href='javascript:submitform(\"Save\",".$FormStart.");'><img width='56' height='56' align='absmiddle' src='../www/images/check.png' /></a></td>" . CRLF);
  echo("<td width='5%'>&nbsp;</td></tr>" . CRLF);

  echo("</table>" . CRLF);
  echo("</form>" . CRLF);
  echo("</div>" . CRLF);
  
  /* FERMETURE SQL */
  mysqli_close($DB);
  
?>
