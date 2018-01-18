<?php
function logToFile($stringToLog) {
	$logFile = fopen("debug.log","a");
	$date = getDate();
	fwrite($logFile, "["$date["hours"].":"$date["minutes"].":".$date["seconds"]." ".$date["month"]." ".$date["mday"].", ".$date["year"]."]: ".$stringToLog."\n");
	fclose($logFile);
}
?>