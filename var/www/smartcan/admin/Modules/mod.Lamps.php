<?PHP
// Main Function Lamps
function Lamps() {

  // Variables Passed Globally
  global $Access_Level;
  global $DB;
  global $msg;
  global $Lang;
  
  // Includes
  include_once "./lang/admin.module.Lamps.php";

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

  
  // Edit Lamp
  if ($action=="EditLamp") {
    $LampD = html_postget("LampD");
	//echo("Lamp Edit, LampD=$LampD<br>");
  } // End IF
  
  // Modify Lamp (After Edit)
  if ($action=="ModifyLamp") {
    $LampE = html_postget("LampE");
	//echo("Lamp Modify, LampE=$LampE<br>");
	$Lamp_Name = html_postget("Lamp_Name");
	$Intensity = dechex(html_postget("Intensity"));
	$Output    = html_postget("Output");
	$Delai     = html_postget("Delai");
	$Icon      = html_postget("Icon_Img");
	  
	// Find Card
	$sql = "SELECT * FROM `ha_element` WHERE id=" . $Output . ";";
	//echo("sql1=$sql<br>");
	$query = mysqli_query($DB,$sql);
    $row = mysqli_fetch_array($query, MYSQLI_BOTH);
	$Manufacturer = $row['Manufacturer'];
    $Sortie       = $row['element_reference'];
	$Carte        = $row['card_id'];
	  
    $sql = mysqli_real_escape_string($DB,"UPDATE `domotique`.`lumieres` SET `Manufacturer` = '".$Manufacturer."', `carte` = '".$Carte."', `sortie` = '".$Sortie."', `delai` = '".$Delai."', ".
			"`valeur_souhaitee` = '".$Intensity."', `icon` = '".$Icon."', `description` = '".$Lamp_Name."' WHERE `lumieres`.`id` = ".$LampE.";");
	$sql = str_replace("\\'","'",$sql);
    //echo("sql2=$sql<br>");
	$query=mysqli_query($DB,$sql);
  } // End IF
  
  
  // Delete Lamp
  if ($action=="DeleteLamp") {
    $LampD = html_postget("LampD");
	$sql = mysqli_real_escape_string($DB,"DELETE FROM `lumieres` " .
              " WHERE `id` = \"" . $LampD . "\";");
	$sql = str_replace(chr(92).chr(34),"'",$sql);
	if (!$query=mysqli_query($DB,$sql)) { log_this("Erreur DB[$sql]"); }
	$sql = mysqli_real_escape_string($DB,"DELETE FROM `lumieres_status` " .
              " WHERE `id` = \"" . $LampD . "\";");
	$sql = str_replace(chr(92).chr(34),"'",$sql);
	if (!$query=mysqli_query($DB,$sql)) { log_this("Erreur DB[$sql]"); }
  } // End IF
  
  // Add and Modify Lamps
  if (($action=="update") OR ($action=="AddLamp")) {
    // Graps form input
    $IdArray    = html_postget("moveMe_id");
    $DescArray  = html_postget("moveMe_desc");
    $XcoorArray = html_postget("moveMe_xcoor");
    $YcoorArray = html_postget("moveMe_ycoor");

    // Update DB
    $i = 1;
    while (isset($IdArray[$i])) { 
	  // Moved on AddLamp??
	  $sql = "SELECT * FROM `lumieres` WHERE `id`='".$IdArray[$i]."';";
	  $query = mysqli_query($DB,$sql);
	  $row = mysqli_fetch_array($query, MYSQLI_BOTH);
	  $Ycoord = $row['img_x']."px";
	  $Xcoord = $row['img_y']."px";
	  // Lamp 8: X=-150, XArray=402px, Y=286, YArray=277px
	  // Lamp 8: X=129, XArray=129px, Y=277, YArray=277px
	  if (($Xcoord!=$XcoorArray[$i]) || ($Ycoord!=$YcoorArray[$i])) {
	    //echo("Lamp $i: X=".$Xcoord.", XArray=".$XcoorArray[$i].", Y=".$Ycoord.", YArray=".$YcoorArray[$i]."<br>");
	    if ($i%10!=0) {
          if (fmod($i,10)!=0) {
	        $web_offst  = (($i%10)-1)*39;
	      } else {
	        $web_offst  = ($i%10)*39;
	      } // END IF
	    } else {
	      $web_offst  = ($i%10)*39;
	    } // END IF
	    if ($i>10) { $web_offst = $web_offst - ((($i%10)-1)*39);}
		if ($i>20) { $web_offst = $web_offst + ((($i%20))*39);}
	  } else {
	    $web_offst = 0;
	  } // END IF

      $sql = mysqli_real_escape_string($DB,"UPDATE `lumieres` SET `img_x` = \"" . 
             ((int)$YcoorArray[$i]) . "\", " . "`img_y` = \"" .
             (((int)$XcoorArray[$i])-((int)$web_offst)) . "\", `description` = \"" .
             $DescArray[$i]  . "\" " .
             "WHERE `id` = \"" . $IdArray[$i] . "\";");
	  $sql = str_replace(chr(92).chr(34),"'",$sql);
      if (!$query=mysqli_query($DB,$sql)) { log_this("Erreur DB[$sql]"); }
      $i++;
    } // End While

    // Create a new Lamp on this level?
    if ($action=="AddLamp") {
	  $Lamp_Name = html_postget("Lamp_Name");
	  $Intensity = dechex(html_postget("Intensity"));
	  $Output    = html_postget("Output");
	  $Delai     = html_postget("Delai");
	  $Icon      = html_postget("Icon_Img");
	  
	  // Find Card
	  $sql = "SELECT * FROM `ha_element` WHERE id=" . $Output . ";";
	  //echo("sql1=$sql<br>");
	  $query = mysqli_query($DB,$sql);
      $row = mysqli_fetch_array($query, MYSQLI_BOTH);
	  $Manufacturer = $row['Manufacturer'];
      $Sortie       = $row['element_reference'];
	  $Carte        = $row['card_id'];
	  
      $sql = mysqli_real_escape_string($DB,"INSERT INTO `lumieres` (`id`, `Manufacturer`, `carte`, `sortie`, `valeur_souhaitee`, " .
			 "`delai`, `timer`, `localisation`, `img_x`, `img_y`, `description`, `icon`) VALUES " .
			 "(NULL, \"" . $Manufacturer . "\", \"" . $Carte . "\", \"" . $Sortie . "\", \"" . $Intensity . "\", \"" . $Delai . "\", " .
			 "\"0\", " .
			 "\"" . $selected_level . "\", \"1\", \"1\", \"" . $Lamp_Name . "\", \"".$Icon."\");");
	  $sql = str_replace(chr(92).chr(34),"'",$sql);
      //echo("sql2=$sql<br>");
	  $Lamp_id=0;
	  if (!$query=mysqli_query($DB,$sql)) { 
	    echo($msg["MAIN"]["DBerror"][$Lamp]." [$sql]"); 
	  } else {
	    $Lamp_id = mysqli_insert_id($DB);
	  }
	  // Also into lumieres_status Table
	  if ($Lamp_id!=0) {
	    $sql = "INSERT INTO `domotique`.`lumieres_status` (`id`, `valeur`, `timer_pid`) VALUES ('" . $Lamp_id . "', '00', '0');";
		$query=mysqli_query($DB,$sql);
	  } // END IF
    } // End IF
  } // End IF

  // Add Level
  if ($action=="AddLevel") {
    // Move File
	$Level_Name = html_postget("Level_Name");
	$File_Name = $_FILES['NewPlan']['name'];
	if (($Level_Name!="") AND ($File_Name)) {
      $Dest_Name = "../www/images/plans/plan_" . str_replace(" ","",$Level_Name) . ".png";
	  $Dest_Jpeg = "../www/images/plans/plan_" . str_replace(" ","",$Level_Name) . ".jpg";
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
      $Level = html_postget("Level"); // original Name
	  $LName = html_postget("LName"); // New Name
	  if ((rename("../www/images/plans/plan_" . $Level . ".png" , "../www/images/plans/plan_" . str_replace(" ","",$LName) . ".png")) AND
	      (rename("../www/images/plans/plan_" . $Level . ".jpg" , "../www/images/plans/plan_" . str_replace(" ","",$LName) . ".jpg"))) {
	    $sql = mysqli_real_escape_string($DB,"UPDATE `localisation` SET `lieu` = \"" . $LName .
               "\" WHERE `lieu` = \"" . $Level . "\" LIMIT 1;");
		$sql = str_replace(chr(92).chr(34),"'",$sql);
		if (!$query=mysqli_query($DB,$sql)) { log_this("Erreur DB[$sql]"); }
	    
		$sql = mysqli_real_escape_string($DB,"UPDATE `lumieres` SET `localisation` = \"" . $LName .
               "\" WHERE `localisation` = \"" . $Level . "\";");
		$sql = str_replace(chr(92).chr(34),"'",$sql);
		if (!$query=mysqli_query($DB,$sql)) { log_this($msg["MAIN"]["DBerror"][$Lang]."[$sql]"); }
		
		$selected_level = $LName;
		
	  } else {
	    log_this($msg["MAIN"]["FileCopyError"][$Lang]);
      }	  // End IF
    } // End IF

    // Modify Level Image
    if ($action=="ChangeImg") {
	  $Lvl_Name  = str_replace(" ","",html_post("Lvl_Name"));
	  $Dest_Name = "../www/images/plans/plan_" . $Lvl_Name . ".png";
	  $Dest_Jpeg = "../www/images/plans/plan_" . $Lvl_Name . ".jpg";
	  if(!copy($_FILES['ImgFile']['tmp_name'], $Dest_Name)) {
	    log_this($msg["MAIN"]["FileCopyError"][$Lang]);
	  } else {
	    // Generate the jpg file
	    if (!png2jpg($Dest_Name, $Dest_Jpeg, 100)) { log_this($msg["MAIN"]["FileCopyError"][$Lang]." .JPG"); } // End IF
      }	  // End IF
	  
    } // End IF
  
  
    // Delete Level
    if ($action=="DeleteLevel") {
      $Level = html_postget("Level"); // original Name
	  if ((@unlink("../www/images/plans/plan_" . $Level . ".png")) AND (@unlink("../www/images/plans/plan_" . str_replace(" ","",$Level) . ".jpg"))) {
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

  echo("<h2 class='title'>" . $msg["LAMPS"]["PageTitle"][$Lang] . "</h2>");
  echo("<form name='ChangeName' id='ChangeName' action='./index.php?page=Lamps' method='post'>" .
       "<div class='post_info'>&nbsp;Niveau: " . 
       "<input type='text' name='LName' id='LName' value='" . $selected_level . "'/>&nbsp;&nbsp;" .
       "<a href='javascript:void();' onClick='ActiveLevelChange(\"LName\")'><img src='./images/edit.png'/></a>&nbsp;&nbsp;" .
       "<a href='javascript:void();' onClick='ActiveLevelDelete()'><img src='./images/drop.png'/></a>&nbsp;&nbsp;" .
       "</div><input type='hidden' name='action' id ='action' value=''/>" .
	   "<input type='hidden' name='page' id ='page' value='Lamps'/>" .
	   "<input type='hidden' name='Level' id ='Level' value='" . $selected_level . "'/>" .
	   "</form>" . CRLF);
  $ImgURL = "../www/images/plans/plan_" . str_replace(" ","",$selected_level) . ".png";
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
posx = (pleft+e.clientX-xcoor)+(39*(sender.id.substring(6,7)-1));
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

function lampfocus(e,img)
{
document.getElementById("moveMe"+e).src="../www/images/outputs/"+img+"_on.png";
//alert("focus"+e);
}

function lampUNfocus(e,img)
{
document.getElementById("moveMe"+e).src="../www/images/outputs/"+img+"_off.png";
}
</script>

  <?PHP
  // Places Lamp Images on plan
  $sql = mysqli_real_escape_string($DB,"SELECT * FROM `".TABLE_LUMIERES."` WHERE `localisation`=\"" . $selected_level . "\" ORDER BY `id`;");
  $sql = str_replace(chr(92).chr(34),"'",$sql);
  //echo("SQL=$sql<br>");
  $i=1;
  $query = mysqli_query($DB,$sql);
  while ( $row = mysqli_fetch_array($query, MYSQLI_BOTH) ) {
    $id[$i]          = $row['id'];
    $description[$i] = $row['description'];
    $img_x[$i]       = $row['img_x'];
    $img_y[$i]       = $row['img_y'];
	$img_Icon[$i]    = $row['icon'];
	$web_offset[$i]=0;

    // Display on page
    echo("<img id='moveMe" . $i . "' " .
         "src='../www/images/outputs/".$img_Icon[$i]."_off.png' " .
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
      echo("<li>&nbsp;<a class='rsswidget' href='./index.php?page=Lamps&selected_level=" .
			$level[$i] . "' title='".$msg["MAIN"]["EditLevel"][$Lang]."'>" .
            $level[$i] .
            "</a> <span class='rss-date'></span></li>");
    } // End IF
    $i++;
  } // End While
  echo("</td><td width='30%' style='vertical-align:middle'><a href='javascript:void();' " .
       "onClick=\"showOverlay('ChangeImg','Lvl_Name');\">" .
       "<img src='./images/ChangeButton.jpg' width='70px' heigth='60px' " .
	   "title=\"".$msg["MAIN"]["ChangeLevelImage"][$Lang]."\"/></a></td></tr></table>");
  
  echo("</ul>" . CRLF);
  echo("<div class='post_info'><a href='javascript:void();' onClick=\"showOverlay('AddLevel','Level_Name');\" id='clickMe'><img width='14' height='14' align='absmiddle' src='../www/images/ajouter.png' />&nbsp;".$msg["MAIN"]["AddLevel"][$Lang]."</a></div>" . CRLF);
  echo("</div>" . CRLF);
?>

<div id="ChangeImg" style="visibility:hidden; z-index: 1; position: absolute; top: 200px; left: 185px; background: WhiteSmoke; width: 950px; height: 850px; opacity: .95; filter: alpha(opacity=80); -moz-opacity: .1; border-width: 2px; border-style: solid; border-color: #000000;">
<a href="javascript:void(1);" onClick="hideOverlay('ChangeImg');"><img width='14' height='14' align='absmiddle' src='../www/images/close.jpg' />&nbsp;<?php echo($msg["MAIN"]["Close"][$Lang]); ?></a>
<p align=center><h1 align=center><?php echo($msg["MAIN"]["ChangeLevelImage"][$Lang]." ".$selected_level);?></h1></p>
<br><br><br><br>
<form id="NewImg" name="NewImg" enctype="multipart/form-data" action="./index.php?page=Lamps&selected_level=<?php echo($selected_level);?>" method="post" >
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

 <form id="NewLevel" name="NewLevel" enctype="multipart/form-data" action="./index.php?page=Lamps&action=AddLevel" method="post" >
<table width="100%">
<tr><td width="20%">&nbsp;</td>
<td width="30%" align="right"><?php echo($msg["MAIN"]["LevelName"][$Lang]); ?>:&nbsp;&nbsp;&nbsp;<br><br></td>
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
   if (!CheckLevelName(lName)) {alert(<?php echo($msg["MAIN"]["NOK"][$Lang]); ?>); return;}
   // File Well Selected?
   var FlName   = document.getElementById(ImgFile).value;
   if (FlName=="") {
     alert("<?php echo($msg["MAIN"]["NoFileError"][$Lang]); ?>!");
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
     alert("<?php echo($msg["MAIN"]["UseOnlyPNG"][$Lang]); ?>!");
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
   if (confirm("<?php echo($msg["MAIN"]["RuSure"][$Lang]); ?>")) {
	 document.getElementById("action").value="DeleteLevel";
	 document.getElementById("ChangeName").submit();
   }	
 }
 
</script>
 
</div>
</div>

<?php
  echo("<div id='sidebar'>" . CRLF);
  echo("<div id='text-11' class='block widget_text'><br><br><br><h2>" . $msg["LAMPS"]["SideTile"][$Lang] . "</h2>" . CRLF);
  echo("<img width='258' height='16' alt='Sidebar Hr' src='./images/sidebar_hr.png' class='divider'/>" . CRLF);
  echo("</div>" . CRLF);
  echo("<div id='rss-3' class='block widget_rss' style='height:280pt;overflow:auto'>" . CRLF);
  echo("<ul>" . CRLF);

  // List Lamps
  echo("<form name='pos' method='post' id='ChangeLamp'".
       " action='" . htmlentities($_SERVER['PHP_SELF']) ."'>" . CRLF);
  echo("<input type='hidden' name='page' value='" .
        "Lamps'/>" . CRLF);
  echo("<input type='hidden' name='selected_level' value='" .
        $selected_level . "'/>" . CRLF);
  echo("<input type='hidden' name='LampD' id='LampD' value=''/>" . CRLF);
  $i = 1;
  while (isset($id[$i])) {
    echo("  <li><input type='text'" .
         " name='moveMe_desc[".$i."]'" .
         " onfocus='lampfocus(\"$i\",\"".$img_Icon[$i]."\")' " .
         " onblur='lampUNfocus(\"$i\",\"".$img_Icon[$i]."\")' " . 
         " value='" . $description[$i] . 
         "' size='14' />" . CRLF);
	echo("<a href='javascript:void();' onClick='LampEdit(".$id[$i].");'><img src='./images/edit.png'/></a>" . CRLF);
	echo("<a href='javascript:void();' onClick='LampDelete(".$id[$i].");'><img src='./images/drop.png'/></a></li>" . CRLF);
    echo("  <input type='hidden'" .
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

&nbsp;&nbsp;<a href='javascript:void(1);' onClick='showOverlay("NewLamp","Lamp_Name");'><img width='14' height='14' align='absmiddle' src='../www/images/ajouter.png' />&nbsp;&nbsp;&nbsp;<?php echo($msg["LAMPS"]["AddLamp"][$Lang]); ?></a>
  </ul></div>


<div class="postcontent">

		<span class="readmore_b">
<a class="readmore" href="javascript:void(1);" style="color: white;" onclick="submitform('update');"><?php echo($msg["MAIN"]["update"][$Lang]); ?></a></span>
		<div class="clear"></div>
	</div>

<script type="text/javascript">

// Submit Modify or Add Lamps
function LampDelete(id) {
  document.getElementById('LampD').value = id;
  submitform("DeleteLamp");
}

function LampEdit(id) {
  document.getElementById('LampD').value = id;
  submitform("EditLamp");
}

function CheckSubmitform(field,action) {
  var ll = document.getElementById(field).value;
  var sl = document.getElementById("Output");
  if (ll.length>=1) {
    if (sl.value>=1) {
      submitform(action);
	} else {
	  alert(<?php echo($msg["LAMPS"]["NoOutput"][$Lang]); ?>);
	  return;
	}
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


<?php

echo("function ChangeIcon(SelectID) {" . CRLF);
echo(" select        = document.getElementById(SelectID);" . CRLF);
echo(" select_Posted = document.getElementById(SelectID+\"_Img\");" . CRLF);
echo(" select_s      = select.style;" . CRLF);
echo(" switch(select.selectedIndex) {" . CRLF);
$jj=0;
foreach (glob("../www/images/outputs/*_on.png") as $img_icon[$jj]) {
  $img_icon[$jj] = substr($img_icon[$jj],strrpos($img_icon[$jj], "/")+1,-7);
  echo("  case $jj :" . CRLF);
  echo("    select_s.background = \"url('../www/images/outputs/".$img_icon[$jj]."_on.png') no-repeat\";" . CRLF);
  echo("  break;" . CRLF);
  $jj++;
} // End FOR EACH
echo("  default:" . CRLF);
echo("    select_s.background = \"none\";" . CRLF);
echo("  break;" . CRLF);
echo(" }" . CRLF);
echo(" select_Posted.value=select.value;" . CRLF);
echo(" select.value       =\"\";" . CRLF);
echo("}" . CRLF);

echo("</script>" . CRLF);
echo("  <input type=\"hidden\" name=\"action\" value=\"\" />" . CRLF);
echo("</div>" . CRLF);

$visibility="hidden; top: 200px; left: 185px;";
if ($action=="EditLamp") { 
  echo("  <input type=\"hidden\" id=\"LampD\" name=\"LampE\" value=\"".$LampD."\" />" . CRLF);
  $visibility="visible; top: 200px; left: 475px;"; 
  $sql="SELECT * FROM `lumieres` WHERE `id`='".$LampD."';";
  echo("sql=$sql<br>");
  $query=mysqli_query($DB,$sql);
  $row = mysqli_fetch_array($query, MYSQLI_BOTH);
} // END IF
echo("<div id=\"NewLamp\" style=\"visibility:".$visibility." z-index: 1; position: absolute; background: WhiteSmoke; width: 950px; height: 850px; opacity: .95; filter: alpha(opacity=80);");
echo(" -moz-opacity: .1; border-width: 2px; border-style: solid; border-color: #000000;\">" . CRLF);

echo("<a href=\"javascript:void(1);\" onClick=\"hideOverlay('NewLamp');\"><img width='14' height='14' align='absmiddle' src='../www/images/close.jpg' />&nbsp;".
		$msg["MAIN"]["Close"][$Lang]."</a>" . CRLF);
echo("<p align=center><h1 align=center>".$msg["LAMPS"]["AddLampOnLevel"][$Lang]." ".$selected_level."</h1></p>" . CRLF);
echo("<br><br><br><br>" . CRLF);
echo("<table width=\"100%\">" . CRLF);
echo("<tr><td width=\"20%\">&nbsp;</td>" . CRLF);
echo("<td width=\"30%\" align=\"right\">Description&nbsp;&nbsp;&nbsp;<br><br></td>" . CRLF);
echo("<td width=\"50%\"><input id=\"Lamp_Name\" name=\"Lamp_Name\" type=\"text\" ");
if ($action=="EditLamp") { echo("value=\"".$row['description']."\" "); } // END IF
echo("/></td></tr>" . CRLF);
echo("<tr><td width=\"20%\">&nbsp;</td>" . CRLF);
echo("<td width=\"30%\" align=\"right\">".$msg["LAMPS"]["Order"][$Lang]."&nbsp;&nbsp;&nbsp;<br><br></td> " . CRLF);
echo("<td width=\"50%\"><select name=\"Intensity\" id=\"Intensity\">" . CRLF);

$i = 1;
while ($i<=50) {
$selected = ""; 
if ($action=="EditLamp") { 
  if ($i==hexdec($row['valeur_souhaitee'])) { $selected = " Selected"; }
} else {
  if ($i==50) { $selected = " Selected"; }
} // END IF

echo("<option value='" . $i . "' " . $selected . ">" .($i*2) . "%</option>" . CRLF);
$i++;
} // End While
?>
</select>
</td></tr>
<tr><td width="20%">&nbsp;</td>
<td width="30%" align="right"><?php echo($msg["MAIN"]["Output"][$Lang]); ?>&nbsp;&nbsp;&nbsp;<br><br></td> 
<td width="50%"><select name="Output" id="Output">
<option value='0'><?php echo($msg["MAIN"]["Select"][$Lang]); ?></option>
<?php
$sql = mysqli_real_escape_string($DB,"SELECT * FROM `ha_element` WHERE " .
        "(`element_type`=\"0x11\" OR `element_type`=\"0x12\")  ORDER BY `id` ASC;");
$sql = str_replace(chr(92).chr(34),"'",$sql);
$query2 = mysqli_query($DB,$sql);
while ($row2 = mysqli_fetch_array($query2, MYSQLI_BOTH)) {
  echo("<option ");
  if (($action=="EditLamp") && ($row['Manufacturer']== $row2['Manufacturer']) && ($row['carte']== $row2['card_id']) && ($row['sortie']== $row2['element_reference'])) { echo("selected "); } // END IF
  echo("value='" . $row2['id']. "'>" . $row2['element_name']." (".$row2['Manufacturer']."/".$msg["MAIN"]["Module"][$Lang].
		" ".$row2['card_id']."/".$msg["MAIN"]["Output"][$Lang]." ".$row2['element_reference']. "</option>" . CRLF); 
} // End While
?>
</select>
</td></tr>

<tr><td width="20%">&nbsp;</td>
<td width="30%" align="right"><?php echo($msg["MAIN"]["Icon"][$Lang]); ?>&nbsp;&nbsp;&nbsp;<br><br></td> 
<td width="50%">
<input type="hidden" id="Icon_Img" name="Icon_Img" value="">
<select name="Icon" id="Icon" onchange="ChangeIcon('Icon');" style="height:45px;">
// http://openclassrooms.com/forum/sujet/image-dans-un-select-10327
<?php
$jj=0;
while (isset($img_icon[$jj])) {
  echo("<option ");
  //if ($jj==0) { echo("selected "); }
  if (($action=="EditLamp") && ($row['icon'] == $img_icon[$jj])) { echo("selected "); } // END IF
  if (($action!="EditLamp") && ($img_icon[$jj]=="lamp")) { echo("selected "); } // END IF
  echo("value=\"".$img_icon[$jj]."\" style=\"background: url(../www/images/outputs/".$img_icon[$jj]."_on.png) no-repeat; padding-left: 20px; width:35px; height:45px;\">".$img_icon[$jj]."</option>" . CRLF);
  $jj++;
} // END WHILE
?>
</select>
<span style='visibility:hidden;'><img src='../www/images/outputs/HiFi_on.png' onload="ChangeIcon('Icon');"/></span>
</td></tr>

<tr><td width="20%">&nbsp;</td>
<td width="30%" align="right"><?php echo($msg["LAMPS"]["VarSpeed"][$Lang]); ?>&nbsp;&nbsp;&nbsp;<br><br></td> 
<td width="50%"><select name="Delai" id="Delai">
<?php
$i = 0;
while ($i<=255) {
$selected = "";
if (($action=="EditLamp") && ($row['delai'] == $i)) { $selected=" selected "; } // END IF
if ($i==0) { 
  $selected = " Selected"; 
  $legend   = $msg["LAMPS"]["FlipFlop"][$Lang];
} else {
  $legend   = ($i*10) . " ms";
} // ENDIF
echo("<option value='" . $i . "' " . $selected . ">" .($legend) . "</option>" . CRLF);
$i++;
} // End While

echo("</select>" . CRLF);
echo("</td></tr>" . CRLF);
echo("<tr><td width=\"20%\">&nbsp;" . CRLF);
echo("<td width=\"30%\"><div class=\"postcontent\">" . CRLF);
echo("  <span class=\"readmore_b\"><p>" . CRLF);
if ($action=="EditLamp") {
  // Modify
  echo("    <a class=\"readmore\" href=\"javascript:void(1);\" onClick=\"CheckSubmitform('Lamp_Name','ModifyLamp');\">".$msg["MAIN"]["Save"][$Lang]."</a></p></span>" . CRLF);
} else {
  // Create
  echo("    <a class=\"readmore\" href=\"javascript:void(1);\" onClick=\"CheckSubmitform('Lamp_Name','AddLamp');\">".$msg["MAIN"]["Add"][$Lang]."</a></p></span>" . CRLF);
} // END IF

echo("  <div class=\"clear\"></div>" . CRLF);
echo("</div>" . CRLF);
echo("</td>" . CRLF);
echo("<td width=\"50%\" align=\"left\"></td></tr>" . CRLF);
echo("</table><br>" . CRLF);
echo("</div>" . CRLF);
echo("</form>" . CRLF);
echo("" . CRLF);

  mysqli_close($DB);
} // End of Function plans