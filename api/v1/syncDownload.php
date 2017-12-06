<?php
header("Content-Type: application/json");

include "../../config.php";

$baseUrl = 'http://www.thebluealliance.com/api/v3/';

$resultJson = "{
 \"CurrentVersion\" : [ \"2017\", \"1\", \"0\"],
 \"Events\" :";

 #Request 1: District Events
$url1 = $baseUrl.'district/'.$seasonYear.$districtKey.'/events/simple';
$ch1 = curl_init($url1);
curl_setopt($ch1, CURLOPT_HTTPHEADER, array('X-TBA-Auth-Key: '.$TBAAuthKey));
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
$districtEventsJson = substr(curl_exec($ch1),0,-1);

$worldCmpEventKeys = array("carv","gal","hop","new","roe","tur","tes","dar","dal","cur","cars","arc");

foreach($worldCmpEventKeys as $cmpKey) {
	$url1Cmp = $baseUrl.'event/'.$seasonYear.$cmpKey.'/simple';
	$ch1Cmp = curl_init($url1Cmp);
	curl_setopt($ch1Cmp, CURLOPT_HTTPHEADER, array('X-TBA-Auth-Key: '.$TBAAuthKey));
	curl_setopt($ch1Cmp, CURLOPT_RETURNTRANSFER, true);
	$districtEventsJson .= ','.PHP_EOL . curl_exec($ch1Cmp);
	curl_close($ch1Cmp);
}

$resultJson .= $districtEventsJson."]
\"TeamsByEvent\" : [";
curl_close($ch1);

#Request 2: Teams for each event
$teamJson = "";
foreach (json_decode($districtEventsJson) as $event) {
	
    $url2 = $baseUrl.'event/'.$event->key.'/teams/simple';
    $ch2 = curl_init($url2);
    curl_setopt($ch2, CURLOPT_HTTPHEADER, array('X-TBA-Auth-Key: '.$TBAAuthKey));
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
	$teamJson =. "{ \"EventKey\": \"".$event->key."\", \"TeamList\": ".curl_exec($ch2)."},".PHP_EOL;
    curl_close($ch2);
}

$resultJson .= $teamJson."{ \"EventKey\": \"0000null\", \"TeamList\" : [{
    \"city\": \"City\",
    \"country\": \"Country\",
    \"key\": \"frc0000\",
    \"name\": \"Long Name\",
    \"nickname\": \"Nick Name\",
    \"state_prov\": \"State/Prov\",
    \"team_number\": 0
  }]}]
}";

$json = json_decode($resultJson, true);

foreach ($json["Events"] as &$event) {
	if ($event["district"] == null) $event["district"] = new array("abbreviation" => "na", "display_name" => "Not A District", "key" => $seasonYear."na", "year" => $seasonYear);
}
unset($event);

echo json_encode($json);
?>
