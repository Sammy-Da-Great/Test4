<?php
if (!isSet($_GET["teamNumber"])) {
	$error = "Something went wrong, please try again.";
	include "index.php";
	exit;
} else {
	$teamNumber = $_GET["teamNumber"];
}

if (!isSet($_GET["eventCode"])) {
	$error = "Something went wrong, please try again.";
	include "index.php";
	exit;
} else {
	$eventCode = $_GET["eventCode"];
	$seasonYear = substr($eventCode,0,4);
}

$teamDataPath = "api/v1/".$eventCode."/".$teamNumber;

if (!file_exists($teamDataPath)) {
	$error = "The team entered hasn't been scouted yet for that event!";
	include "index.php";
	exit;
}

if (filesize($teamDataPath."/pitScout.csv")>0) {
	$file = fopen($teamDataPath."/pitScout.csv","r");
	$raw = explode(",",fread($file,filesize($teamDataPath."/pitScout.csv")));
	$autonomousPit = $raw[7];
	$notesPit = $raw[6];
	$lowPit = $raw[3];
	$highPit = $raw[4];
	$climbPit = $raw[9];
	$gearsDeliverPit = $raw[5];
	$teleopPit = $raw[8];
	$gearsPickupPit = $raw[10];
	$defensePit = $raw[11];
	$fuelDrivePit = $raw[12];
	fclose($file);
} else {
	$autonomousPit = "Unknown";
	$notesPit = "Unknown";
	$lowPit = "Unknown";
	$highPit = "Unknown";
	$climbPit = "Unknown";
	$gearsDeliverPit = "Unknown";
	$gearsPickupPit = "Unknown";
	$teleopPit = "Unknown";
	$defensePit = "Unknown";
	$fuelDrivePit = "Unknown";
}

if (filesize($teamDataPath."/rawData.csv")>0) {
	$file = fopen($teamDataPath."/rawData.csv","r");
	$rawLine = explode("\n",fread ($file,filesize($teamDataPath."/rawData.csv")));
	$GearsDelivered = 0;
	$Low = 0;
	$High = 0;
	$GearsPickup = 0;
	foreach($rawLine as $line) {
		$break = explode(",",$line);
		for ($i = 0; $i < count($break); $i++) {
			switch($i) {
				case 4:
					$Low += $break[$i];
					break;
				case 5:
					$High += $break[$i];
					break;
				case 6:
					$GearsDelivered += $break[$i];
					break;
				case 12:
					$GearsPickup += $break[$i];
					break;
			}
		}
	}
	$GearsDelivered = $GearsDelivered/(count($rawLine)-1);
	$GearsPickup = $GearsPickup/(count($rawLine)-1);
	$Low = $Low/(count($rawLine)-1);
	$High = $High/(count($rawLine)-1);
	fclose($file);
} else {
	$GearsDelivered = "Unknown";
	$Low = "Unknown";
	$High = "Unknown";
	$GearsPickup = "Unknown";
}

include "config.php";

$teamName = null;
function getNameEventCode() {
	global $teamName, $TBAAuthKey, $eventCode;
	if (isSet($teamName)) return $teamName;

	$url = 'http://www.thebluealliance.com/api/v3/event/'.$eventCode.'/simple';
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-TBA-Auth-Key: '.$TBAAuthKey));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = json_decode(curl_exec($ch),true);
	if (isSet($result["name"])) {
		$teamName = $result["name"];
		return $teamName;
	} else {
		return $eventCode;
	}
	curl_close($ch);
}

$teamAtEventStatus = null;
function getTeamAtEventStatus() {
	global $teamNumber, $TBAAuthKey, $eventCode;
	if (isSet($teamAtEventStatus)) return $teamAtEventStatus;
	
	$url = 'http://www.thebluealliance.com/api/v3/team/frc'.$teamNumber.'/event/'.$eventCode.'/status';
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-TBA-Auth-Key: '.$TBAAuthKey));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = json_decode(curl_exec($ch),true);
	if (isSet($result["overall_status_str"])) {
		$teamAtEventStatus = $result["overall_status_str"];
		return $teamAtEventStatus;
	} else {
		return "Status Unavailable";
	}
	curl_close($ch);
}
?>

<head>
<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.1.1.min.js"></script>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
<link rel="icon" href="/favicon.ico" type="image/x-icon">
<script type="text/javascript" src="sortableTable/sortable.js"></script>
<title><?php echo $teamNumber." at ".getNameEventCode($eventCode); ?> - ORF Scouting</title>
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
	
	$.ajaxSetup({headers: {"X-TBA-Auth-Key": "<?php echo $TBAAuthKey; ?>"}})
	if (isNaN(<?php echo $teamNumber; ?>) == false) {
		$.get("https://www.thebluealliance.com/api/v3/team/frc<?php echo $teamNumber ?>/media/<?php echo $seasonYear; ?>", function(data, status) {
			for (i = 0; i < data.length; i++) {
				var primary = data[i];
				switch (primary.type) {
					case "imgur":
						$("#logo").attr("src","http://imgur.com/"+primary.foreign_key+".png");
						console.log(primary.foreign_key);
						i = data.length;
						break;
				
					case "cdphotothread":
						$("#logo").attr("http://www.chiefdelphi.com/media/img/"+primary.details.image_partial);
						i = data.length;
						break;
						
					default:
						continue;
				}
			}
		});
	}
}
</script>
</head>
<body onload="onLoad()">
<h1 style="text-align:center"><?php echo $teamNumber." at ".getNameEventCode($eventCode); ?></h1>
<img id="logo" src="<?php echo $teamDataPath."/".$eventCode."/".$teamNumber."/picture.png"; ?>" style="display: block;margin: 0 auto; border: 1px solid white; width: 70%"/>
<h3 style="text-align:center">Quick Facts:</h3>
<table class="center">
<tr><td>Team Number:</td><td colspan="2"><?php echo $teamNumber ?> (<a target="_blank" href="<?php echo "http://thebluealliance.com/team/".$teamNumber."/".$seasonYear; ?>">View on The Blue Alliance</a>)</td></tr>
<tr><td>Event Key:</td><td colspan="2"><a target="_blank" href="index.php?input=<?php echo $eventCode; ?>"><?php echo $eventCode; ?></a> (<a target="_blank" href=<?php echo "\"https://www.thebluealliance.com/event/".$eventCode."\"" ?>>View on The Blue Alliance</a>)</td></tr>
<tr><td>Team@Event Status:</td><td colspan="2"><?php echo getTeamAtEventStatus(); ?></td></tr>
<tr><td>Autonomous:</td><td><?php echo $autonomousPit ?></td></tr>
<tr><td>Teleoperated:</td><td><?php echo $teleopPit ?></td></tr>
<tr><td>General Notes:</td><td><?php echo $notesPit ?></td></tr>
<tr><td>Low Goal visits per match:</td><td>Pit: <?php echo $lowPit ?></td><td>Average: <?php echo $Low ?></td></tr>
<tr><td>High Goal visits per match:</td><td>Pit: <?php echo $highPit ?></td><td>Average: <?php echo $High ?></td></tr>
<tr><td>Gears delivered per match:</td><td>Pit: <?php echo $gearsDeliverPit ?></td><td>Average: <?php echo $GearsDelivered ?></td></tr>
<tr><td>Gears picked up per match:</td><td>Pit: <?php echo $gearsPickupPit ?></td><td>Average: <?php echo $GearsPickup ?></td></tr>
<tr><td>Affected by fuel on field:</td><td>Pit: <?php echo $fuelDrivePit ?></td><td>Average: See table below</td></tr>
<tr><td>Affected by defense:</td><td>Pit: <?php echo $defensePit ?></td><td>Average: See table below</td></tr>
<tr><td>Climb:</td><td>Pit: <?php echo $climbPit ?></td><td>Average: See table below</td></tr>
</table>
<p></p>
<h3 style="text-align:center">Raw Data</h3>
<table class="sortable" id = "center">
<tr><th class="unsortable">Team Number</th><th>Scouter Name</th><th>Match Number</th><th>Low Goal Visits</th><th>High Goal Visits</th><th>Gears Picked up</th><th>Gears Delivered</th><th>Climb</th><th>Dead On Field</th><th>Fuel impacts driving</th><th>Blocked by defense</th><th class="unsortable">Autonomous Notes</th><th class="unsortable">Teleoperated Notes</th><th class="unsortable">General Notes</th></tr>
<?php
if (filesize($teamDataPath."/rawData.csv") > 0) {
	$rawFile = fopen($teamDataPath."/rawData.csv","r");
	$rawData = explode("\n",fread($rawFile,filesize($teamDataPath."/rawData.csv")));
	foreach ($rawData as $dataLine) {
		if ($dataLine == "") continue;
		$dataArray = explode(",",$dataLine);
		echo "<tr><td>".$dataArray[2]."</td><td>".$dataArray[1]."</td><td>".$dataArray[3]."</td><td>".$dataArray[4]."</td><td>".$dataArray[5]."</td><td>".$dataArray[12]."</td><td>".$dataArray[6]."</td><td>".$dataArray[11]."</td><td>".$dataArray[10]."</td><td>".$dataArray[14]."</td><td>".$dataArray[15]."</td><td>".$dataArray[8]."</td><td>".$dataArray[9]."</td><td>".$dataArray[7]."</td></tr>\n";
	}
	fclose($rawFile);
} ?>
</table>
<p></p>
<p>Link for sharing: <a id="ShareLink" href="http://orfscoutingservice.azurewebsites.net/index.php?team=<?php echo $teamNumber; ?>">http://orfscoutingservice.azurewebsites.net/index.php?team=<?php echo $teamNumber; ?></a></p><br/>
<div style="text-align:center;"><input type="button" style="font-size: 20;" onclick="returnHome()" value="Go Back"></div><br/>
</body>