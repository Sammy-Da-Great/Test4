<?php
if (!isSet($error)) {
	$error = "";
}
$teamNumber = "";
$events = array();

if (isSet($_GET["team"]) && $error == "") {
	$teamNumber = $_GET["team"];
	$eventDirectories = glob('api/v1/*' , GLOB_ONLYDIR);
	foreach($eventDirectories as $fileName) {
		$teamDirectories = glob($fileName."/*", GLOB_ONLYDIR);
		foreach($teamDirectories as $teamFolder) {
			if (explode("/",$teamFolder)[3] == $teamNumber) {
				array_push($events, $teamFolder);
			}
		}
	}
	if (count($events) == 0) {
	$error = "Team number not found! They may not have been scouted yet!";
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
	<?php if ($error == "" && $teamNumber != "") {
	echo "document.getElementById(\"team\").defaultValue = ".$teamNumber.";";
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
	<p>Team Number:</p>
	<p><input id=\"team\" name=\"team\" type=\"number\"></input></p>
	<p><input type=\"submit\"></input></p>
	</form>";
	}
	?>
	
	<p><?php echo $error ?></p>
	<?php
	if (count($events) > 0 && $error == "") {
		echo "<p>Team ".$teamNumber." has been scouted at these event codes:</p>";
		foreach($events as $event) {
			$eventCode = explode("/",$event)[2];
			echo "<p><button style='font-size: 30;' onClick='window.location.href=\"viewTeam.php?eventCode=".$eventCode."&teamNumber=".$teamNumber."\"'>".$eventCode."</button></p>";
		}
		
		echo "<br/><p><button onClick='window.location.href=\"index.php\"'>Go Back</button>";
	}
	?>
</div>