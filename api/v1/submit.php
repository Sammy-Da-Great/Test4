<?php 
include_once "../../util.php";
logToFile("Submit request");
$expectedFormInputCommon = array(
	"App",
	"Version",
	"ScouterName",
	"EventKey",
	"TeamNumber",
	"LowGoalVisits",
	"HighGoalVisits",
	"GearsDelivered",
	"Notes",
	"AutoNotes",
	"TeleopNotes",
	"Climb",
	"GearsPickup",
	"FuelDrive",
	"Defended",
	"NoAlliance",
);

$expectedFormInputStand = array(
	"MatchNumber",
	"DOF",
);

$expectedFormInputPit = array(
	//Insert Pit only form fields here
);
logToFile("Variables defined");
if (isSet($_POST["App"])) {
	logToFile ("App is set");
	$dataArray = array();
	foreach($expectedFormInputCommon as $input) {
		if (isSet($_POST[$input])) {
			if (seralizeString($_POST[$input]) !== false) {
				$dataArray[$input] = seralizeString($_POST[$input]);
			} else {
				logToFile("Seralize Failed for ".$input);
				http_response_code(400);
				exit;
				}
		} else {
			logToFile("POST not set for ".$input);
			http_response_code(400);
			exit;
		}
}
		include_once "../../config.php";
	logToFile ("Config Included");
	
	$url = 'http://www.thebluealliance.com/api/v3/team/frc'.$_POST["TeamNumber"].'/event/'.$_POST["EventKey"].'/status';
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-TBA-Auth-Key: '.$TBAAuthKey));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	logToFile ("Request Complete Http Code:".$httpCode." url: ".$url);
	if ($httpCode != 200) {
		logToFile("Error: ".$httpCode." URL: ".$url);
		http_response_code(400);
		exit;
	}
		if ($_POST["App"] == "stand") {
		logToFile ("App is stand");
		foreach($expectedFormInputStand as $input) {
			if (isSet($_POST[$input])) {
				if (seralizeString($_POST[$input]) !== false) {
					$dataArray[$input] = seralizeString($_POST[$input]);
			} else {
				logToFile("Seralize Failed for ".$input);
				http_response_code(400);
				exit;
				}
		} else {
			logToFile("POST not set for ".$input);
			http_response_code(400);
			exit;
		}
		}
	} elseif ($_POST["App"] == "pit") {
		logToFile ("App is pit");
		foreach($expectedFormInputPit as $input) {
			if (isSet($_POST[$input])) {
				if (seralizeString($_POST[$input]) !== false) {
					$dataArray[$input] = seralizeString($_POST[$input]);
			} else {
				logToFile("Seralize Failed for ".$input);
				http_response_code(400);
				exit;
				}
		} else {
			logToFile("POST not set for ".$input);
			http_response_code(400);
			exit;
		}
		}
	} else {
		logToFile("App not valid");
		http_response_code(400);
		exit;
	}
		$lineToAppend = json_encode($dataArray);
	echo $lineToAppend;
	logToFile ("LineToAppend: ".$lineToAppend);
	
	$teamPath = getOrCreateTeamFolder($_POST["TeamNumber"], $_POST["EventKey"]);
	logToFile ($teamPath);
	if ($_POST["App"] == "stand") {
		$dataFile = fopen($teamPath."rawData.json","a");
	} else {
		$dataFile = fopen($teamPath."pitScout.json","w");
	}
	fwrite($dataFile, $lineToAppend."\n");
	fclose($dataFile);
	logToFile ("Data written");
	} else {
		logToFile("App not set");
		http_response_code(400);
	exit;
}

function getOrCreateTeamFolder($teamNumber, $eventCode) {
	$path = $eventCode."/".$teamNumber;
	if (!file_exists($path)) {
		if (mkdir($path,0777,true)) {
			$filesToMake = array("rawData.json","pitScout.json");
			foreach($filesToMake as $file) {
				$newFile = fopen($path."/".$file,"w+");
				fclose($newFile);
			}
		} else {
			logToFile("Error making team folder");
			http_response_code(400);
		}
	}
	return $path."/";
}

function seralizeString($stringToValidate) {
	$newString = addslashes($stringToValidate); //Escape dangerous characters.
	if ($newString != null) {
		return $newString;
	}
	return false;
}
?>