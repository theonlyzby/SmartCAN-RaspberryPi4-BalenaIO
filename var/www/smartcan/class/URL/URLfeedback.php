<?php
// PHP Error Reporting
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
$base_URI = substr($_SERVER['SCRIPT_FILENAME'],0,strpos(substr($_SERVER['SCRIPT_FILENAME'],1),"/")+1);

// Includes
include_once($base_URI.'/www/smartcan/www/conf/config.php');
include_once($base_URI.'/www/smartcan/class/class.triggers.php5');

// Initiate Trigger (PUSH + DB Update)
$trigger = new trigger();

// Connects to DB
if (!$DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD)) { $this->debug->envoyer(1, "URL Class", "!!! ERREUR Connection DB!!!"); }
if (!mysqli_select_db($DB,mysqli_DB)) { $this->debug->envoyer(1, "URL class", "!!! ERREUR Selection DB!!!"); }

// Config => Parse DBs
$sql = "SELECT * FROM `ha-URLmod-vars`;";
$query=mysqli_query($DB,$sql);
while ($row = mysqli_fetch_array($query, MYSQLI_BOTH)) {
  if ($row['variable']=="FbVar0")     { $FbVar[0]   = $row['value'];}
  if ($row['variable']=="FbVal0")     { $FbVal[0]   = $row['value'];}
  if ($row['variable']=="FbVar1")     { $FbVar[1]   = $row['value'];}
  if ($row['variable']=="FbVal1")     { $FbVal[1]   = $row['value'];}
  if ($row['variable']=="FbIntVar")   { $FbIntVar   = $row['value'];}
  if ($row['variable']=="FbIntValue") { $FbIntValue = $row['value'];}
  if ($row['variable']=="FbSource")   { $FbSource   = $row['value'];}
} // END WHILE

// Parse GET Request Variables
//echo("Query=".$_SERVER['QUERY_STRING']."<br>");
$query = explode("&", $_SERVER['QUERY_STRING']);
$i=0; $var0="";$var1="";$value="";
while (isset($query[$i])) {
  //echo("<br>$i:".$query[$i]."<br>");
  $query_part = explode("=",$query[$i]);
  // Check if configured => Umplement
  if (($FbVar[0]!="") && ($FbVar[0]==$query_part[0])) {  
	if (substr_count($FbVal[0],"*#--ONE--#*"))       { $var0=str_replace("*#--ONE--#*",$query_part[1],$FbVal[0]);/*echo("Val0=".str_replace("*#--ONE--#*",$query_part[1],$FbVal[0])."<br>");*/ } 
	if (substr_count($FbVal[0],"*#--TWO--#*"))       { $var1=str_replace("*#--TWO--#*",$query_part[1],$FbVal[0]);/*echo("Val1=".str_replace("*#--TWO--#*",$query_part[1],$FbVal[0])."<br>");*/ }
	if (substr_count($FbVal[0],"*#--INTENSITY--#*")) { $value=str_replace("*#--INTENSITY--#*",$query_part[1],$FbVal[0]);
			/*echo("Value=".str_replace("*#--INTENSITY--#*",$query_part[1],$FbVal[0])."<br>");*/ } 
  } // END IF
  if (($FbVar[1]!="") && ($FbVar[1]==$query_part[0])) {  
	if (substr_count($FbVal[1],"*#--ONE--#*"))       { $var0=str_replace("*#--ONE--#*",$query_part[1],$FbVal[1]);/*echo("Val0=".str_replace("*#--ONE--#*",$query_part[1],$FbVal[1])."<br>");*/ } 
	if (substr_count($FbVal[1],"*#--TWO--#*"))       { $var1=str_replace("*#--TWO--#*",$query_part[1],$FbVal[1]);/*echo("Val1=".str_replace("*#--TWO--#*",$query_part[1],$FbVal[1])."<br>");*/ }
	if (substr_count($FbVal[1],"*#--INTENSITY--#*")) { $value=str_replace("*#--INTENSITY--#*",$query_part[1],$FbVal[1]);
			/*echo("Value=".str_replace("*#--INTENSITY--#*",$query_part[1],$FbVal[1])."<br>");*/ } 
  } // END IF
  if (($FbIntVar!="") && ($FbIntVar==$query_part[0])) {
	if (substr_count($FbIntValue,"*#--ONE--#*"))       { $var0=str_replace("*#--ONE--#*",$query_part[1],$FbIntValue);/*echo("Val0=".str_replace("*#--ONE--#*",$query_part[1],$FbIntValue)."<br>");*/ } 
	if (substr_count($FbIntValue,"*#--TWO--#*"))       { $var1=str_replace("*#--TWO--#*",$query_part[1],$FbIntValue);/*echo("Val1=".str_replace("*#--TWO--#*",$query_part[1],$FbIntValue)."<br>");*/ }
	if (substr_count($FbIntValue,"*#--INTENSITY--#*")) { $value=str_replace("*#--INTENSITY--#*",$query_part[1],$FbIntValue);
			/*echo("Value=".str_replace("*#--INTENSITY--#*",$query_part[1],$FbIntValue)."<br>");*/ } 
  } // END IF
  $i++;
} // END WHILE

// Remote address check?
if ($FbSource!="") {
  $start=strpos($FbSource,"*#");
  $fromEnd=strlen($FbSource)-strpos($FbSource,"#*")-2;
  $Nb_Chars=strlen($_SERVER["REMOTE_ADDR"])-$start;
  //echo("<br>Remote=".$_SERVER["REMOTE_ADDR"]." - Requested:$FbSource<br>");
  //echo("Pos Var=".$start.", End=".$fromEnd."<br>");
  
  if ($fromEnd==0) {
    $var = substr($_SERVER["REMOTE_ADDR"],$start);
  } else {
    $var = substr($_SERVER["REMOTE_ADDR"],$start,-$fromEnd);
  } // END IF
  //echo("Variable=".$var."<br>");
  
  if (substr_count($FbSource,"*#--ONE--#*"))       { $var0=str_replace("*#--ONE--#*",$var,$FbSource); /*echo("Val0=".str_replace("*#--ONE--#*",$var,$FbSource)."<br>");*/ } 
  if (substr_count($FbSource,"*#--TWO--#*"))       { $var1=str_replace("*#--TWO--#*",$var,$FbSource); /*echo("Val1=".str_replace("*#--TWO--#*",$var,$FbSource)."<br>");*/ }
} else {
  // NO Remote adress check=> OK
  //echo("NO Address check Request=> OK!<br>");
} // END IF
mysqli_close($DB);

echo("OK<br>");
$trigger->OUTtrigger("URL", $var0, $var1, dechex(($value/2)));

/*





*/

?>
