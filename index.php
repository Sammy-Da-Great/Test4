<?php
$error = "";
$default = "";
if (isSet($_GET["team"])) {
	$default = $_GET["team"];
	$directories = glob('*' , GLOB_ONLYDIR);
	$teams = array();
	foreach($directories as $fileName) {
		if ($fileName != "template"){
			array_push($teams, $fileName);
		}
	}
	foreach ($teams as $team) {
		echo $team;
		if ($_GET["team"] == $team) {
			header("Location: ./".$team,true);
		}
	}
	$error = "Team number not found! They may not have been scouted yet!";
}
?>

<head><title>ORF Scouter</title>
<style>
body {
	background-color: black;
}

p, h1, h2, h3 {
	color: white;
}

div {
    width: 100px;
    height: 100px;

    position: absolute;
    top:0;
    bottom: 0;
    left: 0;
    right: 0;

    margin: auto;
}
</style><script>
function onLoad() {
	document.getElementById("team").defaultValue = "<?php echo $default; ?>";
}
</script></head><body onload="onLoad()">
<div>
	<form method="get">
	<p>Team Number:</p>
	<p><input id="team" name="team" type="number"></input></p>
	<p><input type="submit"></input></p>
	</form>
	<p><?php echo $error ?></p>
</div>