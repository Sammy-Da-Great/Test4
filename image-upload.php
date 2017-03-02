<?php
// Make sure we have a file name
if(!isset($_GET["filename"])){
	http_response_code(400);
	exit();
}
$filename = strtoupper($_GET["filename"]);
// Ensure the image and temporary directories are created
$image_dir = "uploaded/images/";
$tmp_dir = $image_dir . "tmp/";
if (!file_exists($image_dir)) {
	mkdir($image_dir, 0777, true);
}
if(!file_exists($tmp_dir)){
	mkdir($tmp_dir, 0777, true);
}
// Read image from input
$data = file_get_contents("php://input");
// No data? No good!
if (!$data) {
	http_response_code(400);
	exit();
}
// Write the file to the temporary directory
$file_dir = $image_dir . $filename;
$temp_file_dir = $tmp_dir . $filename;
$fhandle = fopen($temp_file_dir, "wb");
fwrite($fhandle, $data);
fclose($fhandle);
// Validate the file's name and content
$file_name_no_ext = pathinfo($temp_file_dir, PATHINFO_FILENAME);
if (!preg_match("/ROBOT_\\d+$/", $file_name_no_ext) || !exif_imagetype($temp_file_dir)) {
	unlink($temp_file_dir);
	http_response_code(400);
	exit();
}
// If the file IS an image, and IS properly named, move it into the image directory
$file_dir = $image_dir . $file_name_no_ext . ".png";
rename($temp_file_dir, $file_dir);
http_response_code(200);
