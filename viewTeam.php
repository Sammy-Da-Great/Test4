<?php

if (!isSet($_GET,$_GET["teamNumber"],$_GET["eventCode"])) {
	$error = "Something went wrong, please try again.";
	include "index.php";
	exit;
}

include_once "util.php";
LogToFile("ViewTeam start");

LogToFile("Begin API call");
ob_start(PHP_OUTPUT_HANDLER_CLEANABLE);
include_once "api/v1/retrieveTeam.php";
$result = json_decode(ob_get_clean(), true);
LogToFile("API Call complete");

function arrayToString($array) {
	$string = "[";
	for($i = 0; $i < count($array); $i++) {
		$string .= "'".$array[$i]."',";
	}
	$string = substr($string,0, strlen($string)-1)."]";
	return $string;
}
?>

<head>
<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.1.1.min.js"></script>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
<link rel="icon" href="/favicon.ico" type="image/x-icon">
<script type="text/javascript" src="sortableTable/sortable.js"></script>
<title><?php echo $result["TeamNumber"]." at ".$result["EventName"]; ?> - ORF Scouting</title>
<style>
a {
	color: white;
}

table, th, td {
    border: 1px solid white;
}

table.center {
	margin-left: auto;
	margin-right: auto;
	width: 65%;
}

#center {
	margin-left: auto;
	margin-right: auto;
	width: 65%;
}

p, h1, h3, td, th {
	color: white;
	text-align: center;
}

body {
	background-color: black;
}

td, th { 
	padding: 5px; 
}
</style>
<script>
function returnHome() {
	window.location.href = "index.php";
}

function getBackPage() {
	var url = window.location.href;
	var broken = url.split("/");
	var newUrl = broken[0];
	console.log(broken[0]);
	for (var i = 2; i < broken.length-1; i++) {
		newUrl = newUrl.concat("/",broken[i-1]);
	}
	return newUrl;
}

function onLoad() {
	$("#ShareLink").attr("href",window.location.href);
	$("#ShareLink").html(window.location.href);
	
	var type = "<?php echo $result["Media"][0]["type"]; ?>";
	var key = "<?php switch ($result["Media"][0]["type"]) {
		case "imgur":
		echo $result["Media"][0]["foreign_key"];
		break;

		case "cdphotothread":
		echo $result["Media"][0]["details"]["image_partial"];
	}
	?>";
	
	switch (type) {
		case "imgur":
			$("#logo").attr("src","http://imgur.com/"+key+".png");
			break;
				
		case "cdphotothread":
			$("#logo").attr("http://www.chiefdelphi.com/media/img/"+key);
			break;
	}
}

function openWindow(type, description) {
	var newWindow = window.open("","Description","width=500,height=500,left=50");
	newWindow.document.write("<body style=\"background-color:black;text-align:center;color:white;\"><h2>"+type+"</h2><br/>");
	for(var i = 0; i < description.length; i++) {
		newWindow.document.write("<p>"+description[i]+"</p><br/>");
	}
	newWindow.document.write("<button onclick=\"window.close()\">Close</button></body>");
}
</script>
</head>
<body onload="onLoad()">
<h1 style="text-align:center"><?php echo $result["TeamName"]." (".$result["TeamNumber"].") at ".$result["EventName"]; ?></h1>
<img id="logo" src="/picture.png" style="display: block;margin: 0 auto; border: 1px solid white; width: 70%"/>
<h3 style="text-align:center">Quick Facts:</h3>
<table class="center">
<tr><td>Team Number:</td><td colspan="2"><a target="_blank" href="index.php?input=<?php echo $result["TeamNumber"].(($showNoAlliance) ? "&showNoAlliance=1": ""); ?>"><?php echo $result["TeamNumber"] ?></a> (<a target="_blank" href="<?php echo "http://thebluealliance.com/team/".$result["TeamNumber"]."/".$result["SeasonYear"]; ?>">View on The Blue Alliance</a>)</td></tr>
<tr><td>Event Key:</td><td colspan="2"><a target="_blank" href="index.php?input=<?php echo $result["EventCode"].(($showNoAlliance) ? "&showNoAlliance=1": ""); ?>"><?php echo $result["EventCode"]; ?></a> (<a target="_blank" href=<?php echo "\"https://www.thebluealliance.com/event/".$result["EventCode"]."\"" ?>>View on The Blue Alliance</a>)</td></tr>
<tr><td>Team@Event Status:</td><td colspan="2"><?php echo $result["TeamStatusString"]; ?></td></tr>
<tr><td>Autonomous:</td><td><?php echo $result["Pit"]["AutonomousNotes"]; ?></td></tr>
<tr><td>Teleoperated:</td><td><?php echo $result["Pit"]["TeleoperatedNotes"]; ?></td></tr>
<tr><td>General Notes:</td><td><?php echo $result["Pit"]["GeneralNotes"]; ?></td></tr>
<tr><td>Switch visits per match:</td><td>Pit: <?php //echo $result["Pit"]["SwitchVists"]; ?></td><td>Average: <?php echo $result["Stand"]["AvgSwitchVisits"]; ?></td></tr>
<tr><td>Scale visits per match:</td><td>Pit: <?php //echo $result["Pit"]["ScaleVisits"]; ?></td><td>Average: <?php echo $result["Stand"]["AvgScaleVisits"]; ?></td></tr>
<tr><td>Exchange visits per match:</td><td>Pit: <?php //echo $result["Pit"]["ExchangeVisits"]; ?></td><td>Average: <?php echo $result["Stand"]["AvgExchangeVisits"]; ?></td></tr>
<tr><td>Climb:</td><td>Pit: <?php //echo $result["Pit"]["ClimbRating"] ?></td><td>Average: See table below</td></tr>
<?php if ($showNoAlliance) echo "<tr><td>No Alliance:</td><td>Pit: ".$result["Pit"]["NoAlliance"]."</td><td>Average: See table below</td></tr>" ?>
</table>
<p></p>
<h3 style="text-align:center">Raw Data</h3>
<table class="sortable" id = "center">
<tr><th class="unsortable">Team Number</th><th>Scouter Name</th><th>Match Number</th><th>No Show</th><th>Starting Position</th><th>Auto - Baseline</th><th>Auto - Placed Switch</th><th>Auto - Placed Scale</th><th class="unsortable">Auto - Notes</th><th>Teleop - Switch Visits</th><th>Teleop - Scale Visits</th><th>Teleop - Exchange Visits</th><th class="unsortable">Teleop - Notes</th><th>Teleop - Boost Used</th><th>Teleop - Force Used</th><th>Teleop - Levitate Used</th><th>Climb</th><th>Died On Field</th><th class="unsortable">General Notes</th><?php if ($showNoAlliance) echo "<th>No Alliance</th>" ?></tr>
<?php
foreach ($result["Stand"]["Matches"] as $match) {
	if ($match == null || $match[0] == null) continue;
	$processedMatch = array();
	$keys = array_keys($match[0]);
	var_dump($match);
	foreach ($match as $oneScout) {
		foreach ($keys as $key) {
			if (!array_key_exists($key,$processedMatch)) $processedMatch[$key] = array();
			$processedMatch[$key][] = $oneScout[$key];
		}
	}
	echo "<td>".$processedMatch["TeamNumber"][0]."</td><td><a href=\"#\" onclick=\"openWindow('Scouts for ".$processedMatch["TeamNumber"][0]." for Match ".$processedMatch["MatchNumber"][0]."',".arrayToString($processedMatch["ScouterName"]).")\">Show All</a></td><td>".$processedMatch["MatchNumber"][0]."</td><td><a href=\"#\" onclick=\"openWindow('No Show by ".$processedMatch["TeamNumber"][0]." for Match ".$processedMatch["MatchNumber"][0]."',".arrayToString($processedMatch["Pre_NoShow"]).")\">Show All</a></td><td><a href=\"#\" onclick=\"openWindow('Starting positions for ".$processedMatch["TeamNumber"][0]." for Match ".$processedMatch["MatchNumber"][0]."',".arrayToString($processedMatch["Pre_StartingPos"]).")\">Show All</a></td><td><a href=\"#\" onclick=\"openWindow('Baseline crosses in Auto for ".$processedMatch["TeamNumber"][0]." for Match ".$processedMatch["MatchNumber"][0]."',".arrayToString($processedMatch["Auto_CrossedBaseline"]).")\">Show All</a></td><td><a href=\"#\" onclick=\"openWindow('Power Cube placed on Switch in Auto by ".$processedMatch["TeamNumber"][0]." in Match ".$processedMatch["MatchNumber"][0]."',".arrayToString($processedMatch["Auto_PlaceSwitch"]).")\">Show All</a></td><td><a href=\"#\" onclick=\"openWindow('Power Cube placed on Scale in Auto by ".$processedMatch["TeamNumber"][0]." in Match ".$processedMatch["MatchNumber"][0]."',".arrayToString($processedMatch["Auto_PlaceScale"]).")\">Show All</a></td><td><a href=\"#\" onclick=\"openWindow('Autonomous Notes for ".$processedMatch["TeamNumber"][0]." for Match ".$processedMatch["MatchNumber"][0]."',".arrayToString($processedMatch["Auto_Notes"]).")\">Show All</a></td><td><a href=\"#\" onclick=\"openWindow('Switch visits in Teleop by ".$processedMatch["TeamNumber"][0]." in Match ".$processedMatch["MatchNumber"][0]."',".arrayToString($processedMatch["Teleop_SwitchPlace"]).")\">Show All</a></td><td><a href=\"#\" onclick=\"openWindow('Scale visits in Teleop by ".$processedMatch["TeamNumber"][0]." in Match ".$processedMatch["MatchNumber"][0]."',".arrayToString($processedMatch["Teleop_ScalePlace"]).")\">Show All</a></td><td><a href=\"#\" onclick=\"openWindow('Exchange Zone Visits by ".$processedMatch["TeamNumber"][0]." in Match ".$processedMatch["MatchNumber"][0]."',".arrayToString($processedMatch["Teleop_ExchangeVisit"]).")\">Show All</a></td><td><a href=\"#\" onclick=\"openWindow('Teleop Notes for ".$processedMatch["TeamNumber"][0]." in Match ".$processedMatch["MatchNumber"][0]."',".arrayToString($processedMatch["Teleop_Notes"]).")\">Show All</a></td><td><a href=\"#\" onclick=\"openWindow('Boost used by ".$processedMatch["TeamNumber"][0]."\'s alliance in Match ".$processedMatch["MatchNumber"][0]."',".arrayToString($processedMatch["Teleop_BoostUsed"]).")\">Show All</a></td><td><a href=\"#\" onclick=\"openWindow('Force used by ".$processedMatch["TeamNumber"][0]."\'s alliance in Match ".$processedMatch["MatchNumber"][0]."',".arrayToString($processedMatch["Teleop_ForceUsed"]).")\">Show All</a></td><td><a href=\"#\" onclick=\"openWindow('Levitate used by ".$processedMatch["TeamNumber"][0]."\'s alliance in Match ".$processedMatch["MatchNumber"][0]."',".arrayToString($processedMatch["Teleop_LevitateUsed"]).")\">Show All</a></td><td><a href=\"#\" onclick=\"openWindow('Climb status for ".$processedMatch["TeamNumber"][0]." in Match ".$processedMatch["MatchNumber"][0]."',".arrayToString($processedMatch["Post_Climb"]).")\">Show All</a></td><td><a href=\"#\" onclick=\"openWindow('DOFs for ".$processedMatch["TeamNumber"][0]." in Match ".$processedMatch["MatchNumber"][0]."',".arrayToString($processedMatch["DOF"]).")\">Show All</a></td><td><a href=\"#\" onclick=\"openWindow('General Notes for ".$processedMatch["TeamNumber"][0]." in Match ".$processedMatch["MatchNumber"][0]."',".arrayToString($processedMatch["Notes"]).")\">Show All</a></td>".(($showNoAlliance) ? "<td><a href=\"#\" onclick=\"openWindow('No Alliance markings for ".$processedMatch["TeamNumber"][0]." for Match ".$processedMatch["MatchNumber"][0]."',".arrayToString($processedMatch["NoAlliance"]).")\">Show All</a></td>" : "")."</tr>\n";
}
?>
</table>
<p></p>
<p>Link for sharing: <a id="ShareLink" href="http://orfscoutingservice.azurewebsites.net/index.php?team=<?php echo $result["TeamNumber"]; ?>">http://orfscoutingservice.azurewebsites.net/index.php?team=<?php echo $result["TeamNumber"]; ?></a></p><br/>
<div style="text-align:center;"><input type="button" style="font-size: 20;" onclick="returnHome()" value="Go Back"></div><br/>
</body></html>