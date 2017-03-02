<?php
if (filesize("teamNumber.txt")>0) {
$file = fopen("teamNumber.txt", "r");
$teamNumber = fread($file,filesize("teamNumber.txt"));
fclose($file);
} else {
	$teamNumber = "ERROR";
}

if (filesize("pitScout.csv")>0) {
$file = fopen("pitScout.csv","r");
$raw = explode(",",fread ($file,filesize("pitScout.csv")));
$autonomousPit = $raw[4];
$notesPit = $raw[3];
$lowPit = $raw[5];
$highPit = $raw[6];
$climbPit = $raw[7];
$gearsPit = $raw[8];
$teleopPit = $raw[9];
fclose($file);
} else {
	$autonomousPit = "Unknown";
	$notesPit = "Unknown";
	$lowPit = "Unknown";
	$highPit = "Unknown";
	$climbPit = "Unknown";
	$gearsPit = "Unknown";
	$teleopPit = "Unknown";
}

if (filesize("rawData.csv")>0) {
	$file = fopen("rawData.csv","r");
	$rawLine = explode("\n",fread ($file,filesize("rawData.csv")));
	$Gears = 0;
	$Low = 0;
	$High = 0;
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
					$Gears += $break[$i];
			}
		}
	}
	$Gears = $Gears/(count($rawLine)-1);
	$Low = $Low/(count($rawLine)-1);
	$High = $High/(count($rawLine)-1);
	fclose($file);
} else {
	$Gears = "Unknown";
	$Low = "Unknown";
	$High = "Unknown";
}
?>

<head><title><?php echo $teamNumber ?> - ORF Scouting</title>
<style>
table, th, td {
    border: 1px solid white;
}
table.center {
	margin-left:auto;
	margin-right:auto;
	width: 65%;
}
p, h1, h3, td, th {
	color: white;
}
body {
	background-color: black;
}
</style>
<script>
function returnHome() {
	var url = window.location.href;
	var broken = url.split("/");
	var newUrl = broken[0];
	console.log(broken[0]);
	for (var i = 2; i < broken.length-1; i++) {
		newUrl = newUrl.concat("/",broken[i-1]);
	}
	newUrl = newUrl.concat("/index.php");
	window.location.href = newUrl;
}
</script></head>
<body>
<h1 style="text-align:center"><?php echo $teamNumber ?></h1>
<img src="picture.png" style="display: block;margin: 0 auto; border: 1px solid white;"/>
<h3 style="text-align:center">Quick Facts:</h3>
<table class="center">
<tr><td>Team Number:</td><td><?php echo $teamNumber ?></td></tr>
<tr><td>Autonomous:</td><td><?php echo $autonomousPit ?></td></tr>
<tr><td>Teleoperated:</td><td><?php echo $teleopPit ?></td></tr>
<tr><td>General Notes:</td><td><?php echo $notesPit ?></td></tr>
<tr><td>Low Goal visits per match:</td><td>Pit: <?php echo $lowPit ?></td><td> Average: <?php echo $Low ?></td></tr>
<tr><td>High Goal visits per match:</td><td>Pit: <?php echo $highPit ?></td><td> Average: <?php echo $High ?></td></tr>
<tr><td>Gears per match</td><td>Pit: <?php echo $gearsPit ?></td><td> Average: <?php echo $Gears ?></td></tr>
</table>
<p></p>
<h3 style="text-align:center">Raw Data</h3>
<table class="center">
<tr><th>Team Number</th><th>Scouter Name</th><th>Match Number</th><th>Low Goal Visits</th><th>High Goal Visits</th><th>Gears Delivered</th><th>Climb</th><th>Dead On Field</th><th>Autonomous Notes</th><th>Teleoperated Notes</th><th>General Notes</th></tr>
<?php
if (filesize("rawdata.csv") > 0) {
	$rawFile = fopen("rawData.csv","r");
	$rawData = explode("\n",fread($rawFile,filesize("rawData.csv")));
	foreach ($rawData as $dataLine) {
		if ($dataLine == "") continue;
		$dataArray = explode(",",$dataLine);
		echo "<tr><td>".$dataArray[2]."</td><td>".$dataArray[1]."</td><td>".$dataArray[3]."</td><td>".$dataArray[4]."</td><td>".$dataArray[5]."</td><td>".$dataArray[6]."</td><td>".$dataArray[11]."</td><td>".$dataArray[10]."</td><td>".$dataArray[8]."</td><td>".$dataArray[9]."</td><td>".$dataArray[7]."</td></tr>\n";
	}
	fclose($rawFile);
} ?>
</table>
<p></p>
<div style="text-align:center"><input type="button" onclick="returnHome()" value="Go Back"></div>
</body>