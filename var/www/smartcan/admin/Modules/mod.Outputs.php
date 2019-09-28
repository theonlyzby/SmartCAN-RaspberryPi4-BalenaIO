<?PHP
// Main Function Outputs
function Outputs() {

  // Variables Passed Globally
  global $Access_Level;
  global $DB;
  global $msg;
  global $Lang;
  
  // Includes
  include_once "./lang/admin.module.Outputs.php";

  // Action Requested via Form?  
  $action = html_postget("action");
  $selected_level = html_postget("selected_level");

  // Delete Output
  if ($action=="DeleteOutput") {
    $OutputD = html_get("OutputD");
	$sql = mysqli_real_escape_string($DB,"DELETE FROM `lumieres` " .
              " WHERE `id` = \"" . $OutputD . "\";");
	$sql = str_replace(chr(92).chr(34),"'",$sql);
	if (!$query=mysqli_query($DB,$sql)) { log_this($msg["MAIN"]["DBerror"][$Lang]."[$sql]"); }
  } // End IF
  
  // Add and Modify Outputs
  if (($action=="update") OR ($action=="AddOutput")) {
    // Graps form input
    $IdArray    = html_get("moveMe_id");
    $DescArray  = html_get("moveMe_desc");
    $XcoorArray = html_get("moveMe_xcoor");
    $YcoorArray = html_get("moveMe_ycoor");

    // Update DB
    $i = 1;
    while (isset($IdArray[$i])) { 
      $sql = mysqli_real_escape_string($DB,"UPDATE `lumieres` SET `img_x` = \"" . 
             $YcoorArray[$i] . "\", " . "`img_y` = \"" .
             $XcoorArray[$i] . "\", `description` = \"" .
             $DescArray[$i]  . "\" " .
             "WHERE `id` = \"" . $IdArray[$i] . "\";");
	  $sql = str_replace(chr(92).chr(34),"'",$sql);
      if (!$query=mysqli_query($DB,$sql)) { log_this($msg["MAIN"]["DBerror"][$Lang]."[$sql]"); }
      $i++;
    } // End While

    // Create a new Output on this level?
    if ($action=="AddOutput") {
	  $Output_Name = html_get("Output_Name");
	  $Intensity = dechex(html_get("Intensity"));
	  $Output    = html_get("Output");
	  $Delai    = html_get("Delai");
	  
	  // Find Card
	  $sql = mysqli_real_escape_string($DB,"SELECT Element.element_reference AS Sortie, Sub.Reference AS Carte " .
	         "FROM `ha_subsystem` AS Sub, `ha_element` AS Element WHERE Element.id=" . $Output .
			 " AND Element.card_id=Sub.id;");
	  $query = mysqli_query($DB,$sql);
      $row = mysqli_fetch_array($query, MYSQLI_BOTH);
      $Sortie = $row['Sortie'];
	  $Carte  = substr($row['Carte'],2,2);
	  
      $sql = mysqli_real_escape_string($DB,"INSERT INTO `lumieres` (`id`, `carte`, `sortie`, `valeur_souhaitee`, " .
			 "`delai`, `valeur`, `timer`, `timer_pid`, `localisation`, `img_x`, `img_y`, `description`) VALUES " .
			 "(NULL, \"" . $Carte . "\", \"" . $Sortie . "\", \"" . $Intensity . "\", \"" . $Delai . "\", " .
			 "\"00\", \"0\", \"0\", " .
			 "\"" . "selected_level" . "\", \"1\", \"1\", \"" . $Output_Name . "\");");
	  $sql = str_replace(chr(92).chr(34),"'",$sql);
      if (!$query=mysqli_query($DB,$sql)) { echo($msg["MAIN"]["DBerror"][$Lang]." [$sql]"); }
    } // End IF
  } // End IF


  // Start Build Page ...


  echo("<h2 class='title'>" . $msg["OUTPUTS"]["Title"][$Lang] . "</h2>");
  echo("<form name='ChangeName' id='ChangeName' action='./index.php?page=Outputs' method='get'>" .
       "<div class='post_info'>&nbsp;Niveau: " . 
       "<input type='text' name='LName' id='LName' value='" . "selected_level" . "'/>&nbsp;&nbsp;" .
       "<a href='javascript:void();' onClick='ActiveLevelChange(\"LName\")'><img src='./images/edit.png'/></a>&nbsp;&nbsp;" .
       "<a href='javascript:void();' onClick='ActiveLevelDelete()'><img src='./images/drop.png'/></a>&nbsp;&nbsp;" .
       "</div><input type='hidden' name='action' id ='action' value=''/>" .
	   "<input type='hidden' name='page' id ='page' value='Outputs'/>" .
	   "</form>" . CRLF);

  echo("	<div class='postcontent' name='plan' " .
        "style='height: 355px;" .
        " width: 550px; margin-left: 50px;'>" . CRLF);
?>

<style>
img
{
position:relative;
}
</style>

  <?PHP
  // Places Output Images on plan
  $sql = mysqli_real_escape_string($DB,"SELECT * FROM `ha_subsystem` WHERE 1;");
  $sql = str_replace(chr(92).chr(34),"'",$sql);
  //echo("SQL=$sql<br>");
  $i=1;
  $query = mysqli_query($DB,$sql);
  echo("<table width=\"100%\">" . CRLF);
  while ( $row = mysqli_fetch_array($query, MYSQLI_BOTH) ) {
    $id           = $row['id'];
    $Manufacturer = $row['Manufacturer'];
    $Type         = $row['Type'];
    $Reference    = $row['Reference'];
    $Name         = $row['Name'];
    // Display on page
	//echo("<input type='hidden' name='page' id ='page' value='Outputs'/>" . CRLF);
	echo("<tr><td width=\"40%\">" . $Manufacturer . "</td>" . CRLF);
	echo("<td width=\"10%\">" . $Type . "</td>" . CRLF);
	echo("<td width=\"10%\">" . $Reference . "</td>" . CRLF);
	echo("<td width=\"40%\">" . $Name . "</td></tr>" . CRLF);
	
	
    $i++;
  } // End While
  echo("</table>" . CRLF);

  echo("<div class='clear'></div>" . CRLF);
//  echo("<a href='javascript:void();' onClick=''>" .
//      "<img src='./images/ChangeButton.jpg' width='70px' heigth='60px' /></a>");
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
      echo("<li>&nbsp;<a class='rsswidget' href='./index.php?page=Outputs&selected_level=" .
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
  echo("<div class='post_info'><a href='javascript:void();' onClick=\"showOverlay('AddLevel','Level_Name');\" id='clickMe'><img width='14' height='14' align='absmiddle' src='../www/images/ajouter.png' />&nbsp;".$msg["MAIN"]["AddLevel"][$Lang]."</a></div>" . CRLF);
  echo("</div>" . CRLF);
?>

<div id="ChangeImg" style="visibility:hidden; z-index: 1; position: absolute; top: 200px; left: 185px; background: WhiteSmoke; width: 950px; height: 850px; opacity: .95; filter: alpha(opacity=80); -moz-opacity: .1; border-width: 2px; border-style: solid; border-color: #000000;">
<a href="javascript:void(1);" onClick="hideOverlay('ChangeImg');"><img width='14' height='14' align='absmiddle' src='../www/images/close.jpg' />&nbsp;<?php echo($msg["MAIN"]["Close"][$Lang]); ?></a>
<p align=center><h1 align=center><?php echo($msg["MAIN"]["ChangeLevelImage"][$Lang]." ".$selected_level);?></h1></p>
<br><br><br><br>
<form id="NewImg" name="NewImg" enctype="multipart/form-data" action="./index.php?page=Outputs&selected_level=<?php echo($selected_level);?>" method="post" >
<table width="100%">
<tr><td width="20%">&nbsp;</td>
<td width="30%" align="right"><?php echo($msg["MAIN"]["LevelImage"][$Lang]); ?>&nbsp;&nbsp;&nbsp;<br><br></td> 
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

 <form id="NewLevel" name="NewLevel" enctype="multipart/form-data" action="./index.php?page=Outputs&action=AddLevel" method="post" >
<table width="100%">
<tr><td width="20%">&nbsp;</td>
<td width="30%" align="right"><?php echo($msg["MAIN"]["LevelName"][$Lang]); ?>&nbsp;&nbsp;&nbsp;<br><br></td>
<td width="50%"><input name="Level_Name" id="Level_Name" type="text" size="20" value="" /></td></tr>
<tr><td width="20%">&nbsp;</td>
<td width="30%" align="right"><?php echo($msg["MAIN"]["LevelImage"][$Lang]); ?>&nbsp;&nbsp;&nbsp;<br><br></td> 
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
	 alert("Nom du niveau Vide!");
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
    alert("Utilisez des noms incluant Uniquement A-Z , a-z , 0-9, _ ou -");
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
   if (!CheckLevelName(lName)) {alert("<?php echo($msg["MAIN"]["NOK"][$Lang]); ?>"); return;}
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
  echo("<div id='text-11' class='block widget_text'><br><br><br><h2>" . $msg["OUTPUTS"]["SideTitle"][$Lang] . "</h2>" . CRLF);
  echo("<img width='258' height='16' alt='Sidebar Hr' src='./images/sidebar_hr.png' class='divider'/>" . CRLF);
  echo("</div>" . CRLF);
  echo("<div id='rss-3' class='block widget_rss'>" . CRLF);
  echo("<ul>" . CRLF);

  // List Outputs
  echo("<form name='pos' method='get' id='ChangeOutput'".
       " action='" . htmlentities($_SERVER['PHP_SELF']) ."'>" . CRLF);
  echo("<input type='hidden' name='page' value='" .
        "Outputs'/>" . CRLF);
  echo("<input type='hidden' name='selected_level' value='" .
        $selected_level . "'/>" . CRLF);
  echo("<input type='hidden' name='OutputD' value=''/>" . CRLF);
  $i = 1;
  while (isset($id[$i])) {
    echo("  <li><input type='text'" .
         " name='moveMe_desc[".$i."]'" .
         " onfocus='Outputfocus(\"$i\")' " .
         " onblur='OutputUNfocus(\"$i\")' " . 
         " value='" . $description[$i] . 
         "' />" . CRLF);
	echo("<a href='javascript:void();' onClick='OutputDelete(".$id[$i].");'><img src='./images/drop.png'/></a></li>" . CRLF);
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

&nbsp;&nbsp;<a href='javascript:void(1);' onClick='showOverlay("NewOutput","Output_Name");'><img width='14' height='14' align='absmiddle' src='../www/images/ajouter.png' />&nbsp;&nbsp;&nbsp;<?php echo($msg["OUTPUTS"]["AddOuput"][$Lang]); ?></a>
  </ul></div>


<div class="postcontent">

		<span class="readmore_b">
<a class="readmore" href="javascript:void(1);" style="color: white;" onclick="submitform('update');"><?php echo($msg["MAIN"]["update"][$Lang]); ?></a></span>
		<div class="clear"></div>
	</div>

<script type="text/javascript">

// Submit Modify or Add Outputs
function OutputDelete(id) {
  document.pos.OutputD.value = id;
  submitform("DeleteOutput");
}

function CheckSubmitform(field,action) {
  var ll = document.getElementById(field).value;
  if (ll.length>=1) {
    submitform(action);
  } else {
    alert("Description Vide!");
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


<div id="NewOutput" style="visibility:hidden; z-index: 1; position: absolute; top: 200px; left: 185px; background: WhiteSmoke; width: 950px; height: 850px; opacity: .95; filter: alpha(opacity=80); -moz-opacity: .1; border-width: 2px; border-style: solid; border-color: #000000;">
<a href="javascript:void(1);" onClick="hideOverlay('NewOutput');"><img width='14' height='14' align='absmiddle' src='../www/images/close.jpg' />&nbsp;Fermer</a>

<p align=center><h1 align=center><?php echo($msg["OUTPUTS"]["AddOuputLevel"][$Lang]." ".$selected_level);?></h1></p>
<br><br><br><br>
<table width="100%">
<tr><td width="20%">&nbsp;</td>
<td width="30%" align="right"><?php echo($msg["MAIN"]["Description"][$Lang]); ?>&nbsp;&nbsp;&nbsp;<br><br></td> 
<td width="50%"><input id="Output_Name" name="Output_Name" type="text"/></td></tr>
<tr><td width="20%">&nbsp;</td>
<td width="30%" align="right"><?php echo($msg["OUTPUTS"]["SetPoint"][$Lang]); ?>&nbsp;&nbsp;&nbsp;<br><br></td> 
<td width="50%"><select name="Intensity" id="Intensity">
<?php
$i = 1;
while ($i<=50) {
$selected = ""; if ($i==50) { $selected = " Selected"; }
echo("<option value='" . $i . "' " . $selected . ">" .($i*2) . "%</option>" . CRLF);
$i++;
} // End While
?>
</select>
</td></tr>
<tr><td width="20%">&nbsp;</td>
<td width="30%" align="right"><?php echo($msg["MAIN"]["Output"][$Lang]); ?>&nbsp;&nbsp;&nbsp;<br><br></td> 
<td width="50%"><select name="Output" id="Output">
<?php
$sql = mysqli_real_escape_string($DB,"SELECT * FROM `ha_element` WHERE " .
        "(`element_type`=\"0x11\" OR `element_type`=\"0x12\");");
$sql = str_replace(chr(92).chr(34),"'",$sql);
$query = mysqli_query($DB,$sql);
while ($row = mysqli_fetch_array($query, MYSQLI_BOTH)) {
  $id           = $row['id'];
  $element_name = $row['element_name'];
  echo("<option value='" . $id . "'>" . $element_name . "</option>" . CRLF);
} // End While
?>
</select>
</td></tr>


<tr><td width="20%">&nbsp;</td>
<td width="30%" align="right"><?php echo($msg["OUTPUTS"]["Delay"][$Lang]); ?>&nbsp;&nbsp;&nbsp;<br><br></td> 
<td width="50%"><select name="Delai" id="Delai">
<?php
$i = 0;
while ($i<=255) {
$selected = "";
if ($i==0) { 
  $selected = " Selected"; 
  $legend   = $msg["OUTPUTS"]["Relay"][$Lang];
} else {
  $legend   = ($i*10) . " ms";
} // ENDIF
echo("<option value='" . $i . "' " . $selected . ">" .($legend) . "</option>" . CRLF);
$i++;
} // End While
?>
</select>
</td></tr>


<tr><td width="20%">&nbsp;
<td width="30%"><div class="postcontent">
  <span class="readmore_b"><p>
    <a class="readmore" href="javascript:void(1);" onClick="CheckSubmitform('Output_Name','AddOutput');"><?php echo($msg["MAIN"]["Add"][$Lang]); ?></a></p></span>
  <div class="clear"></div>
</div>
</td>
<td width="50%" align="left"></td></tr>
</table><br>
</div>
</form>
<?php
  mysqli_close($DB);
} // End of Function plans