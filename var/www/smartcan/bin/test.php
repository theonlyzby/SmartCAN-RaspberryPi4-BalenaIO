<?php
// PHP Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Dump all available environment variables
var_dump(getenv());
$val = getenv("GAIN");
echo("<br><br><b>Value =>".$val."<=</b><br>");

//$exec = '{"multicast_id":8533971268695483474,"success":1,"failure":0,"canonical_ids":0,"results":[{"message_id":"0:1577187943627671%0f493ae6f9fd7ecd"}]}';
$exec = '{"multicast_id":3294881676520689455,"success":1,"failure":0,"canonical_ids":0,"results":[{"message_id":"https:\/\/updates.push.services.mozilla.com\/m\/gAAAAABeAfpn_ipJJZsTyPX_yg7romaEPPY4VS_Gg_DqGil5AOFLSNArH8oA3gJt5F2uoz9rGVWjc3J49d3tThHKLHflNJd3rPTVI3Mb1re8UF0H6kmHfmF3IVcNoKSJZFWpxPQXMgH-t38_xSmJ6nbz1gdRxBFUCtXsIBD0rXf-sr1DxCMBzMV-iPrxTFMXrMGrFuZxgm8W"}]}';

$dec = json_decode($exec);
echo("<br>");
//var_dump($dec->{"results"}[0]->{"message_id"});
//echo("<br>");
echo("result=".$dec->{"results"}[0]->{"message_id"}."<br>");
if (is_numeric(substr($dec->{"results"}[0]->{"message_id"},0,1))) {
  echo("OK");
} else {
  echo("NOK");
}
?>