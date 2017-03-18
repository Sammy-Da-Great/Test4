<?php
$error = "";
$default = "";
if (isSet($_GET["team"])) {
	$default = $_GET["team"];
	$directories = glob('*' , GLOB_ONLYDIR);
	$teams = array();
	foreach($directories as $fileName) {
		if ($fileName != "template" && $fileName != "js" && $fileName != "sortableTable"){
			array_push($teams, $fileName);
		}
	}
	foreach ($teams as $team) {
		if ($_GET["team"] == $team) {
			header("Location: ./".$team."/noalliance.php",true);
		}
	}
	$error = "Team number not found! They may not have been scouted yet!";
}
?>

<head>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<link rel="icon" href="favicon.ico" type="image/x-icon">
<title>No Alliance Searcher - ORF Scouter</title>
<style>
body {
	background-color: black;
}
img {
    height: 70%;
    width: 80%;
}
p, h1, h2, h3 {
	color: white;
}

div {
    width: 50%;
    height: 70%;
	border: 1px solid white;
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
	document.getElementById("team").defaultValue = "<?php echo $default; ?>";
}
</script></head><body onload="onLoad()">
<div>
	<img src="template/picture.png"/>
	<form method="get">
	<p>Team Number: (Shows only matches marked as "No Alliance")</p>
	<p><input id="team" name="team" type="number"></input></p>
	<p><input type="submit"></input></p>
	</form>
	<p><?php echo $error ?></p>
</div>