<?php
header("Content-Type: application/json");
$cacheDir = __DIR__."/Cache/";
if (!file_exists($cacheDir)) {
	mkdir($cacheDir);
}

if (file_exists($cacheDir."/fullResponse.json")) {
	$cachedFile = file($cacheDir."/fullResponse.json");
	if (time() - strtotime($cachedFile[0]) < 300 && !isSet($_GET["forceRefresh"])) {
		echo $cachedFile[1];
		exit;
	} else {
		unlink($cacheDir."/fullResponse.json");
	}
}

$data = array();
$data["CurrentVersion"] = ["2018","1","0"];

#Request 1: District Events
$data["Events"] = array(array(
	"city" => "Tacoma",
	"country" => "USA",
	"district" => array (
		"abbreviation" => "pnw",
		"display_name" => "Pacific Northwest",
		"key" => "2018pnw",
		"year" => 2018
	),
	"end_date" => "2018-12-31",
	"event_code" => "test",
	"event_type" => 0,
	"key" => "2018test",
	"name" => "2018 Test Event",
	"start_date" => "2018-01-06",
	"state_prov" => "WA",
	"year" => 2018
));

#Request 2: Teams for each event
$teamAtEventCacheDir = $cacheDir."TeamList/";
if (!file_exists($teamAtEventCacheDir)) {
	mkdir($teamAtEventCacheDir);
}
$data["TeamsByEvent"] = array();
foreach ($data["Events"] as $event) {
	$tmpData = array("EventKey"=> $event->key);
	$tmpData["TeamList"] = array(
		array(
			"key" => "frc0001",
			"team_number" => 0001,
			"nickname" => "Team #1",
			"name" => "Team #1",
			"city" => "City",
			"state_prov" => "WA",
			"country" => "USA"
			),
		array(
			"key" => "frc0002",
			"team_number" => 0002,
			"nickname" => "Team #2",
			"name" => "Team #2",
			"city" => "City",
			"state_prov" => "WA",
			"country" => "USA"
			),
		array(
			"key" => "frc0003",
			"team_number" => 0003,
			"nickname" => "Team #3",
			"name" => "Team #3",
			"city" => "City",
			"state_prov" => "WA",
			"country" => "USA"
			),
		array(
			"key" => "frc0004",
			"team_number" => 0004,
			"nickname" => "Team #4",
			"name" => "Team #4",
			"city" => "City",
			"state_prov" => "WA",
			"country" => "USA"
			),
		array(
			"key" => "frc0005",
			"team_number" => 0005,
			"nickname" => "Team #5",
			"name" => "Team #5",
			"city" => "City",
			"state_prov" => "WA",
			"country" => "USA"
			),
		array(
			"key" => "frc0006",
			"team_number" => 0006,
			"nickname" => "Team #6",
			"name" => "Team #6",
			"city" => "City",
			"state_prov" => "WA",
			"country" => "USA"
			)
	);
}
$data["TeamsByEvent"][] = $tmpData;
unset($tmpData);

#Request 3: Matches for each team for each event.
$teamMatchesCacheDir = $cacheDir."teamMatches/";
if (!file_exists($teamMatchesCacheDir)) {
	mkdir($teamMatchesCacheDir);
}
$data["EventMatches"] = array();	
foreach ($data["TeamsByEvent"] as $eventData) {
	$event = (object) array ("key" => $eventData["EventKey"]);
	foreach($eventData["TeamList"] as $team) {
		$tmpDataTeam = array( "EventKey" => $event->key , "TeamNumber" => $team->team_number);
		$tmpDataTeam["Matches"] = array("2018test_qm1","2018test_qm2","2018test_qm3","2018test_qm4","2018test_qm5","2018test_qm6","2018test_qm7","2018test_qm8","2018test_qm9");	
	
		$data["EventMatches"][] = $tmpDataTeam;
	}
}
	
unset($event, $tmpDataTeam);

$responseCache = fopen($cacheDir."/fullResponse.json","w"); //Cache full response so it can be used in the next 5 minutes.
fwrite($responseCache,time()."\n".json_encode($data));
fclose($responseCache);
echo json_encode($data);
?>
