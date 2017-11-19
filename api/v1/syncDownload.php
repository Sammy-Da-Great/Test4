<?php
header("Content-Type: application/json");

include "/config.php";

$baseUrl = 'http://www.thebluealliance.com/api/v3/';

#Request 1: District Events
$url1 = $baseUrl.'district/'.$seasonYear.$districtKey.'/events/keys';
$ch1 = curl_init($url1);
curl_setopt($ch1, CURLOPT_HTTPHEADER, array('X-TBA-Auth-Key: '.$TBAAuthKey));
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
$districtEventsJson = curl_exec($ch1);
curl_close($ch1);

#Request 2: Teams for each event
#foreach ($eventKey as json_decode($districtEventsJson)) {

#}

echo $url1;
?>
{
 "Events" : <?php echo $districtEventsJson ?>   
}