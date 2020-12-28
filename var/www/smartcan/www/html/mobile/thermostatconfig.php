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
  
  
   
  //echo("zone Z=".$heatzone);
  // POST? => Update DB
	if (isset($_POST["ID"])) { 
	  if (substr($FormAction,0,6)=="Delete") {
	    // DELETE
		$sql = "DELETE FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE `id` = ".substr($FormAction,6).";";
		//echo("DELETE, SQL=$sql<br>".CRLF);
		$retour = mysqli_query($DB,$sql);
	  } else {
	    // UPDATES
		$heatzone = $_POST["zone"];
	    $form_id = $_POST["ID"]; $form_days = $_POST["day"]; $form_function = $_POST["function"]; $form_start = $_POST["start"]; $form_stop = $_POST["stop"]; $form_active = $_POST["active"]; $form_zones = $_POST["zones"];
	    $nbr_lines = sizeof($form_id); $form_line = 0; //echo("<br>SizeOf=$nbr_lines<br>");
	    while ($nbr_lines>$form_line) {
	      $db_id       = $form_id[$form_line];
		  $db_day      = implode("",$form_days[$form_line])."0"; //echo("form Day=".$db_day);
		  $db_zones    = $form_zones[$form_line];
		  $db_function = $form_function[$form_line]; //echo("Form_function=".$db_function);
		  $db_start    = $form_start[$form_line];
		  $db_stop     = $form_stop[$form_line]; //echo("ID=$db_id, Start=$db_start(".intval(substr($db_start,0,2).substr($db_start,3,2))."), Stop=$db_stop<br>");
		  $db_active   = $form_active[$form_line]; //echo("ID=$form_line, Active? ". $db_active."[".implode(",",$form_active)."]<br>".CRLF);
		  if ($heatzone=="0") {
			$update      = "`function` = '".$db_function."', `days` = '".$db_day."', `start` = '".$db_start."', `stop` = '".$db_stop."', `active` = '".$db_active."'";
			if ($db_active=="N") { $update .= " , `zones` = '1000000'"; }
		  } else {
			if ($db_active=="Y") { $db_active="1"; } else { $db_active="0"; }
			$update      = "`zones` = '".substr($db_zones,0,(intval($heatzone)-1)).$db_active.substr($db_zones,intval($heatzone),(7-intval($heatzone)))."'";
		  }
		  if ($db_id!="0") {
		    $sql = "UPDATE `" . TABLE_HEATING_TIMSESLOTS . "` SET ".$update." WHERE `id` = ".$db_id.";";
		  } else {
		    if (($db_day!="00000000") && (intval(substr($db_start,0,2).substr($db_start,3,2))<intval(substr($db_stop,0,2).substr($db_stop,3,2)))) {
		      if ($heatzone=="0") {
			    $sql = "INSERT INTO `" . TABLE_HEATING_TIMSESLOTS . "` (`id`, `function`, `days`, `start`, `stop`, `active`, `zones`) VALUES (NULL, '".$db_function."', '".$db_day."', '".$db_start.":00', '".$db_stop.":00', '".$db_active."', '1111111');";
			  } else {
				$zone_insert = str_pad("",(intval($heatzone)-1),"0")."1".str_pad("",(6-intval($heatzone)),"0");
			    $sql = "INSERT INTO `" . TABLE_HEATING_TIMSESLOTS . "` (`id`, `function`, `days`, `start`, `stop`, `active`, `zones`) VALUES (NULL, '".$db_function."', '".$db_day."', '".$db_start.":00', '".$db_stop.":00', 'N', '".$zone_insert."');";
			  } // END IF
		    } // END IF
		  } // END IF
	      $form_line++;
		  // Update DB
		  echo("<br>SQL=$sql<br>");
		  $retour = mysqli_query($DB,$sql);
	    } // END WHILE
	  } // END IF DELETE?
	} // END IF POST?
  
  
  // Determine Zone and function dependant parameters
  if ($heatzone!=0) { $selector = "`active`='Y' OR (`active`='N' AND `zones`='".str_pad("",(intval($heatzone)-1),"0")."1".str_pad("",(6-intval($heatzone)),"0")."')"; }  else { $selector = "`zones` LIKE '1______'"; }
  // Circul => no Boiler in Zones
  $sql = "SELECT * FROM `chauffage_clef` WHERE `clef`='circulateureauchaude';";
  $retour   = mysqli_query($DB,$sql);
  $row=mysqli_fetch_array($retour, MYSQLI_BOTH);
  $CirculMode = $row["valeur"];
  if ($CirculMode=="1" && $heatzone!=0) { $selector .= " AND `function`='HEATER'"; }
  
  // Count records in DB and Determine page record offset (Next Page?)
  $sql = "SELECT COUNT(*) as Count FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE ".$selector.";";
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
  echo("<div id='ConfigDIV' style='visibility:".$vis." z-index: 2; position: absolute; background: ".$bgcolor."; width: 785px; height: 450px; margin-top: 80px; opacity: .95; filter: alpha(opacity=80); ");
  echo("-moz-opacity: .1; border-width: 2px; border-style: solid; border-color: #292929; '>" . CRLF);
  
  
  if ($_GET['theme']) {$theme="&theme=".$_GET['theme'];} else {$theme="";}
  echo("<form name='Config' method='post' id='admin' action='./index.php?page=thermostat".$theme."&zone=".$heatzone."'>" . CRLF);
  echo("<table style='font-family: Calibri; font-style: normal; font-weight: 500; color: white' width='100%'>" . CRLF);
  echo("<input type='hidden' name='theme' id='theme' value='" . $theme . "'>" . CRLF);
  echo("<input type='hidden' name='zone' id='zone' value='" . $heatzone . "'>" . CRLF);
  
  echo("<tr><td width='35%' colspan=2>&nbsp;</td>" . CRLF);
  echo("<td width='35%' colspan=7 align='middle' valign='bottom'>".$msg["thermostat"]["Days"][$Lang]."</td>" . CRLF);
  echo("<td width='15%' colspan=3>&nbsp;</td>" . CRLF);
  echo("<td width='5%' align='right' valign='top'><a onClick=\"hideOverlay('ConfigDIV'); href='javascript:void(1);'\"><img align='absmiddle' src='../www/images/close.png' /></a></td></tr>" . CRLF);
  
  echo("<tr><td width='15%'><font color=".$bgcolor.">Zone</font></td>" . CRLF);
  echo("<td width='20%'>".$msg["thermostat"]["Function"][$Lang]."</td>" . CRLF);
  $j=0;
  while ($j<=6) {
    echo("<td width='5%' align='left'>" . substr($day_list[$Lang][$j],0,3) . "</td>" . CRLF);
	$j++;
  } // END WHILE
  echo("<td width='10%'>".$msg["thermostat"]["Start"][$Lang]."</td>" . CRLF);
  echo("<td width='10%'>".$msg["thermostat"]["End"][$Lang]."</td>" . CRLF);
  echo("<td width='5%'>");
  if (($FormAction=="Add") && ($heatzone!="0")) { echo("&nbsp;"); } else { echo($msg["thermostat"]["Active"][$Lang]); }
  echo("</td>" . CRLF);
  echo("<td width='5%'>&nbsp;</td></tr>" . CRLF);


  $line=0;
  //print("HeatZone=".$heatzone);
  
  $sql = "SELECT * FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE ".$selector." ORDER BY function,start,zones LIMIT ".$FormStart.",5;";
  //echo("sql = ".$sql."<br>");
  $retour   = mysqli_query($DB,$sql);
  while ($row=mysqli_fetch_array($retour, MYSQLI_BOTH)) {
    $db_id       = $row['id'];
	$db_function = $row['function'];
	$db_zones    = $row['zones'];
	$db_day      = $row['days'];
	$db_start    = $row['start'];
	$db_stop     = $row['stop'];
	$db_active   = $row['active'];
    if ($heatzone!="0" && (substr($db_zones,0,1)=="1")) {
	  $disabled = "disabled='disabled'";
	  $del      = "";
    } else {
      $disabled = "";
      $del      = "<a href='javascript:ConfirmSubmitform(\"Delete".$db_id."\",".$FormStart.");'><img width='30' height='46' valign='bottom' src='../www/images/delete.png' /></a>";
    }

    echo("<input type='hidden' name='ID[".$line."]' ' id='ID[".$line."]' value='" . $row['id'] . "'>" . CRLF);
	echo("<input type='hidden' name='zones[".$line."]' id='zones[".$line."]' value='" . $row['zones'] . "'>" . CRLF);
    echo("<tr><td width='15%' valign='bottom'><font color=".$bgcolor.">&nbsp;</font></td>" . CRLF);
    echo("<td width='20%' valign='bottom'>");
	
	//if ($disabled=="disabled='disabled'") {
	if ($heatzone!="0") {
	  echo("<input type='hidden' name='function[".$line."]' id='function[".$line."]' value='" . $db_function . "'>" . CRLF);
	  if ($db_active=="N") { echo("&nbsp;"); } else { echo($msg["thermostat"][ucfirst(strtolower($db_function))][$Lang]); }
	} else {
	  echo("<select ".$disabled." name='function[" . $line . "]' size='1' style='background: ".$bgcolor."; color: white; border-color: #292929;'>" .CRLF);
      echo("<option"); if ($db_function=="HEATER") { echo (" selected"); } 
	  echo(" value='HEATER'>".$msg["thermostat"]["Heater"][$Lang]."</option>" .CRLF);
      echo("<option"); if ($db_function=="BOILER") { echo (" selected"); } 
	  echo(" value='BOILER'>".$msg["thermostat"]["Boiler"][$Lang]."</option>" .CRLF);
      echo("</select>");
	} // END IF
	echo("</td>" . CRLF);
	
	$j=0;
	while ($j<=6) {
      echo("<td width='5%' align='left' valign='bottom'><input type=hidden name='day[".$line."][".$j."]' value='0'><input name='day[".$line."][".$j."]' type='checkbox' value='1' ".$disabled." title='" . $day_list[$Lang][$j] . "'");
	  if (substr($db_day,$j,1)=="1") { echo("checked"); }
	  echo("/></td>" . CRLF);
	  $j++;
	} // END WHILE
    
	if ($disabled=="disabled='disabled'") {
	  echo("<input type='hidden' name='start[".$line."]' ' id='start[".$line."]' value='" . $db_start . "'>" . CRLF);
	  echo("<input type='hidden' name='stop[".$line."]' ' id='stop[".$line."]' value='" . $db_stop . "'>" . CRLF);
	  echo("<td width='10%' valign='bottom'>".substr($db_start,0,5)."</td>" . CRLF);
	  echo("<td width='10%' valign='bottom'>".substr($db_stop,0,5)."</td>" . CRLF);
	} else {
      echo("<td width='10%' valign='bottom'><input type='time' ".$disabled." name='start[" . $line . "]' value='" . $db_start . "' style='background: ".$bgcolor."; color: white; border-color: #292929;'></td>" . CRLF);
      echo("<td width='10%' valign='bottom'><input type='time' ".$disabled." name='stop[" . $line . "]' value='" . $db_stop . "' style='background: ".$bgcolor."; color: white; border-color: #292929;'></td>" . CRLF);
    } // END IF
	
    echo("<td width='5%' align='left' valign='top'><input type=hidden name='active[".$line."]' value='N'><input id='active[" . $line . "]' name='active[" . $line . "]' type='checkbox' value='Y' style='visibility: hidden; title='Active?'' ");
	if (($heatzone=="0" && $db_active=="Y") || ($heatzone!="0" && substr($db_zones,($heatzone-1),1)=="1")) { echo("checked"); }
	echo("/><label for='active[" . $line . "]'><span class='ui'></span></label></td>" . CRLF);
    echo("<td width='5%' align='center' valign='bottom'>".$del."</td></tr>" . CRLF);
	$line++;
  } // END WHILE
 
  // ADD???
  if ($FormAction=="Add") {
    echo("<input type='hidden' name='ID[".$line."]' id='ID[".$line."]' value='0'>" . CRLF);
	echo("<input type='hidden' name='zones[".$line."]' id='zones[".$line."]' value='" . $row['zones'] . "'>" . CRLF);
    echo("<tr><td width='15%' valign='bottom'><font color=".$bgcolor.">&nbsp;</font></td>" . CRLF);
    echo("<td width='20%' valign='bottom'>");
	if ($heatzone!="0") {
	  echo("<input type='hidden' name='function[".$line."]' id='function[".$line."]' value='HEATER'>" . CRLF);
	  //echo($msg["thermostat"]["Heater"][$Lang]);
	} else {
	  echo("<select name='function[" . $line . "]' size='1' title='Fonction' style='background: ".$bgcolor."; color: white; border-color: #292929;'>" .CRLF);
      echo("<option value='HEATER'>".$msg["thermostat"]["Heater"][$Lang]."</option>" .CRLF);
      echo("<option value='BOILER'>".$msg["thermostat"]["Boiler"][$Lang]."</option>" .CRLF);
      echo("</select>");
	} // END IF
	echo("</td>" . CRLF);
	$j=0;
	while ($j<=6) {
      echo("<td width='5%' align='left' valign='bottom'><input type=hidden name='day[".$line."][".$j."]' value='0'><input name='day[".$line."][".$j."]' type='checkbox' value='1' title='" . $day_list[$Lang][$j] . "'/></td>" . CRLF);
	  $j++;
	} // END WHILE
  
    echo("<td width='10%' valign='bottom'><input type='time' name='start[" . $line . "]' value='12:00:00' style='background: ".$bgcolor."; color: white; border-color: #292929;'></td>" . CRLF);
    echo("<td width='10%' valign='bottom'><input type='time' name='stop[" . $line . "]' value='12:00:00' style='background: ".$bgcolor."; color: white; border-color: #292929;'></td>" . CRLF);
    echo("<td width='5%' align='left' valign='top'><input type=hidden name='active[".$line."]' value='N'>");
	if ($heatzone=="0") {
	  echo("<input name='active[" . $line . "]' id='active[" . $line . "]' type='checkbox' value='Y'  checked style='visibility: hidden;' /><label for='active[" . $line . "]'>");
    } else {
	  echo("&nbsp;");
	} // END IF
	echo("<span class='ui'></span></label></td>" . CRLF);
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
