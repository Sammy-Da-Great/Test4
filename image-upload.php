<?php

if (isSet($_POST["TeamNumber"]) && isSet($_POST["photo"])) {
	$teamPath = getOrCreateTeamFolder($_POST["TeamNumber"]);
	$photo = fopen($teamPath."/photo.txt","w");
	fwrite($photo, data_uri($_POST["photo"]));
	echo $_POST["photo"];
	fclose($photo);
}

function data_uri($contents) 
{  
  $base64   = base64_encode($contents); 
  return ('data:image/png;base64,' . $base64);
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