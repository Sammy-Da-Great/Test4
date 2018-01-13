<?php
ini_set('max_execution_time', 600); //In seconds
header("Content-Type: application/json");

include "../../config.php";

$baseUrl = 'http://www.thebluealliance.com/api/v3/';
$cacheDir = __DIR__."/Cache/";
if (!file_exists($cacheDir)) {
	mkdir($cacheDir);
}

function curlRequest($url,$httpHeaders) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);
	
	$rawResult = curl_exec($ch);
	
	$result = array();
	
	$headersString = trim(substr($rawResult,0,curl_getinfo($ch, CURLINFO_HEADER_SIZE)));
	$headersArray = explode("\n",$headersString);
	foreach($headersArray as $header) {
		$parts = explode(": ",$header);
		if (isSet($parts[1])) {
		$result["header"][strtolower($parts[0])] = $parts[1];
		} else {
			$result["header"][strtolower($parts[0])] = $parts[0];
		}
	}
	
	$result["body"] = trim(substr($rawResult,curl_getinfo($ch, CURLINFO_HEADER_SIZE)));
	$result["http_code"] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
	curl_close($ch);
	
	return $result;
}

$data = array();
$data["CurrentVersion"] = ["2017","1","0"];

#Request 1: District Events

$districtEventsCacheDir = $cacheDir."EventInfo/";
if (!file_exists($districtEventsCacheDir)) {
	mkdir($districtEventsCacheDir);
}

$httpHeader = array('X-TBA-Auth-Key: '.$TBAAuthKey);
if (file_exists($districtEventsCacheDir."districtEvents.json")) {
	$file = file($districtEventsCacheDir."districtEvents.json", FILE_IGNORE_NEW_LINES);
	$lastModified = $file[0];
	$cachedJSON = $file[1];
	$httpHeader[1] = "If-Modified-Since: ".trim($lastModified);
}

$url1 = $baseUrl.'district/'.$seasonYear.$districtKey.'/events/simple';
$result1 = curlRequest($url1,$httpHeader); //From cache, nothing's changed.
if ($result1["http_code"] == 304) {
	$data["Events"] = json_decode($cachedJSON); 
} elseif ($result1["http_code"] != 200) { //Something went wrong, give cached data if possible, error entry otherwise.
	if (isSet($cachedJSON)) {
		$data["Events"] = json_decode($cachedJSON);
	} else {
		$data["Events"] = array(
		"city" => "Error",
		"country" => "ERR",
		"district" => array (
			"abbreviation" => "ERR",
			"display_name" => "Error",
			"key" => "0000err",
			"year" => 0000
		),
		"end_date" => "0000-00-00",
		"event_code" => "error",
		"event_type" => 0,
		"key" => "0000error",
		"name" => "An error has occurred, please try again later.",
		"start_date" => "0000-00-00",
		"state_prov" => "ER",
		"year" => 0000
		);
	}
} else { //New data!
	$data["Events"] = json_decode($result1["body"]);
	$dataToWrite = $result1["header"]["last-modified"]."\n".str_replace("\n","",$result1["body"]);
	file_put_contents($districtEventsCacheDir."districtEvents.json",$dataToWrite);
}

$worldCmpEventKeys = array("carv","gal","hop","new","roe","tur","tes","dar","dal","cur","cars","arc");
$worldCmpCacheDir = $cacheDir."EventInfo/worldCmp/";
if (!file_exists($worldCmpCacheDir)) {
	mkdir($worldCmpCacheDir,0777,true);
}
foreach($worldCmpEventKeys as $cmpKey) {
	$httpHeader = array('X-TBA-Auth-Key: '.$TBAAuthKey);
	if (file_exists($worldCmpCacheDir.$seasonYear.$cmpKey.".json")) {
		$file = file($worldCmpCacheDir.$seasonYear.$cmpKey.".json", FILE_IGNORE_NEW_LINES);
		$lastModified = $file[0];
		$cachedJSON = $file[1];
		$httpHeader[1] = "If-Modified-Since: ".$lastModified;
	}
	$url1Cmp = $baseUrl.'event/'.$seasonYear.$cmpKey.'/simple';
	$result1Cmp = curlRequest($url1Cmp,$httpHeader);
	if ($result1Cmp["http_code"] == 304) { //Nothing changed! Use the cached data!
		$data["Events"] = $data["Events"] + array(json_decode($cachedJSON,true)); 
	} elseif ($result1Cmp["http_code"] != 200) { //Something went wrong, give cached data if possible, error entry otherwise.
		if (isSet($cachedJSON)) {
		$data["Events"] = $data["Events"] + array(json_decode($cachedJSON,true));
		} else {
		$data["Events"] = $data["Events"] + array(
			"city" => "Error",
			"country" => "ERR",
			"district" => array (
				"abbreviation" => "ERR",
				"display_name" => "Error",
				"key" => "0000err",
				"year" => 0000
			),
			"end_date" => "0000-00-00",
			"event_code" => "error",
			"event_type" => 0,
			"key" => "0000error",
			"name" => "An error has occurred, please try again later.",
			"start_date" => "0000-00-00",
			"state_prov" => "ER",
			"year" => 0000
			);
		}
	} else { //New data!
		$data["Events"] = $data["Events"] + array(json_decode($result1Cmp["body"],true));
		$dataToWrite = $result1Cmp["header"]["last-modified"]."\n".str_replace("\n","",$result1Cmp["body"]);
		file_put_contents($worldCmpCacheDir.$seasonYear.$cmpKey.".json",$dataToWrite);
	}
}

#Request 2: Teams for each event
$teamAtEventCacheDir = $cacheDir."TeamList/";
if (!file_exists($teamAtEventCacheDir)) {
	mkdir($teamAtEventCacheDir);
}
$data["TeamsByEvent"] = array();
foreach ($data["Events"] as $event) {
	$httpHeader = array('X-TBA-Auth-Key: '.$TBAAuthKey);
	if (file_exists($teamAtEventCacheDir.$event->key.".json")) {
		$file = file($teamAtEventCacheDir.$event->key.".json", FILE_IGNORE_NEW_LINES);
		$lastModified = $file[0];
		$cachedJSON = $file[1];
		$httpHeader[1] = "If-Modified-Since: ".$lastModified;
	}
    $url2 = $baseUrl.'event/'.$event->key.'/teams/simple';
    $result2 = curlRequest($url2,$httpHeader);
	
	$tmpData = array("EventKey"=> $event->key);
	if ($result2["http_code"] == 304) {
		$tmpData["TeamList"] = json_decode($cachedJSON,true); 
	} elseif ($result2["http_code"] != 200) { //Something went wrong, give cached data if possible, error entry otherwise.
		if (isSet($cachedJSON)) {
		$tmpData["TeamList"] = json_decode($cachedJSON,true); 
		} else {
		$tmpData["TeamList"] = array(
			"key" => "frc0000",
			"team_number" => 0000,
			"nickname" => "Error",
			"name" => "An Error has occurred, please try again later.",
			"city" => "Error",
			"state_prov" => "ERR",
			"country" => "ERR"
			);
		}
	} else { //New data!
		$tmpData["TeamList"] = json_decode($result2["body"],true);
		$dataToWrite = $result2["header"]["last-modified"]."\n".str_replace("\n","",$result2["body"]);
		file_put_contents($teamAtEventCacheDir.$event["key"].".json", $dataToWrite);
	}
	$data["TeamsByEvent"] = $data["TeamsByEvent"] + array($tmpData);
	var_dump($data["TeamsByEvent"]);
	unset($tmpData);
}

#Request 3: Matches for each team for each event.
$teamMatchesCacheDir = $cacheDir."teamMatches/";
if (!file_exists($teamMatchesCacheDir)) {
	mkdir($teamMatchesCacheDir);
}
$data["EventMatches"] = array();
foreach ($data["Events"] as &$event) {
	$teamMatchesEventCacheDir = $teamMatchesCacheDir."/".$event->key."/";
	if (!file_exists($teamMatchesEventCacheDir)) {
		mkdir($teamMatchesEventCacheDir);
	}
	if (isSet($event->district)) if ($event->district == null) $event->district = array("abbreviation" => "na", "display_name" => "Not A District", "key" => $seasonYear."na", "year" => $seasonYear);
	
	$tmpDataEvent = array();
	
	foreach ($data["TeamsByEvent"][$event->key]["TeamList"] as $team) {
		$tmpDataTeam = array();
		
		$httpHeader = array('X-TBA-Auth-Key: '.$TBAAuthKey);
		if (file_exists($teamMatchesEventCacheDir.$team->key.".json")) {
			$file = file($teamMatchesEventCacheDir.$team->key.".json", FILE_IGNORE_NEW_LINES);
			$lastModified = $file[0];
			$cachedJSON = $file[1];
			$httpHeader[1] = "If-Modified-Since: ".$lastModified;
		}
		$url3 = $baseUrl.'team/'.$team->key.'/event/'.$event->key.'/matches/simple';
		$result3 = curlRequest($url3,$httpHeader);
		
		$tmpDataTeam = array( "EventKey" => $event->key , "TeamNumber" => $team->team_number);
		if ($result3["http_code"] == 304) {
			$tmpDataTeam["Matches"] = json_decode($cachedJSON,true); 
		} elseif ($result3["http_code"] != 200) { //Something went wrong, give cached data if possible, error entry otherwise.
			if (isSet($cachedJSON)) {
				$tmpDataTeam["Matches"] = json_decode($cachedJSON,true); 
			} else {
			$tmpDataTeam["Matches"] = array(array(
				"actual_time" => 0,
				"alliances" => array(
					"blue" => [
						"dq_team_keys" => [],
						"score" => 0,
						"surrogate_team_keys" => [],
						"team_keys" => [
							"frc0000",
							"frc0000",
							"frc0000"
						]
					],
					"red" => [
						"dq_team_keys" => [],
						"score" => 0,
						"surrogate_team_keys" => [],
						"team_keys" => [
							"frc0000",
							"frc0000",
							"frc0000"
						]
					]
				),
				"comp_level" => "er",
				"event_key" => "0000error",
				"key" => "0000error_er0",
				"match_number" => 0,
				"predicted_time" => 0,
				"set_number" => 0,
				"time" => 0,
				"winning_alliance" => "error"
			));
		}
		} else { //New data!
			$tmpDataTeam["Matches"] = json_decode($result3["body"], true);
			$dataToWrite = $result3["header"]["last-modified"]."\n".str_replace("\n","",$result3["body"]);
			file_put_contents($teamMatchesEventCacheDir.$team->key.".json", $dataToWrite);
		}
		
		$tmpDataEvent = $tmpDataEvent + array($tmpDataTeam);
	}
	
	$data["EventMatches"] = $data["EventMatches"] + array($tmpDataEvent);
}
unset($event, $tmpDataEvent, $tmpDataTeam);

//echo json_encode($data);
?>
