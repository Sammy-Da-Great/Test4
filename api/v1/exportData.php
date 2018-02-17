<?php

if (!isSet($_GET["exportType"])) {
	http_response_code(400);
	exit;
}

$zipDir = __DIR__."/CacheZip/";

switch($_GET["exportType"]) {
	case "eventData":
		if (!isSet($_GET["eventKey"])) {
			http_response_code(400);
			exit;
		}
		
		$pathToEvent = __DIR__."/".$_GET["eventKey"];
		if (file_exists($pathToEvent)) {
			$zip = new ZipArchive;
			$zip->open($zipDir.$_GET["eventKey"].".zip", ZipArchive::OVERWRITE);
			$files = scandir($pathToEvent);
			foreach($files as $filePart) {
				if (file_exists($pathToEvent."/".$filePart) && ($filePart != "." || $filePart != "..")) {
					$pitData = json_decode(file($pathToEvent."/".$filePart."/pitScout.json"), true);
					$standDataRaw = file($pathToEvent."/".$filePart."/standScout.json");
					$standData = array();
					foreach ($standDataRaw as $standRaw) {
						$standData[] = json_decode($standRaw, true);
					}
					
					$pitLine = "Scout's Name,Scout's Team Number,Event Key,Team Number,Starting Position,Autonomous: Crossed Baseline,Autonomous: Notes,Autonomous: Placed Cube on Switch,Autonomous: Placed Cube on Scale,Teleop: Placed Cube on Switch,Teleop: Placed Cube on Scale,Teleop: Number of Exchange Visits,Teleop: Climb,Teleop: Notes,Robot Notes,Strategy for Power Ups,Strategy in General".PHP_EOL;
					$pitLine .= $pitData["ScouterName"].",".$pitData["ScouterTeamNumber"].",".$pitData["EventKey"].",".$pitData["TeamNumber"].",".$pitData["Pre_StartingPos"].",".$pitData["Auto_CrossedBaseline"].",".$pitData["Auto_Notes"].",".$pitData["Auto_PlaceSwitch"].",".$pitData["Auto_PlaceScale"].",".$pitData["Teleop_PlaceSwitch"].",".$pitData["Teleop_PlaceScale"].",".$pitData["Teleop_ExchangeVisit"].",".$pitData["Teleop_Climb"].",".$pitData["Teleop_Notes"].",".$pitData["RobotNotes"].",".$pitData["Strategy_PowerUp"].",".$pitData["Strategy_General"].PHP_EOL;
					$zip->addFromString($filePart."/pitData.csv",$pitLine);
					
					$standLink = "Scout's Name,Scout's Team Number,Event Key,Team Number,Match Number,Starting Position,Autonomous: Crossed Baseline,Autonomous: Notes,Autonomous: Placed Cube on Switch,Autonomous: Placed Cube on Scale,Teleop: Placed Cube on Switch,Teleop: Placed Cube on Scale,Teleop: Number of Exchange Visits,Teleop: Climb,Teleop: Notes,Teleop: Boost Used,Teleop: Force Used,Teleop: Levitate Used,Climb,General Notes,No Show,Died on Field".PHP_EOL;
					foreach ($standData as $singleStandData) {
						$standLine .= $singleStandData["ScouterName"].",".$singleStandData["ScouterTeamNumber"].",".$singleStandData["EventKey"].",".$singleStandData["TeamNumber"].",".",".$singleStandData["MatchNumber"].",".$singleStandData["Pre_StartingPos"].",".$singleStandData["Auto_CrossedBaseline"].",".$singleStandData["Auto_Notes"].",".$singleStandData["Auto_PlaceSwitch"].",".$singleStandData["Auto_PlaceScale"].",".$singleStandData["Teleop_PlaceSwitch"].",".$singleStandData["Teleop_PlaceScale"].",".$singleStandData["Teleop_ExchangeVisit"].",".$singleStandData["Teleop_Climb"].",".$singleStandData["Teleop_Notes"].",".$singleStandData["Teleop_BoostUsed"].",".$singleStandData["Teleop_ForceUsed"].",".$singleStandData["Teleop_LevitateUsed"].",".$singleStandData["Post_Climb"].",".$singleStandData["Notes"].",".$singleStandData["Pre_NoShow"].",".$singleStandData["DOF"].PHP_EOL;
					}
					
					$zip->addFromString($filePart."/standData.csv",$standLine);
					$zip->close();
					
					header('Content-Type: application/zip');
					header('Content-disposition: attachment; filename='.$_GET["eventKey"].'.zip');
					header('Content-Length: ' . filesize($zipDir.$_GET["eventKey"].".zip"));
					readfile($zipDir.$_GET["eventKey"].".zip");
				}
			}
		}
		break;
		
	case "teamData":
		if (!isSet($_GET["teamNumber"])) {
			http_response_code(400);
			exit;
		}
		break;
		
	case "teamAtEventData":
		if (!isSet($_GET["teamNumber"]) || !isSet($_GET["eventKey"])) {
			http_response_code(400);
			exit;
		}
		break;
	
	case "allData":
		break;
		
	default:
		http_response_code(400);
		exit;
}
?>