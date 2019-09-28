<?php
// PHP Error Reporting
//error_reporting(E_ALL);
//ini_set('display_errors', '1');


// Load Dependencies
if ($_SERVER['DOCUMENT_ROOT']!="") {
  include_once($_SERVER['DOCUMENT_ROOT'].'/smartcan/www/conf/config.php');
} else {
  $base_URI = substr($_SERVER['SCRIPT_FILENAME'],0,strpos(substr($_SERVER['SCRIPT_FILENAME'],1),"/")+1);
  include_once($base_URI.'/www/smartcan/www/conf/config.php');
}

// Funstions
function distance($lat1, $lon1, $lat2, $lon2) {
  $theta = $lon1 - $lon2;
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
  $dist = acos($dist);
  $dist = rad2deg($dist);
  $miles = $dist * 60 * 1.1515;
  return ($miles * 1.609344);
} // END Function

function detect_plane($server, $debug=0) {
  // Connects to Local DVB-S receiver (grabs Dump1090 json)
  $file = "http://".$server."/data.json";
  //echo("Dump1090 URI: " . $file . "END");
  // Open the file to get existing content
  $dump = @file_get_contents($file);

  // Could open link?
  if ($dump=="") {
    // NO!
    $outMessage = "ERROR: Couldn't connect to dump1090 server!"; 
  } else {
    // JSON decode
    $planes = json_decode($dump);
    $closest_dist   = 10;
    $closest_flight = "";
    $closest_alt    = 0;

    foreach ($planes as $value) {
      $flight     = $value->flight;
      $valid      = $value->validposition;
	  $validTrack = $value->validtrack;
      $long       = floatval($value->lon);
      $lat        = floatval($value->lat);
      $alt        = round(floatval($value->altitude)/3.2808);
      $vert       = round(($value->vert_rate)*0.0051,1);
	  $speed      = round(($value->speed)*1,852,1);
      $dist       = round(distance($lat,$long,50.8503804,4.4185094),1);
  
      if ((($debug!=0) && ($flight!="")) && (($valid!=0) || (($valid==0) && ($alt<=3000) && ($validTrack==0)))) {
		if ($valid==0) echo("<a target='_blank' style='text-decoration: none' href='https://fr.flightaware.com/photos/aircraft/".$flight."'><font color=blue><b>"); 
	    echo("Check Flight: ".$flight.", alt: ".$alt."m");
		if ($valid!=0) { echo(", Distance: ".$dist."km, Vert Rate: " . $vert ." m/s, Speed: " . $speed . " km/h"); } else { echo("</b></font></a>"); }
		echo("<br>");
	  } // END IF
  
      if (($valid!=0) && ($flight!="") && ($alt>=340) && ($alt<=5000) && ($dist<=$closest_dist)) {
	    if ($debug!=0) echo("<font color='red'><b>CLOSEST => Flight: ".$flight.", alt: ".$alt."m, Distance: ".$dist."km, Vert Rate: " . $vert ." m/s, Speed: " . $speed . " km/h</b></font><br>");
        $closest_flight = $flight;
	    $closest_dist   = $dist;
	    $closest_alt    = $alt;
      } // END IF
  
      /*
      if (($valid!=0) && ($flight!="")) {
        echo("Flight: ".$flight.", Valid? ".$valid.", alt: ".$alt.", Distance: ".$dist."<br>");
      }
      */

    } // END FOREACH

    if ($closest_flight!="") {
      //echo("<br>Closest Flight = " . $closest_flight . " at " . $closest_dist . "km (alt=" . $closest_alt ." m)<br>");
      // Parsing FlightAware info
      $url='https://flightaware.com/live/flight/'.$closest_flight; //'./test-plane.json'; //'https://flightaware.com/live/flight/IBK5JB'; //AUA35D';
      $original=file_get_contents($url);

      $pattern='/trackpollBootstrap.*?<\/script>/';
      $a=preg_match_all($pattern,$original,$matches);
      if (!$a) die("");
      $flight_json = substr($matches[0][0],21,strrpos($matches[0][0],";")-21);

      //echo("START" . $flight_json . "END");
      $plane_info = json_decode($flight_json,true);

      //echo("<br>Extract:" . $plane_info['version'] . "EOL<br>");
      //print_r($plane_info['flights']);

      //echo("<br>END<br>");

      // Last OK code:
      foreach($plane_info['flights'] as $data) {
        $planeType=""; if (isset($data['aircraft']['friendlyType'])) { $planeType    = $data['aircraft']['friendlyType']; if (strpos($planeType," (")!=0) $planeType = substr($planeType,0,strpos($planeType," ("));}
        $planeAirline=$closest_flight; if (isset($data['airline'])) { if ($data['airline']['shortName']!="") { $planeAirline = $data['airline']['shortName']; } else { $planeAirline = $data['airline']['fullName']; }}
        if (isset($data['activityLog']['flights']['0'])) {
          $planeFrom = $data['activityLog']['flights']['0']['origin']['friendlyLocation'];
          $planeTo   = $data['activityLog']['flights']['0']['destination']['friendlyLocation'];
        } else { 
		  if (isset($data['origin'])) {
		    $planeFrom = $data['origin']['friendlyLocation'];
            $planeTo   = $data['destination']['friendlyLocation'];
		  } else {
		    $planeTo = ""; $planeFrom = "";
		  }
		}
        $outMessage = "";
        if ($planeType!="") $outMessage .= $planeType . " of ";
        $outMessage .= $planeAirline;
        if (($planeFrom!="Brussels, Belgium") && ($planeFrom!="")) $outMessage .= " from " . $planeFrom;
        if (($planeTo  !="Brussels, Belgium") && ($planeTo!=""))   $outMessage .= " to " . $planeTo;
        $outMessage .= ", at " . (round($closest_alt/10)*10) . " meters altitude, " . $closest_dist . " km distance.";
        //echo($outMessage);
        //print_r($data['activityLog']['flights']['0']['origin']['friendlyLocation']);
        //print_r($data->activityLog->flights->origin->friendlyLocation);
      } // END ForEach

    } else {
      $outMessage = "No plane close to home";
    } // END IF
  } // END IF
  return $outMessage;
} // END Function

//echo(detect_plane());
?>
