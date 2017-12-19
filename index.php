<?php

if (!isSet($error)) {
	$error = "";
}
$events = array();
$teams = array();
$inputTeam = false;
$inputEvent = false;
$input = "";

if (isSet($_GET["input"]) && $error == "") {
	$input = $_GET["input"];
	$eventDirectories = glob('api/v1/*' , GLOB_ONLYDIR);
	foreach($eventDirectories as $fileName) {
		if (explode("/", $fileName)[2] == $input) {
			$inputTeam = false;
			$inputEvent = true;
			$teams = glob('api/v1/'.$input.'/*', GLOB_ONLYDIR);
			break;
		}
		$teamDirectories = glob($fileName."/*", GLOB_ONLYDIR);
		foreach($teamDirectories as $teamFolder) {
			if (explode("/",$teamFolder)[3] == $input) {
				$inputEvent = false;
				$inputTeam = true;
				array_push($events, $teamFolder);
			}
		}
	}
	if (count($events) == 0 && count($teams) == 0) {
	$error = "Team number or Event Code not found! There may be scouting data yet!";
	}
}

include "config.php";
	
function getNameEventCode($code, $TBAAuthKey) {
	$urlPrefix = 'http://www.thebluealliance.com/api/v3/event/';
	$urlSuffix = '/simple';
	
	$url = $urlPrefix.$code.$urlSuffix;
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-TBA-Auth-Key: '.$TBAAuthKey));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = json_decode(curl_exec($ch),true);
	if (isSet($result["name"])) {
		return $result["name"];
	} else {
		return $code;
	}
	curl_close($ch);
}
function getNameTeamNumber($teamNumber, $TBAAuthKey) {
	$urlPrefix = 'http://www.thebluealliance.com/api/v3/team/frc';
	$urlSuffix = '/simple';
	
	$url = $urlPrefix.$teamNumber.$urlSuffix;
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-TBA-Auth-Key: '.$TBAAuthKey));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = json_decode(curl_exec($ch),true);
	curl_close($ch);
	if (isSet($result["nickname"])) {
		return $result["nickname"];
	} else {
		return $teamNumber;
	}
}
?>

<head>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<link rel="icon" href="favicon.ico" type="image/x-icon">
<title>ORF Scouter</title>
<style>
body {
	background-color: black;
}
img {
	width: 25%;
}
p {
	font-size: 25;
}
p, h1, h2, h3 {
	color: white;
}

div {
    position: absolute;
    top:0;
    bottom: 0;
    left: 0;
    right: 0;

    margin: auto;
	text-align: center;
}
</style><script>
function onLoad() {
	<?php if ($error == "" && $input != "") {
	echo "document.getElementById(\"input\").defaultValue = \"".$input."\";";
	}
	?>
}

function loadTeamAtEvent(team,event) {
	window.location.href = "viewTeam.php?eventCode="+ event + "&teamNumber=" + team;
}
</script></head><body onload="onLoad()">
<div>
	<img src="logo.png"/>
	<h1>Welcome to the ORF Scouting Viewer!</h1>
	<?php
	if (!(count($events) > 0)) {
	echo "<form method=\"get\" action=\"index.php\">
	<p>Team Number or Event Key:</p>
	<p><input style=\"font-size: 20; text-align:center;\" id=\"input\" name=\"input\" type=\"text\"></input></p>
	<p><input style=\"font-size: 20;\" type=\"submit\"></input></p>
	</form>";
	}
	?>
	
	<p><?php echo $error ?></p>
	<?php
	if (count($events) > 0 && $error == "" && $inputTeam && !$inputEvent) {
		echo "<p style='font-size:24;'>Team ".$input." has been scouted at these events:</p>";
		foreach($events as $event) {
			$eventCode = explode("/",$event)[2];
			echo "<p><button style='font-size: 30;' onClick='window.location.href=\"viewTeam.php?eventCode=".$eventCode."&teamNumber=".$input."\"'>".getNameEventCode($eventCode, $TBAAuthKey)."</button></p>";
		}
		
		echo "<br/><p><button style=\"font-size: 20;\" onClick='window.location.href=\"index.php\"'>Go Back</button><br/>";
	}
	
	if (count($teams) > 0 && $error == "" && !$inputTeam && $inputEvent) {
		echo "<p style='font-size:24;'>These teams have been scouted from the event \"".getNameEventCode($input, $TBAAuthKey)."\":</p>";
		foreach($teams as $team) {
			$teamNumber = explode("/",$team)[3];
			echo "<p><button style='font-size: 30;' onClick='window.location.href=\"viewTeam.php?eventCode=".$input."&teamNumber=".$teamNumber."\"'>".$teamNumber." - ".getNameTeamNumber($teamNumber, $TBAAuthKey)."</button></p>";
		}
		
		echo "<br/><p><button style=\"font-size: 20;\" onClick='window.location.href=\"index.php\"'>Go Back</button><br/>";
	}
	?>
</div>