<?PHP
// Main Function Temperatures
function Temps() {

  // Variables Passed Globally
  global $Access_Level, $DB, $msg, $Lang, $Moyenne;
  
  // Includes
  include "./lang/admin.module.Temps.php";
  
  // External Sensors, via Addon
  $dir          = PATHWEBADMIN . "Manufacturers";
  $ManufactDir  = scandir($dir);
  $i = 0; $nbrAddons = 0;
  while (isset($ManufactDir[$i+2])) { $i++; if (strpos($ManufactDir[$i+1],"Addon.Temps.php")!=false) { $AddOn[$nbrAddons] = substr($ManufactDir[$i+1],0,strpos($ManufactDir[$i+1],".")); $nbrAddons++; } }
  

  // Variables passed within the <Form> or URL
  $selected_level = html_postget("selected_level");
  if ($selected_level=="") { 
    $sql = mysqli_real_escape_string($DB,"SELECT * FROM `localisation` LIMIT 1;");
    $query = mysqli_query($DB,$sql);
    $row = mysqli_fetch_array($query, MYSQLI_BOTH);
    $selected_level = $row['lieu'];
  } // End IF

  // Action Requested via Form?  
  $action = html_postget("action");
  //if (ADMIN_DEBUG) { echo("Action=$action, Access Level=$Access_Level<br>"); }
  
  // Delete Temp
  if ($action=="DeleteTemp") {
    $TempD = html_get("TempD");
	// Other zone?
	$sql = mysqli_real_escape_string($DB,"SELECT * FROM `chauffage_sonde` WHERE `id` = \"" . $TempD . "\";");
	$sql = str_replace(chr(92).chr(34),"'",$sql);
	$query=mysqli_query($DB,$sql);
	$row = mysqli_fetch_array($query, MYSQLI_BOTH);
	$zone = $row['moyenne'];
	if ($zone!="0" && $zone!="1") {
	  $sql = mysqli_real_escape_string($DB,"SELECT COUNT(*) AS County FROM `chauffage_sonde` WHERE `moyenne` = \"" . $zone . "\";");
	  $sql = str_replace(chr(92).chr(34),"'",$sql);
	  $query=mysqli_query($DB,$sql);
	  $row = mysqli_fetch_array($query, MYSQLI_BOTH);
	  if ($row['County']==1) {
	    $sql = "DELETE FROM `chauffage_clef` WHERE `ZoneNber`='".$zone."';";
		mysqli_query($DB,$sql);
		$sql = "UPDATE `ha_thermostat_zones` SET `Name` = '' WHERE `ZoneNber` = '".$zone."';";
	    mysqli_query($DB,$sql);
	  }
	} // END IF
	$sql = mysqli_real_escape_string($DB,"DELETE FROM `chauffage_sonde` WHERE `id` = \"" . $TempD . "\";");
	$sql = str_replace(chr(92).chr(34),"'",$sql);
	if (!$query=mysqli_query($DB,$sql)) { log_this("Erreur DB[$sql]"); }
	$sql = mysqli_real_escape_string($DB,"DELETE FROM `chauffage_temp` " .
              " WHERE `id` = \"" . $TempD . "\";");
	$sql = str_replace(chr(92).chr(34),"'",$sql);
	if (!$query=mysqli_query($DB,$sql)) { log_this("Erreur DB[$sql]"); }
  } // End IF
  
  // Add and Modify Temps
  if (($action=="update") OR ($action=="AddTemp")) {
    // Graps form input
    $IdArray    = html_get("moveMe_id");
    $DescArray  = html_get("moveMe_desc");
    $XcoorArray = html_get("moveMe_xcoor");
    $YcoorArray = html_get("moveMe_ycoor");

    // Update DB
    $i = 1;
    while (isset($IdArray[$i])) { 
      $sql = mysqli_real_escape_string($DB,"UPDATE `chauffage_sonde` SET `img_x` = \"" . 
             ((int)$YcoorArray[$i]) . "\", " . "`img_y` = \"" .
             ((int)$XcoorArray[$i]) . "\", `description` = \"" .
             $DescArray[$i]  . "\" " .
             "WHERE `id` = \"" . $IdArray[$i] . "\";");
	  $sql = str_replace(chr(92).chr(34),"'",$sql);
	  //echo("<br><b>sql=</b> $sql <br>");
      if (!$query=mysqli_query($DB,$sql)) { log_this($msg["MAIN"]["DBerror"][$Lang]." [$sql]"); }
      $i++;
    } // End While

    // Create a new Temp on this level?
    if ($action=="AddTemp") {
	  $Temp_Name = html_get("Temp_Name");
	  $id_sonde  = html_get("id_sonde");
	  $Moyenne   = html_get("Moyenne"); if ($Moyenne=="") { $Moyenne = "0"; }
	  // Other Manufacturer Sensor?
	  if (substr($id_sonde,0,3)=="New") { 
	    include_once(PATHWEBADMIN . "Manufacturers".'/'.substr($id_sonde,3).'.Addon.Temps.php');
		$addOnClass_fullName = substr($id_sonde,3) . "_class";
		$addOnClass          = new $addOnClass_fullName();
		$id_sonde = $addOnClass->new();
	    //echo(substr($id_sonde,3)); exit; 
	  }
	  //echo("id_sonde=".$id_sonde.", Moyenne=".$Moyenne."<br>".CRLF);
	  //exit();
	  
	  // Saves sensor into DB
	  $sql = "INSERT INTO `chauffage_sonde` (`id`, `id_sonde`, `moyenne`, " .
			 "`localisation`, `img_x`, `img_y`, `description`) VALUES " .
			 "(NULL, \"" . $id_sonde . "\", \"" . $Moyenne . "\",  " .
			 "\"" . $selected_level . "\", \"1\", \"1\", \"" . $Temp_Name . "\");";
      
	  $sql = str_replace(chr(92).chr(34),"'",$sql);
	  //echo("<br><b>sql=</b> $sql <br>");
      if (!$query=mysqli_query($DB,$sql)) { log_this("Erreur DB[$sql]"); }
	  // Also in TEMP Table
	  $sql     = "SELECT * FROM `chauffage_sonde` WHERE `id_sonde`='" . $id_sonde . "';";
	  //echo("Search for id: sql=" . $sql ."<br>");
	  $query   = mysqli_query($DB,$sql);
	  $row     = mysqli_fetch_array($query, MYSQLI_BOTH);
      $idT     = $row['id'];
	  $moyenne = $row['moyenne'];
	  //echo("id=".$idT ."<br>");
	  if ($idT!=0) {
	    $sql     = "INSERT INTO `domotique`.`chauffage_temp` (`id`, `valeur`, `moyenne`, `update`) VALUES ('" . $idT . "', '0', '" . $moyenne . "', '0000-00-00 00:00:00');";
		//echo("<br><b>sql=</b> $sql <br>");
	    if (!$query=mysqli_query($DB,$sql)) { log_this("Erreur DB[$sql]"); }
		// Needs to declare temeprature and min into key table?
		if ($moyenne!="0" && $moyenne!="1") {
		  $sql   = "SELECT COUNT(*) AS County FROM `chauffage_clef` WHERE `clef`='temperature' AND `ZoneNber`=".$moyenne.";";
		  $query = mysqli_query($DB,$sql);
		  $row   = mysqli_fetch_array($query, MYSQLI_BOTH);
		  if ($row['County']==0) {
			$sql = "SELECT * FROM `chauffage_clef` WHERE `ZoneNber`=0;";
			$query = mysqli_query($DB,$sql);
			while ($row=mysqli_fetch_array($query, MYSQLI_BOTH)) {
			  if ($row['clef']=="temperature") { $temperature = $row['valeur']; }
			  if ($row['clef']=="tempminimum") { $tempminimum = $row['valeur']; } 
			} // END WHILE
			$sql = "INSERT INTO `chauffage_clef` (`id`, `clef`, `ZoneNber`, `valeur`) VALUES (NULL, 'temperature', '".$moyenne."', '".$temperature."'), (NULL, 'tempminimum', '".$moyenne."', '".$tempminimum."')";
			$query = mysqli_query($DB,$sql);
		  } // END IF
		} // END IF
	  } // END IF
    } // End IF
  } // End IF

  // Add Level
  if ($action=="AddLevel") {
    // Move File
	$Level_Name = html_postget("Level_Name");
	$File_Name = $_FILES['NewPlan']['name'];
	if (($Level_Name!="") AND ($File_Name)) {
      $Dest_Name = "../www/images/plans/plan_" . $Level_Name . ".png";
	  $Dest_Jpeg = "../www/images/plans/plan_" . $Level_Name . ".jpg";
	  if(copy($_FILES['NewPlan']['tmp_name'], $Dest_Name)) {
	    // Create New DB Entry
        $sql = mysqli_real_escape_string($DB,"INSERT INTO `domotique`.`localisation` (`id`, `lieu`) " .
		         "VALUES (NULL, \"" . $Level_Name . "\");");
		$sql = str_replace(chr(92).chr(34),"'",$sql);
		if (!$query=mysqli_query($DB,$sql)) { log_this($msg["MAIN"]["DBerror"][$Lang]." [$sql]"); }
		// Generate the jpg file
	    if (!png2jpg($Dest_Name, $Dest_Jpeg, 100)) { log_this($msg["MAIN"]["FileCopyError"][$Lang]." .JPG"); } // End IF
      } else {
	    log_this($msg["MAIN"]["FileCopyError"][$Lang]);
      }	  // End IF
	} // End IF
  } // End IF
	
    // Modify Level
    if ($action=="ModifyLevel") {
      $Level = html_get("Level"); // original Name
	  $LName = html_get("LName"); // New Name
	  if ((rename("../www/images/plans/plan_" . $Level . ".png" , "../www/images/plans/plan_" . $LName . ".png")) AND 
	      (rename("../www/images/plans/plan_" . $Level . ".jpg" , "../www/images/plans/plan_" . $LName . ".jpg"))) {
	    $sql = mysqli_real_escape_string($DB,"UPDATE `localisation` SET `lieu` = \"" . $LName .
               "\" WHERE `lieu` = \"" . $Level . "\" LIMIT 1;");
		$sql = str_replace(chr(92).chr(34),"'",$sql);
		if (!$query=mysqli_query($DB,$sql)) { log_this($msg["MAIN"]["DBerror"][$Lang]." [$sql]"); }
	    
		$sql = mysqli_real_escape_string($DB,"UPDATE `lumieres` SET `localisation` = \"" . $LName .
               "\" WHERE `localisation` = \"" . $Level . "\";");
		$sql = str_replace(chr(92).chr(34),"'",$sql);
		//echo("<br><b>sql=</b> $sql <br>");
		if (!$query=mysqli_query($DB,$sql)) { log_this($msg["MAIN"]["DBerror"][$Lang]."[$sql]"); }
		
		$selected_level = $LName;
		
	  } else {
	    log_this($msg["MAIN"]["FileCopyError"][$Lang]);
      }	  // End IF
    } // End IF

    // Modify Level Image
    if ($action=="ChangeImg") {
	  $Lvl_Name = html_post("Lvl_Name");
	  $Dest_Name = "../www/images/plans/plan_" . $Lvl_Name . ".png";
	  $Dest_Jpeg = "../www/images/plans/plan_" . $Lvl_Name . ".jpg";
	  if (!copy($_FILES['ImgFile']['tmp_name'], $Dest_Name)) {
	    log_this($msg["MAIN"]["FileCopyError"][$Lang]);
	  } else {
	    // Generate the jpg file
	    if (!png2jpg($Dest_Name, $Dest_Jpeg, 100)) { log_this($msg["MAIN"]["FileCopyError"][$Lang]." .JPG"); } // End IF
      }	  // End IF
    } // End IF
  
  
    // Delete Level
    if ($action=="DeleteLevel") {
      $Level = html_get("Level"); // original Name
	  if ((@unlink("../www/images/plans/plan_" . $Level . ".png")) AND (@unlink("../www/images/plans/plan_" . $Level . ".jpg"))) {
	    $sql = mysqli_real_escape_string($DB,"DELETE FROM `localisation` " .
               " WHERE `lieu` = \"" . $Level . "\";");
		$sql = str_replace(chr(92).chr(34),"'",$sql);
		if (!$query=mysqli_query($DB,$sql)) { log_this($msg["MAIN"]["DBerror"][$Lang]." [$sql]"); }
	    
		// Remove Lights
		$sql = mysqli_real_escape_string($DB,"DELETE FROM `lumieres` " .
               " WHERE `localisation` = \"" . $Level . "\";");
		$sql = str_replace(chr(92).chr(34),"'",$sql);
		if (!$query=mysqli_query($DB,$sql)) { log_this($msg["MAIN"]["DBerror"][$Lang]." [$sql]"); }
		
		// Remove Temperature Probe
		$sql = mysqli_real_escape_string($DB,"DELETE FROM `chauffage_sonde` " .
               " WHERE `localisation` = \"" . $Level . "\";");
		$sql = str_replace(chr(92).chr(34),"'",$sql);
		if (!$query=mysqli_query($DB,$sql)) { log_this($msg["MAIN"]["DBerror"][$Lang]." [$sql]"); }

	  } else {
	    log_this($msg["MAIN"]["FileDeleteError"][$Lang]);
      }	  // End IF
    } // End IF
    
  // Start Build Page ...

  // Existing levels
  $sql = mysqli_real_escape_string($DB,"SELECT * FROM `localisation` WHERE 1;");
  $query = mysqli_query($DB,$sql);
  $i=0;
  while ( $row = mysqli_fetch_array($query, MYSQLI_BOTH) ) {
    $level[$i] = $row['lieu'];
    $i++;
  } // End While

  echo("<h2 class='title'>" . $msg["TEMPS"]["Title"][$Lang] . "</h2>");
  echo("<form name='ChangeName' id='ChangeName' action='./index.php?page=²Temps' method='get'>" .
       "<div class='post_info'>&nbsp;Niveau: " . 
       "<input type='text' name='LName' id='LName' value='" . $selected_level . "'/>&nbsp;&nbsp;" .
       "<a href='javascript:void();' onClick='ActiveLevelChange(\"LName\")'><img src='./images/edit.png'/></a>&nbsp;&nbsp;" .
       "<a href='javascript:void();' onClick='ActiveLevelDelete()'><img src='./images/drop.png'/></a>&nbsp;&nbsp;" .
       "</div><input type='hidden' name='action' id ='action' value=''/>" .
	   "<input type='hidden' name='page' id ='page' value='Temps'/>" .
	   "<input type='hidden' name='Level' id ='Level' value='" . $selected_level . "'/>" .
	   "</form>" . CRLF);
  $ImgURL = "../www/images/plans/plan_" . $selected_level . ".png";
  $ImgURL = $ImgURL . "?" . rand(1,30000);
  $ImgURL = str_replace(" ",chr(37)."20",$ImgURL);
  echo("	<div class='postcontent' name='plan' " .
        "style='background-image: url(" . $ImgURL . "); position:relative; background-position: 50px 0px;" .
        " background-repeat: no-repeat; height: 355px;" .
        " width: 550px; margin-left: 50px;'>" . CRLF);
?>

<style>
img
{
position:relative;
}
</style>

<script type="text/javascript">
//document.onmousedown=coordinates;
document.onmouseup=mouseup;

function coordinates(object,e)
{
//movMeId=document.getElementById("moveMe");
//alert(object);
movMeId=document.getElementById(object);
if (e == null) { e = window.event;}
var sender = (typeof( window.event ) != "undefined" ) ? e.srcElement : e.target;
//alert(sender.id.substring(0,6));

if (sender.id.substring(0,6)=="moveMe")
  {
  //alert('-'+sender.id.substring(6,10)+'-');
  var ObjName = sender.id.substring(0,6);
  var ObjNber = sender.id.substring(6,10);

  document.pos.elements[ObjName + '_desc[' + ObjNber + ']'].style.backgroundColor='lime';

  mouseover=true;
  pleft=parseInt(movMeId.style.left);
  ptop=parseInt(movMeId.style.top);
  xcoor=e.clientX;
  ycoor=e.clientY;
  document.onmousemove=moveImage;
  return false;
  }
else { return false; }
}

function moveImage(e)
{
if (e == null) { e = window.event;}
var sender = (typeof( window.event ) != "undefined" ) ? e.srcElement : e.target;
//alert(sender.id.substring(6,7));
posx = (pleft+e.clientX-xcoor)+(60*(sender.id.substring(6,7)-1));
movMeId.style.left=pleft+e.clientX-xcoor+"px";
movMeId.style.top=ptop+e.clientY-ycoor+"px";
var sender = (typeof( window.event ) != "undefined" ) ? e.srcElement : e.target;

var ObjName = sender.id.substring(0,6);
var ObjNber = sender.id.substring(6,10);
document.pos.elements[ObjName + '_xcoor[' + ObjNber + ']'].value=posx+"px";
document.pos.elements[ObjName + '_ycoor[' + ObjNber + ']'].value=movMeId.style.top;
return false;
}

function mouseup(e) {
if (e == null) { e = window.event;}
document.onmousemove=null;
var sender = (typeof( window.event ) != "undefined" ) ? e.srcElement : e.target;

var ObjName = sender.id.substring(0,6);
var ObjNber = sender.id.substring(6,10);
document.pos.elements[ObjName + '_desc[' + ObjNber + ']'].style.backgroundColor='white';
document.pos.elements[ObjName + '_desc[' + ObjNber + ']'].focus();
document.pos.elements[ObjName + '_desc[' + ObjNber + ']'].setSelectionRange(300, 300);
}

function tempfocus(e)
{
document.getElementById("moveMe"+e).src="../www/images/fond_temperature_on.png";
//alert("focus"+e);
}

function tempUNfocus(e)
{
document.getElementById("moveMe"+e).src="../www/images/fond_temperature_silver.png";
}
</script>

  <?PHP
  // Places Temp Boxes on plan
  $sql = mysqli_real_escape_string($DB,"SELECT * FROM `chauffage_sonde` WHERE " .
           "`localisation`=\"" . $selected_level . "\" ORDER BY ID ASC;");
  $sql = str_replace(chr(92).chr(34),"'",$sql);
  //echo("SQL=$sql<br>");
  $i=1;
  $query = mysqli_query($DB,$sql);
  while ( $row = mysqli_fetch_array($query, MYSQLI_BOTH) ) {
    $id[$i]          = $row['id'];
    $description[$i] = $row['description'];
	$moyenne[$i]       = $row['moyenne'];
    $img_x[$i]       = $row['img_x'];
    $img_y[$i]       = $row['img_y'];
    $web_offset[$i]  = ($i-1)*60;
	if (($moyenne[$i]!=0) && ($moyenne[$i]!=1)) { $img[$i] = $moyenne[$i]; } else { $img[$i] = "silver"; }
    // Display on page
    echo("<img id='moveMe" . $i . "' " .
         "src='../www/images/fond_temperature_".$img[$i].".png' " .
         "onmousedown='coordinates(\"moveMe" . $i . "\")' />" . CRLF);
    echo("<script type='text/javascript'>" . CRLF);
    echo("//Movable image" . CRLF);
    echo("movMeId".$i."=document.getElementById(\"moveMe".$i."\");" . CRLF);
    echo("//image starting location" . CRLF);
    echo("movMeId".$i.".style.top=\"".$img_x[$i]."px\";" . CRLF);
    echo("movMeId".$i.".style.left=\"".
         ($img_y[$i]-$web_offset[$i])."px\";" . CRLF);
    echo("</script>" . CRLF);

    $i++;
  } // End While


  echo("<div class='clear'></div>" . CRLF);
  echo("</div>	<!-- end .postcontent -->" . CRLF);

?>
<div id="rss-3" class="block widget_rss">
<ul>
<?PHP
  $i=0;
  echo("<table width='100%'><tr><td width='70%'>");
  if (isset($level[$i])) { echo($msg["MAIN"]["OtherLevels"][$Lang].":<br>"); }
  while (isset($level[$i])) {
    if ($level[$i]!=$selected_level) {
      echo("<li>&nbsp;<a class='rsswidget' href='./index.php?page=Temps&selected_level=" .
			$level[$i] . "' title='".$msg["MAIN"]["EditLevel"][$Lang]."'>" .
            $level[$i] .
            "</a> <span class='rss-date'></span></li>");
    } // End IF
    $i++;
  } // End WHile
  echo("</td><td width='30%' style='vertical-align:middle'><a href='javascript:void();' " .
       "onClick=\"showOverlay('ChangeImg','Lvl_Name');\">" .
       "<img src='./images/ChangeButton.jpg' width='70px' heigth='60px' " .
	   "title=\"".$msg["MAIN"]["ChangeLevelImage"][$Lang]."\"/></a></td></tr></table>");
  
  echo("</ul>" . CRLF);
  echo("<div class='post_info'><a href='javascript:void();' onClick=\"showOverlay('AddLevel','Level_Name');\" id='clickMe'><img width='14' height='14' align='absmiddle' src='../www/images/ajouter.png' />&nbsp;".$msg["MAIN"]["AddLevel"]["en"]."</a></div>" . CRLF);
  echo("</div>" . CRLF);
?>

<div id="ChangeImg" style="visibility:hidden; z-index: 1; position: absolute; top: 200px; left: 185px; background: WhiteSmoke; width: 950px; height: 850px; opacity: .95; filter: alpha(opacity=80); -moz-opacity: .1; border-width: 2px; border-style: solid; border-color: #000000;">
<a href="javascript:void(1);" onClick="hideOverlay('ChangeImg');"><img width='14' height='14' align='absmiddle' src='../www/images/close.jpg' />&nbsp;<?php echo($msg["MAIN"]["Close"][$Lang]); ?></a>
<p align=center><h1 align=center><?php echo($msg["MAIN"]["ChangeLevelImage"][$LAng]." ".$selected_level);?></h1></p>
<br><br><br><br>
<form id="NewImg" name="NewImg" enctype="multipart/form-data" action="./index.php?page=Temps&selected_level=<?php echo($selected_level);?>" method="post" >
<table width="100%">
<tr><td width="20%">&nbsp;</td>
<td width="30%" align="right"><?php echo($msg["MAIN"]["LevelImage"][$Lang]); ?>:&nbsp;&nbsp;&nbsp;<br><br></td> 
<td width="50%"><input id="ImgFile" name="ImgFile" type="file" accept="image/png" /></td></tr>
<tr><td width="20%">&nbsp;<input type="hidden" name="FakeLvl_Name" id="FakeLvl_Name" value="01234567890azertyuiop"/>
<input type="hidden" name="Lvl_Name" id="Lvl_Name" value="<?php echo($selected_level);?>"/>
<input type="hidden" name="action" id="action" value="ChangeImg"/></td></td>
<td width="30%"><div class="postcontent" style="valign:absmiddle;">
  <span class="readmore_b">
    <a class="readmore" href="javascript:void(1);" onClick="CheckForm('NewImg','FakeLvl_Name','ImgFile');"><?php echo($msg["MAIN"]["Modify"][$Lang]); ?></a></span>
  <div class="clear"></div>
</div>
</td>
<td width="50%" align="left"></td></tr>
</table><br>
</form>
</div>

<div id="AddLevel" style="visibility:hidden; z-index: 1; position: absolute; top: 200px; left: 185px; background: WhiteSmoke; width: 950px; height: 850px; opacity: .95; filter: alpha(opacity=80); -moz-opacity: .1; border-width: 2px; border-style: solid; border-color: #000000;">
<a href="javascript:void(1);" onClick="hideOverlay('AddLevel');"><img width='14' height='14' align='absmiddle' src='../www/images/close.jpg' />&nbsp;<?php echo($msg["MAIN"]["Close"][$Lang]); ?></a>
<p align=center><h1 align=center><?php echo($msg["MAIN"]["AddLevel"][$Lang]); ?></h1></p>
<br><br><br><br>

 <form id="NewLevel" name="NewLevel" enctype="multipart/form-data" action="./index.php?page=Temps&action=AddLevel" method="post" >
<table width="100%">
<tr><td width="20%">&nbsp;</td>
<td width="30%" align="right"><?php echo($msg["MAIN"]["LevelName"]["en"]); ?>:&nbsp;&nbsp;&nbsp;<br><br></td>
<td width="50%"><input name="Level_Name" id="Level_Name" type="text" size="20" value="" /></td></tr>
<tr><td width="20%">&nbsp;</td>
<td width="30%" align="right"><?php echo($msg["MAIN"]["LevelImage"][$Lang]); ?>:&nbsp;&nbsp;&nbsp;<br><br></td> 
<td width="50%"><input id="NewPlan" name="NewPlan" type="file" accept="image/png" /></td></tr>
<tr><td width="20%">&nbsp;</td>
<td width="30%"><div class="postcontent">
  <span class="readmore_b">
    <a class="readmore" href="javascript:void(1);" onClick="CheckForm('NewLevel','Level_Name','NewPlan');"><?php echo($msg["MAIN"]["Add"][$Lang]); ?></a></span>
  <div class="clear"></div>
</div>
</td>
<td width="50%" align="left"></td></tr>
</table><br>
</form>

</div>

<script type="text/javascript">
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

function CheckLevelName(lID) {
   var Name   = document.getElementById(lID).value;
   // Empty?
   if (Name.length==0) {
	 alert("<?php echo($msg["MAIN"]["EmptyLevelName"][$Lang]); ?>!");
     document.getElementById(lID).focus();
	 return;
   }
   // Correct Name?
   var NameOut = "";
   var j = 0;
   var d = "";

   var l = Name.length-1;
   while (Name.substr(l,1) == " ") {
     l = l - 1;
   }
   l =l + 1;

   for (i = 0 ; i < l ;i++) {
     c = Name.substr(i,1);
     if ((((c >= "0") && (c <= "9")) || ((c >= "a") 
      && (c <= "z")) || ((c >= "A") && (c <= "Z"))) || ((c==" ") 
      && (d!=" "))) {
        NameOut += c;
        d=c;
     }
   }
   if (NameOut != Name) {
    alert("<?php echo($msg["MAIN"]["UseOnlyAZ"][$Lang]); ?>");
    document.getElementById(lID).value = NameOut;
    document.getElementById(lID).focus();
    return;
   }
   

   // Level Exits?
   <?php
   $i   = 0;
   $var = "var Levels=new Array(";
   while ( isset($level[$i]) ) {
     if ($i>=1) { $var .= ","; }
     $var .= "\"" . $level[$i] . "\"";	
     $i++;
   }
   $i--;
   $var .= ");" . CRLF;
   if ($i>=0) {
   echo($var);
   ?>
   var i = 0;
   while (i<=<?php echo($i);?>) {
     if (Name==Levels[i]) {
	   alert("<?php echo($msg["MAIN"]["LevelAlreadyExists"][$Lang]); ?>!");
	   document.getElementById(lID).focus();
       return;
	 }
	 i=i+1;
   }
   <?php } else { echo($msg["MAIN"]["DBempty"][$Lang] . CRLF); } // End IF ?>
   return true;
}

 function CheckForm(FormName,lName,ImgFile) {
   // Check Level Name
   if (!CheckLevelName(lName)) {alert("NOK"); return;}
   // File Well Selected?
   var FlName   = document.getElementById(ImgFile).value;
   if (FlName=="") {
     alert("<?php echo($msg["MAIN"]["NoFileError"][$Lang]); ?>");
	 return;	
   }
   

   // File = png?
   var hash = { 
     '.gif'  : 1,
	 '.jpg'  : 1,
	 '.jpeg' : 1,
	 '.wmf'  : 1,
	 '.pdf'  : 1,
   }; 
   var re = /\..+$/; 
   var ext = FlName.match(re);
   if (hash[ext]) { 
     alert("<?php echo($msg["MAIN"]["UseOnlyPNG"]["fr"]); ?>!");
     var Name = document.getElementById(lName).value;	 
	 var fld  = document.getElementById(ImgFile);
     fld.form.reset();
	 document.getElementById(lName).value = Name;
     fld.focus();
     return;
   }
   // Form OK => Submit
   document.getElementById(FormName).submit();
 }

 function ActiveLevelChange(lID) {
  	if (CheckLevelName(lID)) {
	  document.getElementById("action").value="ModifyLevel";
	  document.getElementById("ChangeName").submit();
	}
 }
 
 function ActiveLevelDelete() {
   if (confirm("Etes vous certain?")) {
	 document.getElementById("action").value="DeleteLevel";
	 document.getElementById("ChangeName").submit();
   }	
 }
 
</script>
 
</div>
</div>



<?php
  echo("<div id='sidebar'>" . CRLF);
  echo("<div id='text-11' class='block widget_text'><br><br><br><h2>" . $msg["TEMPS"]["SideTitle"][$Lang] . "</h2>" . CRLF);
  echo("<img width='258' height='16' alt='Sidebar Hr' src='./images/sidebar_hr.png' class='divider'/>" . CRLF);
  echo("</div>" . CRLF);
  echo("<div id='rss-3' class='block widget_rss' style='height:280pt;overflow:auto'>" . CRLF);
  echo("<ul>" . CRLF);

  // List Temps
  echo("<form name='pos' method='get' id='ChangeTemp'".
       " action='" . htmlentities($_SERVER['PHP_SELF']) ."'>" . CRLF);
  echo("<input type='hidden' name='page' value='" .
        "Temps'/>" . CRLF);
  echo("<input type='hidden' name='selected_level' value='" .
        $selected_level . "'/>" . CRLF);
  echo("<input type='hidden' name='TempD' value=''/>" . CRLF);
  $i = 1;
  while (isset($id[$i])) {
    echo("  <li><input type='text'" .
         " name='moveMe_desc[".$i."]'" .
         " onfocus='tempfocus(\"$i\")' " .
         " onblur='tempUNfocus(\"$i\")' " . 
         " value='" . $description[$i] . 
         "' />" . CRLF);
	if ($i!=1) { echo("<a href='javascript:void();' onClick='TempDelete(".$id[$i].");'><img src='./images/drop.png'/></a>" . CRLF); }
    echo("</li>  <input type='hidden'" .
         " name='moveMe_id[".$i."]' value='" . $id[$i] . "' />" . CRLF);

    echo("  <input type='hidden'" .
         " name='moveMe_xcoor[".$i."]' value='" . $img_y[$i] .
         "px' />" . CRLF);
    echo("  <input type='hidden'" .
         " name='moveMe_ycoor[".$i."]' value='" . $img_x[$i] . 
         "px' />" . CRLF);
    $i++;
  } // End While
?>

&nbsp;&nbsp;<a href='javascript:void(1);' onClick='showOverlay("NewTemp","Temp_Name");'><img width='14' height='14' align='absmiddle' src='../www/images/ajouter.png' />&nbsp;&nbsp;&nbsp;<?php echo($msg["TEMPS"]["AddSensor"][$Lang]); ?></a>
  </ul></div>


<div class="postcontent">

		<span class="readmore_b">
<a class="readmore" href="javascript:void(1);" style="color: white;" onclick="submitform('update');"><?php echo($msg["MAIN"]["update"][$Lang]); ?></a></span>
		<div class="clear"></div>
	</div>

<script type="text/javascript">

// Submit Modify or Add Temps
function TempDelete(id) {
  document.pos.TempD.value = id;
  submitform("DeleteTemp");
}

function CheckDropSubmitform(field,drop,action) {
  var ll = document.getElementById(field).value;
  var dd = document.getElementById(drop).value;
  <?php
  $nbrAddons = 0;
  while (isset($AddOn[$nbrAddons])) {  
    include_once(PATHWEBADMIN . "Manufacturers".'/'.$AddOn[$nbrAddons].'.Addon.Temps.php');
    $addOnClass_fullName = $AddOn[$nbrAddons] . "_class";
    $addOnClass          = new $addOnClass_fullName();
    $addOnClass->HTMLcheck();
    $nbrAddons++;
  } // END WHILE
  echo(CRLF)
  ?>
  if (ll.length>=1) {
    if (dd.length>=1) {
      submitform(action);
    } else {
      alert("<?php echo($msg["TEMPS"]["NoSelectedSensor"][$Lang]); ?>!");
	  return;
    }
  } else {
    alert("<?php echo($msg["MAIN"]["EmptyDescription"][$Lang]); ?>!");
	return;
  } 

}

function CheckSubmitform(field,action) {
  var ll = document.getElementById(field).value;
  if (ll.length>=1) {
    submitform(action);
  } else {
    alert("<?php echo($msg["MAIN"]["EmptyDescription"][$Lang]); ?>!");
	return;
  }
}

function submitform(action) {
  //alert("submit + Action="+action);
  document.pos.action.value = action;
  document.pos.submit();
}
</script>

  <input type="hidden" name="action" value="" />    

</div>


<div id="NewTemp" style="visibility:hidden; z-index: 1; position: absolute; top: 200px; left: 185px; background: WhiteSmoke; width: 950px; height: 850px; opacity: .95; filter: alpha(opacity=80); -moz-opacity: .1; border-width: 2px; border-style: solid; border-color: #000000;">
<a href="javascript:void(1);" onClick="hideOverlay('NewTemp');"><img width='14' height='14' align='absmiddle' src='../www/images/close.jpg' />&nbsp;<?php echo($msg["MAIN"]["Close"][$Lang]); ?></a>

<p align=center><h1 align=center><?php echo($msg["TEMPS"]["AddSensorToLevel"][$Lang]." ".$selected_level);?></h1></p>
<br><br><br><br>
<table width="100%">
<tr><td width="20%">&nbsp;</td>
<td width="30%" align="right"><?php echo($msg["MAIN"]["Description"][$Lang]); ?>&nbsp;&nbsp;&nbsp;<br><br></td> 
<td width="50%"><input id="Temp_Name" name="Temp_Name" type="text"/></td></tr>
<tr><td width="20%">&nbsp;</td>
<td width="30%" align="right"><?php echo($msg["TEMPS"]["Sensor"][$Lang]); ?>&nbsp;&nbsp;&nbsp;<br><br></td> 
<td width="50%"><select name="id_sonde" id="id_sonde" onchange="change('id_sonde');">
<?php

// List all already used Sensors to exclude them later in drop box
$sql = "SELECT * FROM  `" . TABLE_CHAUFFAGE_SONDE . "` WHERE 1;";
$query = mysqli_query($DB,$sql);
$j = 0;
while ($row = mysqli_fetch_array($query, MYSQLI_BOTH)) {
  $SensorID[$j] = $row["id_sonde"];
  $j++;
} // END WHILE
// Scans 1 Wire Directory for NEW Temp Probe
echo("<option value=''>".$msg["MAIN"]["Choose"][$Lang]."</option>" . CRLF);
// addOns  
$nbrAddons = 0;
while (isset($AddOn[$nbrAddons])) {  
  include_once(PATHWEBADMIN . "Manufacturers".'/'.$AddOn[$nbrAddons].'.Addon.Temps.php');
  $addOnClass_fullName = $AddOn[$nbrAddons] . "_class";
  $addOnClass          = new $addOnClass_fullName();
  $addOnClass->HTMLoption();
  $nbrAddons++;
} // END WHILE

 // 1 wire
if (ONEWIRE_MODE=="OWFS") {
  require "/usr/share/php/OWNet/ownet.php";
  $ow=new OWNet("tcp://127.0.0.1:" . ONEWIRE_OWSERVER_PORT);
  $content = $ow->get("/",OWNET_MSG_DIR,true);
  $i=0;
  while (isset($content[$i]["data"])) {
    $sensor = $content[$i]["data"];
    $sensor = substr($sensor,1);
    if (substr($sensor,0,2)=="28") {
      $selected = "";
	  $jj=0; $k=0; while ($jj<=$j) { if ($SensorID[$jj]==$sensor) { $k=1; } $jj++; }
      if ($k==0) { echo("<option value='" . $sensor . "' " . $selected . ">" . $sensor . "</option>" . CRLF); }
    } // End IF
    $i++;
  } // End While
  closedir($handle);
} // END IF
$dir = "/sys/bus/w1/devices";
if ((ONEWIRE_MODE=="RPI") && (file_exists($dir))) {
  $dh  = opendir($dir);
  while (false !== ($filename = readdir($dh))) {
    $files[] = $filename;
  } // END While
  sort($files);
  $i=2;
  while (isset($files[$i])) {
    if (substr($files[$i],0,2)=="28") {
      $selected = "";
	  $jj=0; $k=0; while ($jj<=$j) { if ($SensorID[$jj]==$files[$i]) { $k=1; } $jj++; }
      if ($k==0) { echo("<option value='" . $files[$i] . "' " . $selected . ">" . $files[$i] . "</option>" . CRLF); }
    } // End IF
    $i++;
  } // END WHILE
} // END IF

echo("" . CRLF);

?>

</td></tr>

<?php
// Position HTML configs of addOns
  $nbrAddons = 0;
  while (isset($AddOn[$nbrAddons])) {  
    include_once(PATHWEBADMIN . "Manufacturers".'/'.$AddOn[$nbrAddons].'.Addon.Temps.php');
	$addOnClass_fullName = $AddOn[$nbrAddons] . "_class";
	$addOnClass          = new $addOnClass_fullName();
	$addOnClass->HTMLconfig();
	$nbrAddons++;
  } // END WHILE
  

?>

<tr><td width="20%">&nbsp;</td>
<td width="30%" align="right"><div id="Mean01" style="display: table-cell;"><?php echo($msg["TEMPS"]["UseItForMean"][$Lang]); ?></div>&nbsp;&nbsp;&nbsp;<br><br></td> 
<td width="50%"><div id="Mean02" style="display: table-cell;"><input type="checkbox" checked name="Moyenne" id="Moyenne" value="1"></div></td></tr>

<tr><td width="20%">&nbsp;
<td width="30%"><div class="postcontent">
  <span class="readmore_b"><p>
    <a class="readmore" href="javascript:void(1);" onClick="CheckDropSubmitform('Temp_Name','id_sonde','AddTemp');"><?php echo($msg["MAIN"]["Add"][$Lang]); ?></a></p></span>
  <div class="clear"></div>
</div>
</td>
<td width="50%" align="left"></td></tr>
</table><br>
</div>
</form>
<script>
function change(selectBox) {
  var e = document.getElementById(selectBox);
  var selected  = e.options[e.selectedIndex].value;
  <?php
  // Position java specfic code from addOns
  $nbrAddons = 0;
  while (isset($AddOn[$nbrAddons])) {  
    include_once(PATHWEBADMIN . "Manufacturers".'/'.$AddOn[$nbrAddons].'.Addon.Temps.php');
	$addOnClass_fullName = $AddOn[$nbrAddons] . "_class";
	$addOnClass          = new $addOnClass_fullName();
	$addOnClass->javaChange();
	$nbrAddons++;
  } // END WHILE
	?>

}
</script>
<?php
  mysqli_close($DB);
} // End of Function plans