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
	$tmpData = array("EventKey"=> $event["key"]);
	$tmpData["TeamList"] = array( //Below are some example results, gathered from https://www.thebluealliance.com/api/v3/team/frc{TEAM_NUMBER}/simple, When we figure out what teams are comming, we can fill in this information.
		json_decode('{ "city": "Olympia", "country": "USA", "key": "frc4450", "name": "The Community Foundation of South Puget Sound/Washington State Office of Superintendent of Public Education/Olympia School District/Advance Equipment Company/Boeing/Diamond Technology Innovations/H2OJet, Inc./Obee Credit Union/FastSigns, Inc./Christopher Cook/Google/Capital Industrial, Inc/Kiwanis International/Amazon/SPEEA/Capital Medical Center/SCJ alliance/Olympia School District Education Foundation/Solidworks/Microsoft&Capital High School&Avanti High School&Olympia High School", "nickname": "Olympia Robotics Federation", "state_prov": "Washington", "team_number": 4450 }',true),
		json_decode('{ "city": "Tacoma", "country": "USA", "key": "frc2557", "name": "Boeing/OSPI/Tacoma School District/F5 Networks, Inc/Amazon/Zumar Industries Incorporated/Blue Origin/Hewitt Cabinets & Interiors/Intellectual Ventures/Elements of Education Partners/Marco Heidner foundation/FIRST Washington/Multicare/Microsoft/Aluminumand Bronze Fabricators/Pierce Aluminum Company/Northwest Pipe and Steel/Lakewood Rotary Club&Tacoma School of the Arts", "nickname": "SOTAbots ", "state_prov": "Washington", "team_number": 2557}',true),
		json_decode('{ "city": "Bainbridge Island", "country": "USA", "key": "frc4915", "name": "Boeing/Bainbridge Schools Foundation/Office of Superintendent of Public Instruction/Xerox/Microsoft/Google/Bainbridge Island School District&Bainbridge High School", "nickname": "Spartronics", "state_prov": "Washington", "team_number": 4915 }',true),
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
	$event = array ("key" => $eventData["EventKey"]);
	foreach($eventData["TeamList"] as $team) {
		$tmpDataTeam = array( "EventKey" => $event["key"] , "TeamNumber" => $team["team_number"]);
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
