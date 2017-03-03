<?php 
	if (isSet($_POST["App"])) {
		if ($_POST["App"] == "stand") {
			//echo "Welcome to stand scouting!";
			if (isSet($_POST["Version"]) && isSet($_POST["ScouterName"]) && isSet($_POST["TeamNumber"])&& isSet($_POST["MatchNumber"]) && isSet($_POST["LowGoalFuel"]) && isSet($_POST["HighGoalFuel"]) && isSet($_POST["GearsDelivered"]) && isSet($_POST["Notes"]) && isSet($_POST["AutoNotes"]) && isSet($_POST["TeleopNotes"]) && isSet($_POST["DOF"]) && isSet($_POST["Climb"])) {
				$lineToAppend = $_POST["Version"].",".$_POST["ScouterName"].",".$_POST["TeamNumber"].",".$_POST["MatchNumber"].",".$_POST["LowGoalFuel"].",".$_POST["HighGoalFuel"].",".$_POST["GearsDelivered"].",".$_POST["Notes"].",".$_POST["AutoNotes"].",".$_POST["TeleopNotes"].",".$_POST["DOF"].",".$_POST["Climb"];
				echo $lineToAppend;
				$standData = fopen("StandData.csv","a+");
				if (!is_writable("StandData.csv")) {
					http_response_code(500);
				}
				fwrite($standData, $lineToAppend."\n");
				fclose($standData);
				
				$teamPath = getOrCreateTeamFolder($_POST["TeamNumber"]);
				$rawData = fopen($teamPath."rawData.csv","a");
				fwrite($rawData, $lineToAppend."\n");
				fclose($rawData);
			}
			else {
				echo $_POST["Version"].",".$_POST["ScouterName"].",".$_POST["TeamNumber"].",".$_POST["MatchNumber"].",".$_POST["LowGoalFuel"].",".$_POST["HighGoalFuel"].",".$_POST["GearsDelivered"].",".$_POST["Notes"].",".$_POST["AutoNotes"].",".$_POST["TeleopNotes"].",".$_POST["DOF"];
				http_response_code(400);
			}
		} elseif ($_POST["App"] == "pit") {
			//echo "Welcome to pit scouting!";
			if (isSet($_POST["Version"]) && isSet($_POST["ScouterName"]) && isSet($_POST["TeamNumber"])&& isSet($_POST["LowGoalFuel"]) && isSet($_POST["HighGoalFuel"]) && isSet($_POST["GearsDelivered"]) && isSet($_POST["Notes"]) && isSet($_POST["AutoNotes"]) && isSet($_POST["TeleopNotes"]) && isSet($_POST["Climb"])) {
				$lineToAppend = $_POST["Version"].",".$_POST["ScouterName"].",".$_POST["TeamNumber"].",".$_POST["LowGoalFuel"].",".$_POST["HighGoalFuel"].",".$_POST["GearsDelivered"].",".$_POST["Notes"].",".$_POST["AutoNotes"].",".$_POST["TeleopNotes"].",".$_POST["Climb"];
				echo $lineToAppend;
				$pitData = fopen("PitData.csv","a+");
				if (!is_writable("PitData.csv")) {
					http_response_code(500);
				}
				fwrite($pitData, $lineToAppend."\n");
				fclose($pitData);
				
				$teamPath = getOrCreateTeamFolder($_POST["TeamNumber"]);
				$rawData = fopen($teamPath."pitScout.csv","w");
				fwrite($rawData, $lineToAppend);
				fclose($rawData);
			} else {
				echo $_POST["Version"].",".$_POST["ScouterName"].",".$_POST["TeamNumber"].",".$_POST["MatchNumber"].",".$_POST["LowGoalFuel"].",".$_POST["HighGoalFuel"].",".$_POST["GearsDelivered"].",".$_POST["Notes"].",".$_POST["AutoNotes"].",".$_POST["TeleopNotes"].",".$_POST["Climb"];
				http_response_code(400);
			}
		} else {
			echo "<p><strong>Error: App is not valid!</strong></p>";
			http_response_code(400);
		}
	} else {
		echo "<p><strong>POST is empty!</strong></p>";
		http_response_code(400);
	}
	
	function getOrCreateTeamFolder($teamNumber) {
		if (!file_exists($teamNumber."/index.php")) {
			if (mkdir($teamNumber)) {
				$filesToCopy = array("index.php","picture.png","rawData.csv","teamNumber.txt","pitScout.csv");
				foreach($filesToCopy as $file) {
					copy("template/".$file,$teamNumber."/".$file);
				}
				$teamNumberTxt = fopen($teamNumber."/teamNumber.txt","w");
				fwrite($teamNumberTxt,$teamNumber);
				fclose($teamNumberTxt);
			} else {
				http_response_code(400);
			}
		}
		return $teamNumber."/";
	}
?>