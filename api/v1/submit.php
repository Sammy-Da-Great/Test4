<?php 

$expectedFormInputCommon = array(
	//Format array("KEY","TYPE OF KEY"),
	array("App","string"),
	array("Version","string"),
	array("ScouterName","string"),
	array("EventKey","string"),
	array("TeamNumber","integer"),
	array("LowGoalFuel","integer"),
	array("HighGoalFuel","integer"),
	array("GearsDelivered","integer"),
	array("Notes","string"),
	array("AutoNotes","string"),
	array("TeleopNotes","string"),
	array("Climb","string"),
	array("GearsPickup","integer"),
	array("FuelDrive","boolean"),
	array("Defended","boolean"),
);

$expectedFormInputStand = array(
	array("MatchNumber","integer"),
	array("DOF","boolean"),
	array("NoAlliance","boolean"),
);

$expectedFormInputPit = array(
	//Insert Pit only form inputs here
);

	if (isSet($_POST["App"])) {
		$dataArray = array();
		foreach($expectedFormInputCommon as $input) {
			if (isSet($_POST[$input[0]])) {
				if (gettype($_POST[$input[0]]) == $input[1]) {
					$dataArray[$input[0]] = ($input[1] == "string") ? seralizeString($_POST[$input[0]]) : $_POST[$input[0]];
				} else {
					http_response_code(400);
					exit;
				}
			} else {
				http_response_code(400);
				exit;
			}
		}

		$url = 'http://www.thebluealliance.com/api/v3/team/frc'.$_POST["TeamNumber"].'/event/'.$_POST["EventKey"].'/status';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-TBA-Auth-Key: '.$TBAAuthKey));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($httpCode != 200) {
			http_response_code(400);
			exit;
		}

		if ($_POST["App"] == "stand") {
			foreach($expectedFormInputStand as $input) {
				if (isSet($_POST[$input])) {
					if (gettype($_POST[$input[0]]) == $input[1]) {
						$dataArray[$input[0]] = ($input[1] == "string") ? seralizeString($_POST[$input[0]]) : $_POST[$input[0]];
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
					if (gettype($_POST[$input[0]]) == $input[1]) {
						$dataArray[$input[0]] = ($input[1] == "string") ? seralizeString($_POST[$input[0]]) : $_POST[$input[0]];
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
		$newString = preg_replace("/\\/","", preg_replace("/\"/","'", $stringToValidate)); //Changes double quotes to single, and removes escaping characters.
	}
?>