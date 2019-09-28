<?php
// https://api.irail.be/connections/?from=Cambron-Casteau&to=Bruxelles-Nord&date=261218&time=1700&timesel=departure&format=json&lang=en&fast=false&typeOfTransport=trains&alerts=false&results=3

// https://github.com/Jan-Bart/MMM-NMBS-Connection

// Stations: https://github.com/iRail/stations/blob/master/stations.csv


// Functions
function secondsToTime($seconds) {
	GLOBAL $msg,$Lang;
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@$seconds");
	$fMat = "";
	if ($dtF->diff($dtT)->format('%a')) { if ($dtF->diff($dtT)->format('%a')==1) { $fMat .= "%a ".$msg["trains"]["day"][$Lang]." "; } else { $fMat .= "%a ".$msg["trains"]["days"][$Lang]." "; } }
	if ($dtF->diff($dtT)->format('%h')) { if ($dtF->diff($dtT)->format('%h')==1) { $fMat .= "%h ".$msg["trains"]["hour"][$Lang]." "; } else { $fMat .= "%h ".$msg["trains"]["hours"][$Lang]." "; }}
	if ($dtF->diff($dtT)->format('%i')) { if ($dtF->diff($dtT)->format('%i')==1) { $fMat .= "%i ".$msg["trains"]["minute"][$Lang]; } else { $fMat .= "%i ".$msg["trains"]["minutes"][$Lang]; }}
    return $dtF->diff($dtT)->format($fMat);
}

// Connect to DB
$DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
mysqli_select_db($DB,mysqli_DB);
  
// Determine Variables Values
$sql = "SELECT * FROM `ha_settings` WHERE `variable`='trainShowStations';";
$retour = mysqli_query($DB,$sql);
$row = mysqli_fetch_array($retour, MYSQLI_BOTH);
$trainShowStations = $row["value"];
$sql = "SELECT * FROM `ha_settings` WHERE `variable`='trainSwitchAfterNoon';";
$retour = mysqli_query($DB,$sql);
$row = mysqli_fetch_array($retour, MYSQLI_BOTH);
$trainSwitchAfterNoon = $row["value"];

$url = "https://api.irail.be/connections/";
$sql = "SELECT * FROM `ha_settings` WHERE `variable`='trainDeparture';";
$retour = mysqli_query($DB,$sql);
$row = mysqli_fetch_array($retour, MYSQLI_BOTH);
if ((date("Hi")>"1200") && ($trainSwitchAfterNoon=="Y")) { $trainDestination = $row["value"]; } else { $trainDeparture = $row["value"]; }
$sql = "SELECT * FROM `ha_settings` WHERE `variable`='trainDestination';";
$retour = mysqli_query($DB,$sql);
$row = mysqli_fetch_array($retour, MYSQLI_BOTH);
// Return ?
if ((date("Hi")>"1200") && ($trainSwitchAfterNoon=="Y")) { $trainDeparture = $row["value"]; } else { $trainDestination = $row["value"]; }
// Switched?
if (html_get("Switch")=="Y") { $tempo = $trainDeparture ; $trainDeparture = $trainDestination; $trainDestination = $tempo; }
// Build URL
$url .= "?from=" . $trainDeparture . "&to=" . $trainDestination . "&timesel=departure&format=json&lang=" . $Lang . "&fast=false&typeOfTransport=trains&alerts=false&results=5";
// . "&date=" . date("dmy") . "&time=" . date("Hi")

$output = "";
//$output .= $url . "<br>";

// Parses if values entered
if ($trainDeparture!="" && $trainDestination!="") {
  //$output .= $trainDeparture . " " . $trainDestination;
  // Connect to SNCBNBMS API
  // Open the file to get existing content
  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_POSTFIELDS => "",
    CURLOPT_HTTPHEADER => array("cache-control: no-cache"),
  ));

  $dump = curl_exec($curl);
  $err = curl_error($curl);

  curl_close($curl);

  if ($err) {
    echo "cURL Error #:" . $err;
  }



  //$dump = '{"version":"1.1","timestamp":"1545862661","connection":[{"id":"0","departure":{"delay":"0","station":"Cambron-Casteau","stationinfo":{"locationX":"3.874809","locationY":"50.586759","id":"BE.NMBS.008886074","@id":"http:\/\/irail.be\/stations\/NMBS\/008886074","standardname":"Cambron-Casteau","name":"Cambron-Casteau"},"time":"1545840720","vehicle":"BE.NMBS.L4866","platform":"1","platforminfo":{"name":"1","normal":"1"},"canceled":"0","departureConnection":"http:\/\/irail.be\/connections\/8886074\/20181226\/L4866","direction":{"name":"Mons \/ Bergen"},"left":"1","walking":"0","occupancy":{"@id":"http:\/\/api.irail.be\/terms\/unknown","name":"unknown"}},"arrival":{"delay":"180","station":"Brussels-North","stationinfo":{"locationX":"4.360846","locationY":"50.859663","id":"BE.NMBS.008812005","@id":"http:\/\/irail.be\/stations\/NMBS\/008812005","name":"Brussels-North","standardname":"Brussel-Noord\/Bruxelles-Nord"},"time":"1545844800","vehicle":"BE.NMBS.IC3717","platform":"1","platforminfo":{"name":"1","normal":"1"},"canceled":"0","direction":{"name":"Brussels Airport-Zaventem"},"arrived":"1","walking":"0"},"duration":"4080","vias":{"number":"1","via":[{"id":"0","arrival":{"time":"1545841200","platform":"3","platforminfo":{"name":"3","normal":"1"},"isExtraStop":"0","delay":"0","canceled":"0","arrived":"1","walking":"0","direction":{"name":"Mons \/ Bergen"},"vehicle":"BE.NMBS.L4866","departureConnection":"http:\/\/irail.be\/connections\/8881166\/20181226\/L4866"},"departure":{"time":"1545841920","platform":"2","platforminfo":{"name":"2","normal":"1"},"isExtraStop":"0","delay":"60","canceled":"0","left":"1","walking":"0","direction":{"name":"Brussels Airport-Zaventem"},"vehicle":"BE.NMBS.IC3717","departureConnection":"http:\/\/irail.be\/connections\/8881166\/20181226\/IC3717","occupancy":{"@id":"http:\/\/api.irail.be\/terms\/unknown","name":"unknown"}},"timeBetween":"720","station":"Jurbise","stationinfo":{"locationX":"3.910694","locationY":"50.530496","id":"BE.NMBS.008881166","@id":"http:\/\/irail.be\/stations\/NMBS\/008881166","name":"Jurbise","standardname":"Jurbeke"},"vehicle":"BE.NMBS.L4866","direction":{"name":"Mons \/ Bergen"}}]},"occupancy":{"@id":"http:\/\/api.irail.be\/terms\/unknown","name":"unknown"}},{"id":"1","departure":{"delay":"180","station":"Cambron-Casteau","stationinfo":{"locationX":"3.874809","locationY":"50.586759","id":"BE.NMBS.008886074","@id":"http:\/\/irail.be\/stations\/NMBS\/008886074","standardname":"Cambron-Casteau","name":"Cambron-Casteau"},"time":"1545842880","vehicle":"BE.NMBS.L4888","platform":"2","platforminfo":{"name":"2","normal":"1"},"canceled":"0","departureConnection":"http:\/\/irail.be\/connections\/8886074\/20181226\/L4888","direction":{"name":"Grammont \/ Geraardsbergen"},"left":"1","walking":"0","occupancy":{"@id":"http:\/\/api.irail.be\/terms\/unknown","name":"unknown"}},"arrival":{"delay":"120","station":"Brussels-North","stationinfo":{"locationX":"4.360846","locationY":"50.859663","id":"BE.NMBS.008812005","@id":"http:\/\/irail.be\/stations\/NMBS\/008812005","name":"Brussels-North","standardname":"Brussel-Noord\/Bruxelles-Nord"},"time":"1545847200","vehicle":"BE.NMBS.IC1917","platform":"6","platforminfo":{"name":"6","normal":"1"},"canceled":"0","direction":{"name":"Brussels Airport-Zaventem"},"arrived":"1","walking":"0"},"duration":"4320","vias":{"number":"1","via":[{"id":"0","arrival":{"time":"1545843540","platform":"5","platforminfo":{"name":"5","normal":"1"},"isExtraStop":"0","delay":"300","canceled":"0","arrived":"1","walking":"0","direction":{"name":"Grammont \/ Geraardsbergen"},"vehicle":"BE.NMBS.L4888","departureConnection":"http:\/\/irail.be\/connections\/8886009\/20181226\/L4888"},"departure":{"time":"1545844080","platform":"1","platforminfo":{"name":"1","normal":"1"},"isExtraStop":"0","delay":"120","canceled":"0","left":"1","walking":"0","direction":{"name":"Brussels Airport-Zaventem"},"vehicle":"BE.NMBS.IC1917","departureConnection":"http:\/\/irail.be\/connections\/8886009\/20181226\/IC1917","occupancy":{"@id":"http:\/\/api.irail.be\/terms\/unknown","name":"unknown"}},"timeBetween":"540","station":"Ath","stationinfo":{"locationX":"3.777429","locationY":"50.626932","id":"BE.NMBS.008886009","@id":"http:\/\/irail.be\/stations\/NMBS\/008886009","standardname":"Ath","name":"Ath"},"vehicle":"BE.NMBS.L4888","direction":{"name":"Grammont \/ Geraardsbergen"}}]},"occupancy":{"@id":"http:\/\/api.irail.be\/terms\/unknown","name":"unknown"}},{"id":"2","departure":{"delay":"480","station":"Cambron-Casteau","stationinfo":{"locationX":"3.874809","locationY":"50.586759","id":"BE.NMBS.008886074","@id":"http:\/\/irail.be\/stations\/NMBS\/008886074","standardname":"Cambron-Casteau","name":"Cambron-Casteau"},"time":"1545844320","vehicle":"BE.NMBS.L4867","platform":"1","platforminfo":{"name":"1","normal":"1"},"canceled":"0","departureConnection":"http:\/\/irail.be\/connections\/8886074\/20181226\/L4867","direction":{"name":"Mons \/ Bergen"},"left":"1","walking":"0","occupancy":{"@id":"http:\/\/api.irail.be\/terms\/unknown","name":"unknown"}},"arrival":{"delay":"0","station":"Brussels-North","stationinfo":{"locationX":"4.360846","locationY":"50.859663","id":"BE.NMBS.008812005","@id":"http:\/\/irail.be\/stations\/NMBS\/008812005","name":"Brussels-North","standardname":"Brussel-Noord\/Bruxelles-Nord"},"time":"1545848280","vehicle":"BE.NMBS.IC3718","platform":"1","platforminfo":{"name":"1","normal":"1"},"canceled":"0","direction":{"name":"Brussels Airport-Zaventem"},"arrived":"1","walking":"0"},"duration":"3960","remarks":{"number":"2","remark":[{"id":"0","code":"SNCB_2090_HINT","description":"The original, planned timetable of this trip has been replaced by a new one due to a redirection. Based upon the new timetable, the connection cannot be guaranteed. Consult the overview for an alternative trip."},{"id":"1","code":"SNCB_2090_HINT","description":"The original, planned timetable of this trip has been replaced by a new one due to a redirection. Based upon the new timetable, the connection cannot be guaranteed. Consult the overview for an alternative trip."}]},"vias":{"number":"1","via":[{"id":"0","arrival":{"time":"1545844800","platform":"3","platforminfo":{"name":"3","normal":"1"},"isExtraStop":"0","delay":"480","canceled":"0","arrived":"1","walking":"0","direction":{"name":"Mons \/ Bergen"},"vehicle":"BE.NMBS.L4867","departureConnection":"http:\/\/irail.be\/connections\/8881166\/20181226\/L4867"},"departure":{"time":"1545845400","platform":"2","platforminfo":{"name":"2","normal":"1"},"isExtraStop":"0","delay":"0","canceled":"0","left":"1","walking":"0","direction":{"name":"Brussels Airport-Zaventem"},"vehicle":"BE.NMBS.IC3718","departureConnection":"http:\/\/irail.be\/connections\/8881166\/20181226\/IC3718","occupancy":{"@id":"http:\/\/api.irail.be\/terms\/unknown","name":"unknown"}},"timeBetween":"600","station":"Jurbise","stationinfo":{"locationX":"3.910694","locationY":"50.530496","id":"BE.NMBS.008881166","@id":"http:\/\/irail.be\/stations\/NMBS\/008881166","name":"Jurbise","standardname":"Jurbeke"},"vehicle":"BE.NMBS.L4867","direction":{"name":"Mons \/ Bergen"}}]},"occupancy":{"@id":"http:\/\/api.irail.be\/terms\/unknown","name":"unknown"}},{"id":"3","departure":{"delay":"480","station":"Cambron-Casteau","stationinfo":{"locationX":"3.874809","locationY":"50.586759","id":"BE.NMBS.008886074","@id":"http:\/\/irail.be\/stations\/NMBS\/008886074","standardname":"Cambron-Casteau","name":"Cambron-Casteau"},"time":"1545844320","vehicle":"BE.NMBS.L4867","platform":"1","platforminfo":{"name":"1","normal":"1"},"canceled":"0","departureConnection":"http:\/\/irail.be\/connections\/8886074\/20181226\/L4867","direction":{"name":"Mons \/ Bergen"},"left":"1","walking":"0","occupancy":{"@id":"http:\/\/api.irail.be\/terms\/unknown","name":"unknown"}},"arrival":{"delay":"0","station":"Brussels-North","stationinfo":{"locationX":"4.360846","locationY":"50.859663","id":"BE.NMBS.008812005","@id":"http:\/\/irail.be\/stations\/NMBS\/008812005","name":"Brussels-North","standardname":"Brussel-Noord\/Bruxelles-Nord"},"time":"1545850020","vehicle":"BE.NMBS.IC1718","platform":"6","platforminfo":{"name":"6","normal":"1"},"canceled":"0","direction":{"name":"Li\u00e8ge-Guillemins \/ Luik-Guillemins"},"arrived":"1","walking":"0"},"duration":"5700","vias":{"number":"1","via":[{"id":"0","arrival":{"time":"1545844800","platform":"3","platforminfo":{"name":"3","normal":"1"},"isExtraStop":"0","delay":"480","canceled":"0","arrived":"1","walking":"0","direction":{"name":"Mons \/ Bergen"},"vehicle":"BE.NMBS.L4867","departureConnection":"http:\/\/irail.be\/connections\/8881166\/20181226\/L4867"},"departure":{"time":"1545847080","platform":"2","platforminfo":{"name":"2","normal":"1"},"isExtraStop":"0","delay":"0","canceled":"0","left":"1","walking":"0","direction":{"name":"Li\u00e8ge-Guillemins \/ Luik-Guillemins"},"vehicle":"BE.NMBS.IC1718","departureConnection":"http:\/\/irail.be\/connections\/8881166\/20181226\/IC1718","occupancy":{"@id":"http:\/\/api.irail.be\/terms\/unknown","name":"unknown"}},"timeBetween":"2280","station":"Jurbise","stationinfo":{"locationX":"3.910694","locationY":"50.530496","id":"BE.NMBS.008881166","@id":"http:\/\/irail.be\/stations\/NMBS\/008881166","name":"Jurbise","standardname":"Jurbeke"},"vehicle":"BE.NMBS.L4867","direction":{"name":"Mons \/ Bergen"}}]},"occupancy":{"@id":"http:\/\/api.irail.be\/terms\/unknown","name":"unknown"}},{"id":"4","departure":{"delay":"0","station":"Cambron-Casteau","stationinfo":{"locationX":"3.874809","locationY":"50.586759","id":"BE.NMBS.008886074","@id":"http:\/\/irail.be\/stations\/NMBS\/008886074","standardname":"Cambron-Casteau","name":"Cambron-Casteau"},"time":"1545846480","vehicle":"BE.NMBS.L4889","platform":"2","platforminfo":{"name":"2","normal":"1"},"canceled":"0","departureConnection":"http:\/\/irail.be\/connections\/8886074\/20181226\/L4889","direction":{"name":"Grammont \/ Geraardsbergen"},"left":"1","walking":"0","occupancy":{"@id":"http:\/\/api.irail.be\/terms\/unknown","name":"unknown"}},"arrival":{"delay":"0","station":"Brussels-North","stationinfo":{"locationX":"4.360846","locationY":"50.859663","id":"BE.NMBS.008812005","@id":"http:\/\/irail.be\/stations\/NMBS\/008812005","name":"Brussels-North","standardname":"Brussel-Noord\/Bruxelles-Nord"},"time":"1545850800","vehicle":"BE.NMBS.IC1918","platform":"6","platforminfo":{"name":"6","normal":"1"},"canceled":"0","direction":{"name":"Brussels Airport-Zaventem"},"arrived":"1","walking":"0"},"duration":"4320","vias":{"number":"1","via":[{"id":"0","arrival":{"time":"1545847140","platform":"5","platforminfo":{"name":"5","normal":"1"},"isExtraStop":"0","delay":"60","canceled":"0","arrived":"1","walking":"0","direction":{"name":"Grammont \/ Geraardsbergen"},"vehicle":"BE.NMBS.L4889","departureConnection":"http:\/\/irail.be\/connections\/8886009\/20181226\/L4889"},"departure":{"time":"1545847680","platform":"1","platforminfo":{"name":"1","normal":"1"},"isExtraStop":"0","delay":"0","canceled":"0","left":"1","walking":"0","direction":{"name":"Brussels Airport-Zaventem"},"vehicle":"BE.NMBS.IC1918","departureConnection":"http:\/\/irail.be\/connections\/8886009\/20181226\/IC1918","occupancy":{"@id":"http:\/\/api.irail.be\/terms\/unknown","name":"unknown"}},"timeBetween":"540","station":"Ath","stationinfo":{"locationX":"3.777429","locationY":"50.626932","id":"BE.NMBS.008886009","@id":"http:\/\/irail.be\/stations\/NMBS\/008886009","standardname":"Ath","name":"Ath"},"vehicle":"BE.NMBS.L4889","direction":{"name":"Grammont \/ Geraardsbergen"}}]},"occupancy":{"@id":"http:\/\/api.irail.be\/terms\/unknown","name":"unknown"}},{"id":"5","departure":{"delay":"0","station":"Cambron-Casteau","stationinfo":{"locationX":"3.874809","locationY":"50.586759","id":"BE.NMBS.008886074","@id":"http:\/\/irail.be\/stations\/NMBS\/008886074","standardname":"Cambron-Casteau","name":"Cambron-Casteau"},"time":"1545847920","vehicle":"BE.NMBS.L4868","platform":"1","platforminfo":{"name":"1","normal":"1"},"canceled":"0","departureConnection":"http:\/\/irail.be\/connections\/8886074\/20181226\/L4868","direction":{"name":"Mons \/ Bergen"},"left":"1","walking":"0","occupancy":{"@id":"http:\/\/api.irail.be\/terms\/unknown","name":"unknown"}},"arrival":{"delay":"60","station":"Brussels-North","stationinfo":{"locationX":"4.360846","locationY":"50.859663","id":"BE.NMBS.008812005","@id":"http:\/\/irail.be\/stations\/NMBS\/008812005","name":"Brussels-North","standardname":"Brussel-Noord\/Bruxelles-Nord"},"time":"1545851880","vehicle":"BE.NMBS.IC3719","platform":"1","platforminfo":{"name":"1","normal":"1"},"canceled":"0","direction":{"name":"Brussels Airport-Zaventem"},"arrived":"1","walking":"0"},"duration":"3960","vias":{"number":"1","via":[{"id":"0","arrival":{"time":"1545848400","platform":"3","platforminfo":{"name":"3","normal":"1"},"isExtraStop":"0","delay":"0","canceled":"0","arrived":"1","walking":"0","direction":{"name":"Mons \/ Bergen"},"vehicle":"BE.NMBS.L4868","departureConnection":"http:\/\/irail.be\/connections\/8881166\/20181226\/L4868"},"departure":{"time":"1545848820","platform":"2","platforminfo":{"name":"2","normal":"1"},"isExtraStop":"0","delay":"60","canceled":"0","left":"1","walking":"0","direction":{"name":"Brussels Airport-Zaventem"},"vehicle":"BE.NMBS.IC3719","departureConnection":"http:\/\/irail.be\/connections\/8881166\/20181226\/IC3719","occupancy":{"@id":"http:\/\/api.irail.be\/terms\/unknown","name":"unknown"}},"timeBetween":"420","station":"Jurbise","stationinfo":{"locationX":"3.910694","locationY":"50.530496","id":"BE.NMBS.008881166","@id":"http:\/\/irail.be\/stations\/NMBS\/008881166","name":"Jurbise","standardname":"Jurbeke"},"vehicle":"BE.NMBS.L4868","direction":{"name":"Mons \/ Bergen"}}]},"occupancy":{"@id":"http:\/\/api.irail.be\/terms\/unknown","name":"unknown"}},{"id":"6","departure":{"delay":"120","station":"Cambron-Casteau","stationinfo":{"locationX":"3.874809","locationY":"50.586759","id":"BE.NMBS.008886074","@id":"http:\/\/irail.be\/stations\/NMBS\/008886074","standardname":"Cambron-Casteau","name":"Cambron-Casteau"},"time":"1545850080","vehicle":"BE.NMBS.L4890","platform":"2","platforminfo":{"name":"2","normal":"1"},"canceled":"0","departureConnection":"http:\/\/irail.be\/connections\/8886074\/20181226\/L4890","direction":{"name":"Grammont \/ Geraardsbergen"},"left":"1","walking":"0","occupancy":{"@id":"http:\/\/api.irail.be\/terms\/unknown","name":"unknown"}},"arrival":{"delay":"0","station":"Brussels-North","stationinfo":{"locationX":"4.360846","locationY":"50.859663","id":"BE.NMBS.008812005","@id":"http:\/\/irail.be\/stations\/NMBS\/008812005","name":"Brussels-North","standardname":"Brussel-Noord\/Bruxelles-Nord"},"time":"1545854400","vehicle":"BE.NMBS.IC1919","platform":"6","platforminfo":{"name":"6","normal":"1"},"canceled":"0","direction":{"name":"Brussels Airport-Zaventem"},"arrived":"1","walking":"0"},"duration":"4320","vias":{"number":"1","via":[{"id":"0","arrival":{"time":"1545850740","platform":"4","platforminfo":{"name":"4","normal":"1"},"isExtraStop":"0","delay":"180","canceled":"0","arrived":"1","walking":"0","direction":{"name":"Grammont \/ Geraardsbergen"},"vehicle":"BE.NMBS.L4890","departureConnection":"http:\/\/irail.be\/connections\/8886009\/20181226\/L4890"},"departure":{"time":"1545851280","platform":"1","platforminfo":{"name":"1","normal":"1"},"isExtraStop":"0","delay":"240","canceled":"0","left":"1","walking":"0","direction":{"name":"Brussels Airport-Zaventem"},"vehicle":"BE.NMBS.IC1919","departureConnection":"http:\/\/irail.be\/connections\/8886009\/20181226\/IC1919","occupancy":{"@id":"http:\/\/api.irail.be\/terms\/unknown","name":"unknown"}},"timeBetween":"540","station":"Ath","stationinfo":{"locationX":"3.777429","locationY":"50.626932","id":"BE.NMBS.008886009","@id":"http:\/\/irail.be\/stations\/NMBS\/008886009","standardname":"Ath","name":"Ath"},"vehicle":"BE.NMBS.L4890","direction":{"name":"Grammont \/ Geraardsbergen"}}]},"occupancy":{"@id":"http:\/\/api.irail.be\/terms\/unknown","name":"unknown"}}]}';
  
  //$output .= $dump;
  
  if ($dump!="") {
    // Parses JSON
    $trains = json_decode($dump);

    $output .= "<table width='600pt'><tr><td width='25%'><div class='w3-black w3-xxlarge w3-text-grey'>".$msg["trains"]["Departure"][$Lang]."</div><div class='w3-black tiny'>";
	if ($trainShowStations=="Y") { $output .= $trainDeparture; }
	$output .= "</div></td>" . "<td  width='20%' class='w3-container w3-black w3-center'><a href='?page=trains";
	if (html_get("theme")!="") { $output .= "&layout=" . html_get("theme"); }
	if (html_get("Switch")=="") { $output .= "&Switch=Y"; }
	$output .= "'><img src='./images/Switch.png' width='32' height='32' style='vertical-align:middle'/></a></td>" .
			"<td width='60%'><div class='w3-black w3-xxlarge w3-text-grey'>".$msg["trains"]["Arrival"][$Lang]."</div><div class='w3-black tiny'>";
	if ($trainShowStations=="Y") { $output .= $trainDestination; }
	$output .="</div></td></tr>";
    $trainImg = "<img src='./images/iconTrain.JPG' height='40' wight='40'/>";
    foreach ($trains->connection as $value) {
	  $start = $value->departure;
	
	  $departure  = date('H:i',$start->time);
	  $depDelayClass = "w3-black tiny w3-text-green"; $depDelay = $start->delay/60; if ($depDelay>0) { $depDelayClass = "w3-black tiny w3-text-red"; }
	  $platform = $start->platform;
	  $depClass = "w3-black w3-xxlarge"; $canceled = $start->canceled; if ($canceled>0) { $depClass = "w3-black w3-xxlarge w3-text-deep-orange' style='text-decoration:line-through;"; }
	
	  $end = $value->arrival;
	
	  $arriDelayClass = "w3-black tiny w3-text-green"; $arriDelay = $end->delay/60; if ($arriDelay>0) { $arriDelayClass = "w3-black tiny w3-text-red"; }
	  $arrival = date('H:i',$end->time);
	
	  $duration = secondsToTime($value->duration);

	  $change = ""; if (isset($value->vias->number)) { $change = ", " . $value->vias->number . " ".$msg["trains"]["Changes"][$Lang]; }
	
	  $output .= "<tr><td><div style='display: inline'><span class='$depClass'>$departure</span></div><div  style='display: inline' class='$depDelayClass'>&nbsp; +$depDelay</div></td>";
	  $output .= "<td class='w3-black w3-text-grey'>&boxh;&boxh;&boxh;&boxh;&boxh;".$trainImg."&boxh;&boxh;&boxh;&boxh;&boxh;</td>";
	  $output .= "<td><div style='display: inline' ><span class='$depClass'>$arrival</span></div><div  style='display: inline' class='$arriDelayClass'>&nbsp; +$arriDelay</div></td></tr>";
	  $output .= "<tr><td class='w3-black tiny w3-text-grey'>".$msg["trains"]["Platform"][$Lang]." " .$platform."</td><td>&nbsp;</td><td class='w3-black tiny w3-text-grey'>$duration$change</td></tr>";
	
	  //$output .= "OUT " . $value->id . "<br>";
	  //$output .= "Departure: $departure , Delay: $depDelay , Platform: $platform , Canceled: $canceled , Arrival: $arrival, Arrival Delay: $arriDelay , Change: $change<br>";
	
	  $output .= "</tr>";
	  $trainImg = "&boxh;&boxh;&boxh;";
    }

    $output .= "</table>";
  } // ENDIF
} // ENDIF

// Display Table with Trains
$_XTemplate->assign('TRAINSTABLE', $output);
  
// Close DB
mysqli_close($DB);

?>
