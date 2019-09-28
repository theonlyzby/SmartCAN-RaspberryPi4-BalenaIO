<?PHP
// Includes
include_once($_SERVER['DOCUMENT_ROOT'].'/smartcan/www/conf/config.php');

//include_once(PATHCLASS . 'class.webadmin.php5');

// Main Function SysMap
function SysMap() {

  // Variables Passed Globally
  global $Access_Level;
  global $DB;
  global $msg;
  global $Lang;
  
  // Includes
  include_once "./lang/admin.module.SysMap.php";

  // First Admin Use?
  $sql              = "SELECT * FROM `ha_settings` WHERE `variable` = 'first_use_admin';";
  $query            = mysqli_query($DB,$sql);
  $row              = mysqli_fetch_array($query, MYSQLI_BOTH);
  $First_Use_Admin = $row['value'];

  // Action Requested via Form?  
  $action = html_postget("action");

  // Table Reset Request?
  if ($action=="truncate") {
    // Truncate ha_subsystem Table
	$sql = "TRUNCATE TABLE `domotique`.`ha_subsystem`;";
	$query = mysqli_query($DB,$sql);
    // Truncate ha_element Table
	$sql = "TRUNCATE TABLE `domotique`.`ha_element`;";
	$query = mysqli_query($DB,$sql);
	
	echo("<SCRIPT LANGUAGE=\"JavaScript\">" . CRLF);
	echo("  document.location.href=\"index.php?page=SysMap\" " . CRLF);
	echo("</SCRIPT>" . CRLF);
  } // END IF
  
  // Parse Files
  if ($action=="Refresh_Sysmap") {
    // Correct Posting?
    if ((($_FILES['Cartes_CFG']['error'] > 0) || ($_FILES['Cartes_CFG']['name']!="Cartes.cfg") || ($_FILES['Cartes_CFG']['size']==0)) 
        || (($_FILES['In16Name_CFG']['error'] > 0) || ($_FILES['In16Name_CFG']['name']!="In16Name.cfg") || ($_FILES['In16Name_CFG']['size']==0))
        || (($_FILES['GradNameS_CFG']['error'] > 0) || ($_FILES['GradNameS_CFG']['name']!="GradNameS.cfg") || ($_FILES['GradNameS_CFG']['size']==0))
        || (($_FILES['GradNameM_CFG']['error'] > 0) || ($_FILES['GradNameM_CFG']['name']!="GradNameM.cfg") || ($_FILES['GradNameM_CFG']['size']==0)) )	{
      echo("Files Transfer ERROR or Incorrect FileS!");
    } else {
	  if ($First_Use_Admin=="1") {
	    //
	    // Increase Admin First visit ... next step
	    //
	    $sql = "UPDATE `domotique`.`ha_settings` SET `value` = '2' WHERE `ha_settings`.`variable` = 'first_use_admin';";
	    $query = mysqli_query($DB,$sql);
	    $First_Use_Admin = "2";
	    echo("<table><tr><td width=\"40%\">&nbsp;</td><td>".CRLF);
	    echo("<br>&nbsp;<br>Chargement r&eacute;ussi !<br>".CRLF);
	    echo("<span class=\"readmore_b\"><a class=\"readmore\" href=\"index.php?page=Sensors\" style=\"color: white; align=middle;\" ;\">Suivant</a></span><br>".CRLF);
	    echo("<br><br>".CRLF);
	    echo("</td><td width=\"40%\">&nbsp;</td></tr></table>".CRLF);
	  } // END IF
	  //
      // Parse Cartes.cfg
	  //
	  $file_name = $_FILES['Cartes_CFG']['tmp_name'];
      $fp = fopen($file_name,"r");
      $Card=0;
      while (false !== ($char = fgetc($fp))) {
	    if (ord($char)==0) {
	      // New Card
		  if ($Card==0) {
	        // Horloge
		    // Card Name
		    $Card_Name[$Card] = "";
		    for ($x=1; $x<=8; $x++) {
		      $Card_Name[$Card] .= fgetc($fp);
		    } // END FOR
		    // Card Desc
		    $Card_Desc[$Card] = "";
		    for ($x=1; $x<=8; $x++) {
		      $Card_Desc[$Card] .= fgetc($fp);
		    } // END FOR
		    // Card Number
		    $Card_Number[$Card] = ord(fgetc($fp));
		    $char = fgetc($fp);
		    // Card Type
		    $Card_Type[$Card] = 20;
		    // Increase Card Count
		    $Card++;
	      } else {
	        // Other Cards
		    // Card Type
		    $Card_Type[$Card] = dechex(ord(fgetc($fp)));
		    // Card Number
		    $Card_Number[$Card] = dechex(ord(fgetc($fp)));
		    // Card Name
		    $Card_Name[$Card] = "";
		    for ($x=1; $x<=8; $x++) {
		      $Card_Name[$Card] .= fgetc($fp);
		    } // END FOR
		    // Card Desc
		    $Card_Desc[$Card] = "";
		    for ($x=1; $x<=8; $x++) {
		      $Card_Desc[$Card] .= fgetc($fp);
		    } // END FOR
		    $char = fgetc($fp);
		    $char = fgetc($fp);
		    // Increase Card Count
		    $Card++;
		  } // END IF
		  if ($Card_Name[$Card-1]) {
		    $Card_Number[$Card-1] = str_pad(dechex($Card_Number[$Card-1]),2, "0", STR_PAD_LEFT);
		    // Add or Modify Card in DB
		    $count = mysqli_num_rows(mysqli_query($DB,"SELECT * FROM `ha_subsystem` WHERE Manufacturer='DomoCAN v3.x' AND Type='0x".$Card_Type[$Card-1]."' AND Reference='0x".$Card_Number[$Card-1]."';"));
		    if ($count == 1) {
			  mysqli_query($DB,"UPDATE `ha_subsystem` SET Name='".utf8_encode($Card_Name[$Card-1])."' WHERE Manufacturer='DomoCAN v3.x' AND Type='0x".$Card_Type[$Card-1]."' AND Reference='0x".$Card_Number[$Card-1]."';");
		    } else {
			  mysqli_query($DB,"INSERT INTO `ha_subsystem` (id,Manufacturer,Type,Reference,Name) VALUES ('','DomoCAN v3.x','0x".$Card_Type[$Card-1]."','0x".$Card_Number[$Card-1]."','".utf8_encode($Card_Name[$Card-1])."');");
		    } // End IF
		    //echo("<b>New Card: </b>Name=".$Card_Name[$Card-1].", Type=".$Card_Type[$Card-1]." (".$Card_Desc[$Card-1]."), Number=".$Card_Number[$Card-1]."<br>");
		  } // END IF
	    } // END IF
	    //echo($char . " (" . ord($char) . ",0x" . dechex(ord($char)) . ")<br>");
      } // END WHILE

      //
      // Parse In16Name.cfg
      //
      //echo("<br><b>IN 16 Cards</b><br><br>");
      // Parse File
	  $file_name = $_FILES['In16Name_CFG']['tmp_name'];
      $fp = fopen($file_name,"r");
      $Card=0; $In_Num=0;
      while (false !== ($char = fgetc($fp))) {
	    //if ($In_Num==0) { echo("<b>Card 0x".dechex($Card)."?</b><br>"); }
	    $In_Desc[$Card][$In_Num]=$char; $In_FCS=ord($char);
	    for ($x=1; $x<=7; $x++) {
	      $char = fgetc($fp);
		  $In_Desc[$Card][$In_Num] .= $char;
		  $In_FCS = $In_FCS + ord($char);
	    } // END FOR
	    if ($In_FCS!=0) {
		  // Add or Modify Input in DB
		  $count = mysqli_num_rows(mysqli_query($DB,"SELECT * FROM `ha_element` WHERE `card_id` = '0x".str_pad(dechex($Card),2, "0", STR_PAD_LEFT)."' AND `element_reference`='0x".str_pad(dechex($In_Num),2, "0", STR_PAD_LEFT)."' AND `element_type`='0x22' ;"));
		  if ($count == 1) {
		    // Update
			mysqli_query($DB,"UPDATE `ha_element` SET `element_name`='".utf8_encode($In_Desc[$Card][$In_Num])."' WHERE `element_reference`='0x".str_pad(dechex($In_Num),2, "0", STR_PAD_LEFT)."' AND `card_id`='0x".str_pad(dechex($Card),2, "0", STR_PAD_LEFT)."' AND `element_type`='0x22';");
		  } else {
		    // Create
			mysqli_query($DB,"INSERT INTO `ha_element` (`id`, `card_id`, `element_type`, `element_reference`, `element_name`) VALUES (NULL, '0x" . str_pad(dechex($Card),2, "0", STR_PAD_LEFT) . "', '0x22', '0x" . str_pad(dechex($In_Num),2, "0", STR_PAD_LEFT) .
			"', '" . utf8_encode($In_Desc[$Card][$In_Num]) . "');");
		  } // END IF
		  //echo("IN 0x".dechex($In_Num)."=".$In_Desc[$Card][$In_Num]."<br>");
		} // END IF
	    $In_Num++;
	    if ($In_Num==16) { $In_Num=0; $Card++; }	  
	    //echo($char . " (" . ord($char) . ",0x" . dechex(ord($char)) . ")<br>");	   
      } // END WHILE

      //
      // Parse GradNameS.cfg
      //
      //echo("<br><b>GRAD 16 Cards<br><br>GRAD16 - Outputs</b><br><br>");
      // Parse File
	  $file_name = $_FILES['GradNameS_CFG']['tmp_name'];
      $fp = fopen($file_name,"r");
      $Card=0; $In_Num=0;
      while (false !== ($char = fgetc($fp))) {
	    //if ($In_Num==0) { echo("<b>Card 0x".dechex($Card)."?</b><br>"); }
	    $In_Desc[$Card][$In_Num]=$char; $In_FCS=ord($char);
	    for ($x=1; $x<=7; $x++) {
	      $char = fgetc($fp);
		  $In_Desc[$Card][$In_Num] .= $char;
		  $In_FCS = $In_FCS + ord($char);
	    } // END FOR

	    if ($In_FCS!=0) {
		  // Add or Modify Output in DB
		  $count = mysqli_num_rows(mysqli_query($DB,"SELECT * FROM `ha_element` WHERE `card_id` = '0x".str_pad(dechex($Card),2, "0", STR_PAD_LEFT)."' AND `element_reference`='0x".str_pad(dechex($In_Num),2, "0", STR_PAD_LEFT)."' AND `element_type`='0x11' ;"));
		  if ($count == 1) {
		    // Update
			mysqli_query($DB,"UPDATE `ha_element` SET `element_name`='".utf8_encode($In_Desc[$Card][$In_Num])."' WHERE `element_reference`='0x".str_pad(dechex($In_Num),2, "0", STR_PAD_LEFT)."' AND `card_id`='0x".str_pad(dechex($Card),2, "0", STR_PAD_LEFT)."' AND `element_type`='0x11';");
		  } else {
		    // Create
			mysqli_query($DB,"INSERT INTO `ha_element` (`id`, `card_id`, `element_type`, `element_reference`, `element_name`) VALUES (NULL, '0x" . str_pad(dechex($Card),2, "0", STR_PAD_LEFT) . "', '0x11', '0x" . str_pad(dechex($In_Num),2, "0", STR_PAD_LEFT) .
			"', '" . utf8_encode($In_Desc[$Card][$In_Num]) . "');");
		  } // END IF
		  //echo("OUT 0x".dechex($In_Num)."=".$In_Desc[$Card][$In_Num]."<br>");
		} // END IF
	    $In_Num++;
	    if ($In_Num==16) { $In_Num=0; $Card++; }	  
	    //echo($char . " (" . ord($char) . ",0x" . dechex(ord($char)) . ")<br>");
      } // END WHILE

      //
      // Parse GradNameM.cfg
      //
      //echo("<br><b>GRAD16 - Memories</b><br><br>");
      // Parse File
	  $file_name = $_FILES['GradNameM_CFG']['tmp_name'];
      $fp = fopen($file_name,"r");
      $Card=0; $In_Num=0;
      while (false !== ($char = fgetc($fp))) {
	    //if ($In_Num==0) { echo("<b>Card 0x".dechex($Card)."?</b><br>"); }
	    $In_Desc[$Card][$In_Num]=$char; $In_FCS=ord($char);
	    for ($x=1; $x<=15; $x++) {
	      $char = fgetc($fp);
		  $In_Desc[$Card][$In_Num] .= $char;
		  $In_FCS = $In_FCS + ord($char);
	    } // END FOR
	    if ($In_FCS!=0) {
		  // Add or Modify Memory in DB
		  $count = mysqli_num_rows(mysqli_query($DB,"SELECT * FROM `ha_element` WHERE `card_id` = '0x".str_pad(dechex($Card),2, "0", STR_PAD_LEFT)."' AND `element_reference`='0x".str_pad(dechex($In_Num),2, "0", STR_PAD_LEFT)."' AND `element_type`='0x16' ;"));
		  if ($count == 1) {
		    // Update
			mysqli_query($DB,"UPDATE `ha_element` SET `element_name`='".utf8_encode($In_Desc[$Card][$In_Num])."' WHERE `element_reference`='0x".str_pad(dechex($In_Num),2, "0", STR_PAD_LEFT)."' AND `card_id`='0x".str_pad(dechex($Card),2, "0", STR_PAD_LEFT)."' AND `element_type`='0x16';");
		  } else {
		    // Create
			mysqli_query($DB,"INSERT INTO `ha_element` (`id`, `card_id`, `element_type`, `element_reference`, `element_name`) VALUES (NULL, '0x" . str_pad(dechex($Card),2, "0", STR_PAD_LEFT) . "', '0x16', '0x" . str_pad(dechex($In_Num),2, "0", STR_PAD_LEFT) .
			"', '" . utf8_encode($In_Desc[$Card][$In_Num]) . "');");
		  } // END IF
		  //echo("MEMORY 0x".dechex($In_Num)."=".$In_Desc[$Card][$In_Num]."<br>");
		} // END IF
	    $In_Num++;
	    if ($In_Num==15) { $In_Num=0; $Card++; }	  
	    //echo($char . " (" . ord($char) . ",0x" . dechex(ord($char)) . ")<br>"); 
      } // END WHILE
    } // END IF
  } // END IF
  
  
  // Start Build Page ...

  echo("<h2 class='title'>" . ADMIN_SYSMAP_PAGE_NAME . "</h2>");
  echo("<div class='post_info'>&nbsp;</div>" . CRLF);

  echo("	<div class='postcontent' name='plan' " .
        "style='" .
        " width: 550px; margin-left: 50px;'>" . CRLF);
?>

<style>
img
{
position:relative;
}
</style>

  <?PHP
  // First Admin Use?
  if ($First_Use_Admin=="1") {
    echo("<div id='text-11' class='block widget_text'>");
	echo("<h2><font color=\"#33A5FF\"><b>Premi&egrave;re Utilisation:<br><br>");
	echo("1. Initiez la Base de Donn&eacute;es des Entr&eacute;es, Sorties et M&eacute;moires <br>en t&eacute;l&eacute;chargeant vos fichiers DomoGest 3.x <br><br>&nbsp;&nbsp;&nbsp;&nbsp; ");
	echo("... utilisez le bouton \"Charger DB\"");
	echo("&nbsp; <img src='./images/ArrowRightBlueGloss.gif' style=\"float:right;vertical-align:middle\" /></b></font></h2></div>");
  } else {
  
  // Lists Cards
  $sql = "SELECT `ha_subsystem`.`Manufacturer` AS Manufacturer, `ha_subsystem_types`.`Description` AS Card_Subsys_Name, `ha_subsystem`.`Type` AS Card_Type, `ha_subsystem`.`Reference` AS Card_Reference, " .
			"`ha_subsystem`.`Name` AS Card_Name FROM `ha_subsystem`, `ha_subsystem_types` WHERE `ha_subsystem`.`Manufacturer`=`ha_subsystem_types`.`Manufacturer` AND `ha_subsystem`.`Type` = `ha_subsystem_types`.`Type`;";
  //$sql = mysqli_real_escape_string($DB,"SELECT * FROM `ha_subsystem` WHERE 1;");
  $sql = str_replace(chr(92).chr(34),"'",$sql);
  //echo("SQL=$sql<br>");
  $i=1;
  $query = mysqli_query($DB,$sql);
  echo("<table width=\"100%\">" . CRLF);
  echo("<tr><td width=\"40%\">Sous-Syst&egrave;me</td>" . CRLF);
  echo("<td width=\"15%\">Reference</td>" . CRLF);
  echo("<td width=\"15%\">Nom</td>" . CRLF);
  echo("<td width=\"30%\">Element</td></tr>" . CRLF);
  $border_style = "";
  while ( $row = mysqli_fetch_array($query, MYSQLI_BOTH) ) {
    $Manufacturer = $row['Manufacturer'];
	$Subsys_Name  = $row['Card_Subsys_Name'];
    $Type         = $row['Card_Type'];
    $Reference    = $row['Card_Reference'];
    $Name         = $row['Card_Name'];
    // Display on page
	//echo("<input type='hidden' name='page' id ='page' value='Outputs'/>" . CRLF);
	$border_style = " style='border-top-style: groove; border-top-color: silver; border-top-width: medium;'";
	echo("<tr><td width=\"40%\" " . $border_style . ">" . $Manufacturer . " / <b>" . $Subsys_Name . "</b></td>" . CRLF);
	echo("<td width=\"15%\" " . $border_style . ">" . $Reference . "</td>" . CRLF);
	echo("<td width=\"15%\" " . $border_style . ">" . $Name . "</td>" . CRLF);
	//echo("<td width=\"30%\">&nbsp;</td></tr>" . CRLF);
	
	// Elements
	$sql = "SELECT HE.`element_name` AS El_Name, HE.`element_reference` AS El_Ref FROM `ha_element_types` AS HET LEFT JOIN   `ha_element` AS HE ON (HET.`Type` = HE.`element_type` ) " .
			"WHERE HET.`Manufacturer`= '" . $Manufacturer . "' AND HET.`subsystem_type`='" . $Type . "' AND HE.`card_id`='" . $Reference . "';";
	$el_query = mysqli_query($DB,$sql);
	$i=0;
	while ( $el_row = mysqli_fetch_array($el_query, MYSQLI_BOTH) ) {
	  $El_Name = $el_row['El_Name'];
	  $El_Ref  = $el_row['El_Ref'];
	  if ($i!=0) {
	    echo("<tr><td width=\"40%\" " . $border_style . ">&nbsp;</td>" . CRLF);
	    echo("<td width=\"15%\" " . $border_style . ">&nbsp;</td>" . CRLF);
	    echo("<td width=\"15%\" " . $border_style . ">&nbsp;</td>" . CRLF);
	  } // END IF
	  $i++;
	  echo("<td width=\"30%\" " . $border_style . ">" . $El_Name. " (" . $El_Ref . ")</td></tr>" . CRLF);
	  $border_style = "";
	} // END WHILE
	if ($i==0) { echo("<td width=\"30%\" " . $border_style . ">&nbsp;</td></tr>" . CRLF); }
	    $i++;
  } // End While
  echo("<tr><td width=\"40%\" " . $border_style . ">&nbsp;</td>" . CRLF);
  echo("<td width=\"15%\" " . $border_style . ">&nbsp;</td>" . CRLF);
  echo("<td width=\"15%\" " . $border_style . ">&nbsp;</td>" . CRLF);
  echo("<td width=\"30%\" " . $border_style . ">&nbsp;</td></tr>" . CRLF);
  echo("</table>" . CRLF);

  } // END IF
  
  echo("<div class='clear'></div>" . CRLF);
//  echo("<a href='javascript:void();' onClick=''>" .
//      "<img src='./images/ChangeButton.jpg' width='70px' heigth='60px' /></a>");
  echo("</div>	<!-- end .postcontent -->" . CRLF);

?>

    <body>
        <div id="data"></div>
    </body>

<div id="rss-3" class="block widget_rss">
<ul>
<?PHP

  echo("</ul>" . CRLF);
  echo("</div>" . CRLF);
?>


 
</div>
</div>

<?php
  echo("<div id='sidebar'>" . CRLF);
  echo("<div id='text-11' class='block widget_text'><br><br><br><h2>" . ADMIN_SYSMAP_SIDE_TITLE . "</h2>" . CRLF);
  echo("<img width='258' height='16' alt='Sidebar Hr' src='./images/sidebar_hr.png' class='divider'/>" . CRLF);
  echo("</div>" . CRLF);
  echo("<div id='rss-3' class='block widget_rss'>" . CRLF);
  echo("<ul>" . CRLF);
  echo("<br><br><br>Si cette liste n'est pas ou plus &agrave; jour:" . CRLF);

  
  echo("<form name='pos' method='post' id='ChangeLamp'".
       " action='" . htmlentities($_SERVER['PHP_SELF']) ."' enctype='multipart/form-data'>" . CRLF);
  echo("<input type='hidden' name='page' value='" .
        "SysMap'/>" . CRLF);
?>
  </ul></div>


<div class="postcontent">
  <br>&nbsp;<br>Vide ou Incompl&egrave;te ?<br>
  <span class="readmore_b"><a class="readmore" href="javascript:void(1);" style="color: white; align=middle;" onClick='showOverlay("NewSysMap","Cartes_CFG");';">Charger DB</a></span><br>
  <br><br> 
  <?php
  if ($First_Use_Admin!="1") { ?>
  Totalement incorrecte ?
  <span class="readmore_b">
    <a class="readmore" href="javascript:void(1);" style="color: white; align=middle;" onclick="submitform('truncate',1);">RAZ ?</a></span>
  <?php } // END IF ?>
	  <div class="clear"></div>
</div>



  <input type="hidden" name="action" value="" />    

</div>
<script type="text/javascript">
function submitform(action,ACK) {
  //alert("submit + Action="+action);  
  if ((ACK==0) || (confirm("Etes vous certain?") && (ACK==1))) {
    document.pos.action.value = action;
    document.pos.submit();
  }
}

 var op = 0;
 
 function showOverlay(divID,Ifocus) {
 var o = document.getElementById(divID);
 SurImpose('main',divID);
 o.style.visibility = 'visible';
 o.style.opacity = 0.05;
 op=op+5;
 fadein(op,divID);
 document.getElementById(Ifocus).focus();
 }

function SurImpose(Ref,Obj) {
  oElement = document.getElementById(Ref);
  ToMove =  document.getElementById(Obj);
  var iReturnValue = 0; 
  while( oElement != null ) {
    iReturnValue += oElement.offsetTop;
    oElement = oElement.offsetParent;
  }
  ToMove.style.top = (iReturnValue+5)+"px";
  oElement = document.getElementById('header');
  iReturnValue = 0; 
  while( oElement != null ) {
    iReturnValue += oElement.offsetLeft;
    oElement = oElement.offsetParent;
  }
  ToMove.style.left = (iReturnValue+5)+"px";
  return true;
}
 
function fadein(op,divID) {

 var o = document.getElementById(divID);
 opa = op/100;
 
 o.style.opacity = opa;
 op=op+5;

 if(op>=105) { return; }
 var cmd = "fadein(" + op.toString() + ",'" + divID.toString() + "')";
 setTimeout(cmd,50);
}

 function hideOverlay(lID) {
 var o = document.getElementById(lID);
 o.style.visibility = 'hidden';
 }


</script>

<div id="NewSysMap" style="visibility:hidden; z-index: 1; position: absolute; top: 200px; left: 185px; background: WhiteSmoke; width: 950px; height: 850px; opacity: .95; filter: alpha(opacity=80); -moz-opacity: .1; border-width: 2px; border-style: solid; border-color: #000000;">
<a href="javascript:void(1);" onClick="hideOverlay('NewSysMap');"><img width='14' height='14' align='absmiddle' src='../www/images/close.jpg' />&nbsp;Fermer</a>

<p align=center><h1 align=center>Rafraichir la liste de composants du syst&egrave;me</h1></p>
<br><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Soumettez vos fichiers de configurations g&eacute;n&eacute;r&eacute;s par DomoGest 3.x:<br><br><br>

     <table style="width: 80%; margin:auto">
	 <tr><td align="right"><label for="Cartes_CFG">Cartes.cfg :</label></td>
     <td><input type="file" name="Cartes_CFG" id="Cartes_CFG" /></td></tr>
	 
     <tr><td align="right"><label for="In16Name_CFG">In16Name.cfg :</label></td>
     <td><input type="file" name="In16Name_CFG" id="In16Name_CFG" /></td></tr>

     <tr><td align="right"><label for="GradNameS_CFG">GradNameS.cfg :</label></td>
     <td><input type="file" name="GradNameS_CFG" id="GradNameS_CFG" /></td></tr>
	 
     <tr><td align="right"><label for="GradNameM_CFG">GradNameM.cfg :</label></td>
     <td><input type="file" name="GradNameM_CFG" id="GradNameM_CFG" /></td></tr>
	 
     <tr><td>&nbsp;</td><td></td></tr>
	 </table>
	 
<div class="postcontent">
	 <span class="readmore_b">
    <a class="readmore" href="javascript:void(1);" style="color: white; align=middle;" onclick="submitform('Refresh_Sysmap',0);"><p>Envoyer</p></span>
	</div>

<br>

</div>
</form>

<?php
  mysqli_close($DB);
} // End of Function SysMap
