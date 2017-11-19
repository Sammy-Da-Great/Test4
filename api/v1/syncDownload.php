<?php
header("Content-Type: application/json");

include "../../config.php";

$baseUrl = 'http://www.thebluealliance.com/api/v3/';
?>
{
 "Events" : <?php #Request 1: District Events
$url1 = $baseUrl.'district/'.$seasonYear.$districtKey.'/events/simple';
$ch1 = curl_init($url1);
curl_setopt($ch1, CURLOPT_HTTPHEADER, array('X-TBA-Auth-Key: '.$TBAAuthKey));
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
$districtEventsJson = curl_exec($ch1);
curl_close($ch1);
?>,
 "TeamsByEvent" : <?php #Request 2: Teams for each event
$teamsAtEvents = array();
foreach (json_decode($districtEventsJson) as $event) {

    $url2 = $baseUrl.'event/'.$event['key'].'/events/keys';
    $ch2 = curl_init($url2);
    curl_setopt($ch2, CURLOPT_HTTPHEADER, array('X-TBA-Auth-Key: '.$TBAAuthKey));
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    $teamsAtEvents[$event['key']] = curl_exec($ch2);
    curl_close($ch2);
    echo json_encode($teamsAtEvents);
}
?>
}