<?php
header("Content-Type: application/json");

include "../../config.php";

$baseUrl = 'http://www.thebluealliance.com/api/v3/';
?>
{
 "CurrentVersion" : [ "2017", "1", "0"],
 "Events" : <?php #Request 1: District Events
$url1 = $baseUrl.'district/'.$seasonYear.$districtKey.'/events/simple';
$ch1 = curl_init($url1);
curl_setopt($ch1, CURLOPT_HTTPHEADER, array('X-TBA-Auth-Key: '.$TBAAuthKey));
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
$districtEventsJson = curl_exec($ch1);
echo $districtEventsJson;
curl_close($ch1);
?>,
 "TeamsByEvent" : [
	 <?php #Request 2: Teams for each event
foreach (json_decode($districtEventsJson) as $event) {
	
    $url2 = $baseUrl.'event/'.$event->key.'/teams/simple';
    $ch2 = curl_init($url2);
    curl_setopt($ch2, CURLOPT_HTTPHEADER, array('X-TBA-Auth-Key: '.$TBAAuthKey));
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
	echo "{ \"EventKey\": \"".$event->key."\", \"EventData\": ".curl_exec($ch2)."},".PHP_EOL;
    curl_close($ch2);
}
?>
	{ "EventKey": "0000null", {[{
    "city": "City",
    "country": "Country",
    "key": "frc0000",
    "name": "Long Name",
    "nickname": "Nick Name",
    "state_prov": "State/Prov",
    "team_number": 0000
  }]}}]
}