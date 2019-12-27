<?php
// Script to log User Notification Token into DB

// Source: https://www.itwonders-web.com/blog/push-notification-using-firebase-demo-tutorial
// https://www.gstatic.com/firebasejs/7.2.0/


// PHP Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Includes
$base_URI = substr($_SERVER['SCRIPT_FILENAME'],0,strpos(substr($_SERVER['SCRIPT_FILENAME'],1),"/")+1);
include_once($base_URI.'/www/smartcan/www/conf/config.php');
include_once($base_URI . '/www/smartcan/class/lang.triggers.php');

// Connect DB
$DB=mysqli_connect(mysqli_HOST, mysqli_LOGIN, mysqli_PWD);
mysqli_select_db($DB,mysqli_DB);

// Determines how much Tokens are available
$sql = "SELECT COUNT(*) AS County FROM `users_notification` WHERE 1;";
$query = mysqli_query($DB,$sql);
$row = mysqli_fetch_array($query, MYSQLI_BOTH);
echo("Number of Tokens = " . $row["County"] . "<br>");

// Fetch DB to find user and Language
$sql = "SELECT * FROM `users_notification` WHERE 1;";
$query = mysqli_query($DB,$sql);
$base_curl = "curl -X POST -H \"Authorization: key=AAAAGAKq-Y4:APA91bH9gphJptTwGpiQ32cHpldseJMsRWCV6jdyAB-ESHX4Vxs3XEmABzwz7Im7QD0SBCVvQeJRxgdbmsm3KGZwRaLnA8vzBIkNz3wbFO4L55x2KTFTdO6O03UwIv1RowqKVY36dTuO\" " .
					"-H \"Content-Type: application/json\" -d '{\"data\": {\"notification\": {";
while ($row = mysqli_fetch_array($query, MYSQLI_BOTH)) {
  $Alias = $row['Alias'];
  $Lang = $row['Lang'];
  $Token = $row['Token'];
  $User_Agent = $row["User_Agent"];
  // Sends Alert Notification $msg["PWAnotification"]["Heater-Module-UNREACHABLE"][$Lang]
  $curl = $base_curl . "\"title\": \"" . $msg["PWAnotification"]["SmartCAN-ALERT"][$Lang] . "\", " .
					"\"body\": \"" . "This is a test" . "\", " .
					"\"icon\": \"/smartcan/www/images/icons/icon-192x192.png\" } }," .
					"\"to\": \"".$Token."\" }' https://fcm.googleapis.com/fcm/send";
  echo("<b>User: " . $Alias . ", User_Agent: " . $User_Agent . "</b><br>");
  echo("curl: " . $curl . "<br>" . CRLF);
  echo("<br>Exec: ");
  echo exec($curl);
  echo("<br><br>");
} // END WHILE

?>
