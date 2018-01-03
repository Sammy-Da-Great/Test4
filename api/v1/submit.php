<?php 

$expectedFormInputCommon = array(
	"App",
	"Version",
	"ScouterName",
	"EventKey",
	"TeamNumber",
	"LowGoalFuel",
	"HighGoalFuel",
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
	//Insert Pit only form inputs here
);

	if (isSet($_POST["App"])) {
		$dataArray = array();
		foreach($expectedFormInputCommon as $input) {
			if (isSet($_POST[$input])) {
				if (seralizeString($_POST[$input]) !== false) {
					$dataArray[$input] = seralizeString($_POST[$input]);
				} else {
					http_response_code(400);
					exit;
				}
			} else {
				http_response_code(400);
				exit;
			}
		}

		include_once "../../config.php";

		$url = 'http://www.thebluealliance.com/api/v3/team/frc'.$_POST["TeamNumber"].'/event/'.$_POST["EventKey"].'/status';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-TBA-Auth-Key: '.$TBAAuthKey));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($httpCode != 200) {
			echo " Error: ".$httpCode." URL: ".$url;
			http_response_code(400);
			exit;
		}

		if ($_POST["App"] == "stand") {
			foreach($expectedFormInputStand as $input) {
				if (isSet($_POST[$input])) {
					if (seralizeString($_POST[$input]) !== false) {
						$dataArray[$input] = seralizeString($_POST[$input]);
					} else {
						http_response_code(400);
						exit;
					}
				} else {
					http_response_code(400);
					exit;
				}
			}
		} elseif ($_POST["App"] == "pit") {
			foreach($expectedFormInputPit as $input) {
				if (isSet($_POST[$input])) {
					if (seralizeString($_POST[$input]) !== false) {
						$dataArray[$input] = seralizeString($_POST[$input]);
					} else {
						http_response_code(400);
						exit;
					}
				} else {
					http_response_code(400);
					exit;
				}
			}
		} else {
			http_response_code(400);
			exit;
		}

		$lineToAppend = json_encode($dataArray);
		echo $lineToAppend;
		
		$teamPath = getOrCreateTeamFolder($_POST["TeamNumber"], $_POST["EventKey"]);
		echo $teamPath;
		if ($_POST["App"] == "stand") {
			$dataFile = fopen($teamPath."rawData.json","a");
		} else {
			$dataFile = fopen($teamPath."pitScout.json","w");
		}
		fwrite($dataFile, $lineToAppend."\n");
		fclose($dataFile);

	} else {
		http_response_code(400);
		exit;
	}
	
	function getOrCreateTeamFolder($teamNumber, $eventCode) {
		$path = $eventCode."/".$teamNumber;
		if (!file_exists($path)) {
			if (mkdir($path,0777,true)) {
				$filesToMake = array("rawData.csv","pitScout.csv");
				foreach($filesToMake as $file) {
					$newFile = fopen($path."/".$file,"w+");
					fclose($newFile);
				}
			} else {
				http_response_code(400);
			}
		}
		return $path."/";
	}

	function seralizeString($stringToValidate) {
		$newString = preg_replace('~(\\ | \n)~',' ', preg_replace('/\"/', '\'', $stringToValidate)); //Changes double quotes to single, and removes escaping characters.
		echo $newString;
		if ($newString != null) {
			return $newString;
		}
		return false;
	}
?>