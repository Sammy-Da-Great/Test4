<?php

if (!isSet($_GET,$_GET["teamNumber"],$_GET["eventCode"])) {
	$error = "Something went wrong, please try again.";
	include "index.php";
	exit;
}

$urlParts = split("/",$_SERVER["REQUEST_URI"]);
$baseURL = $urlParts[1];
for ($i = 2; $i < count($urlParts)-1; $i++) {
	$baseURL += ".".$urlParts[$i];
}
$url = 'http://'.$_SERVER["HTTP_HOST"]."/".$baseURL.'/api/v1/retrieveTeam.php?teamNumber='.$_GET["teamNumber"]."&eventCode=".$_GET["eventCode"];

$showNoAlliance = isSet($_GET["showNoAlliance"]);

if ($showNoAlliance) {
	$url .= "&showNoAlliance=1";
}
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = json_decode(curl_exec($ch),true);
curl_close($ch);
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
</script>
</head>
<body onload="onLoad()">
<h1 style="text-align:center"><?php echo $result["TeamName"]." (".$result["TeamNumber"].") at ".$result["EventName"]; ?></h1>
<img id="logo" src="<?php echo $teamDataPath."/".$eventCode."/".$teamNumber."/picture.png"; ?>" style="display: block;margin: 0 auto; border: 1px solid white; width: 70%"/>
<h3 style="text-align:center">Quick Facts:</h3>
<table class="center">
<tr><td>Team Number:</td><td colspan="2"><a target="_blank" href="index.php?input=<?php echo $result["TeamNumber"]; ?>"><?php echo $result["TeamNumber"] ?></a> (<a target="_blank" href="<?php echo "http://thebluealliance.com/team/".$result["TeamNumber"]."/".$result["SeasonYear"]; ?>">View on The Blue Alliance</a>)</td></tr>
<tr><td>Event Key:</td><td colspan="2"><a target="_blank" href="index.php?input=<?php echo $result["EventCode"]; ?>"><?php echo $result["EventCode"]; ?></a> (<a target="_blank" href=<?php echo "\"https://www.thebluealliance.com/event/".$result["EventCode"]."\"" ?>>View on The Blue Alliance</a>)</td></tr>
<tr><td>Team@Event Status:</td><td colspan="2"><?php echo $result["TeamStatusString"]; ?></td></tr>
<tr><td>Autonomous:</td><td><?php echo $result["Pit"]["AutonomousNotes"]; ?></td></tr>
<tr><td>Teleoperated:</td><td><?php echo $result["Pit"]["TeleoperatedNotes"]; ?></td></tr>
<tr><td>General Notes:</td><td><?php echo $result["Pit"]["GeneralNotes"]; ?></td></tr>
<tr><td>Low Goal visits per match:</td><td>Pit: <?php echo $result["Pit"]["LowGoalVisits"]; ?></td><td>Average: <?php echo $result["Stand"]["AvgLowGoalVisits"]; ?></td></tr>
<tr><td>High Goal visits per match:</td><td>Pit: <?php echo $result["Pit"]["HighGoalVisits"]; ?></td><td>Average: <?php echo $result["Stand"]["AvgHighGoalVisits"]; ?></td></tr>
<tr><td>Gears delivered per match:</td><td>Pit: <?php echo $result["Pit"]["GearsDelivered"]; ?></td><td>Average: <?php echo $result["Stand"]["AvgGearsDelivered"]; ?></td></tr>
<tr><td>Gears picked up per match:</td><td>Pit: <?php echo $result["Pit"]["GearsPickedUp"]; ?></td><td>Average: <?php echo $result["Stand"]["AvgGearsPickedUp"]; ?></td></tr>
<tr><td>Affected by fuel on field:</td><td>Pit: <?php echo $result["Pit"]["AffectedByFuelOnField"]; ?></td><td>Average: See table below</td></tr>
<tr><td>Affected by defense:</td><td>Pit: <?php echo $result["Pit"]["Defendable"]; ?></td><td>Average: See table below</td></tr>
<tr><td>Climb:</td><td>Pit: <?php echo $result["Pit"]["ClimbRating"] ?></td><td>Average: See table below</td></tr>
<?php if ($showNoAlliance) echo "<tr><td>No Alliance:</td><td>Pit: ".$result["Pit"]["NoAlliance"]."</td><td>Average: See table below</td></tr>" ?>
</table>
<p></p>
<h3 style="text-align:center">Raw Data</h3>
<table class="sortable" id = "center">
<tr><th class="unsortable">Team Number</th><th>Scouter Name</th><th>Match Number</th><th>Low Goal Visits</th><th>High Goal Visits</th><th>Gears Picked up</th><th>Gears Delivered</th><th>Climb</th><th>Dead On Field</th><th>Fuel impacts driving</th><th>Blocked by defense</th><th class="unsortable">Autonomous Notes</th><th class="unsortable">Teleoperated Notes</th><th class="unsortable">General Notes</th><?php if ($showNoAlliance) echo "<th>No Alliance</th>" ?></tr>
<?php
foreach ($result["Stand"]["Matches"] as $match) {
	if ($match == null) continue;
	echo "<tr><td>".$match["TeamNumber"]."</td><td>".$match["ScouterName"]."</td><td>".$match["MatchNumber"]."</td><td>".$match["LowGoalVisits"]."</td><td>".$match["HighGoalVisits"]."</td><td>".$match["GearsPickup"]."</td><td>".$match["GearsDelivered"]."</td><td>".$match["Climb"]."</td><td>".$match["DOF"]."</td><td>".$match["FuelDrive"]."</td><td>".$match["Defended"]."</td><td>".$match["AutoNotes"]."</td><td>".$match["TeleopNotes"]."</td><td>".$match["Notes"]."</td>".(($showNoAlliance) ? "<td>".$match["NoAlliance"]."</td>" : "")."</tr>\n";
} ?>
</table>
<p></p>
<p>Link for sharing: <a id="ShareLink" href="http://orfscoutingservice.azurewebsites.net/index.php?team=<?php echo $teamNumber; ?>">http://orfscoutingservice.azurewebsites.net/index.php?team=<?php echo $teamNumber; ?></a></p><br/>
<div style="text-align:center;"><input type="button" style="font-size: 20;" onclick="returnHome()" value="Go Back"></div><br/>
</body></html>