<?php 

$matchingFiles = [];
$folder = './'.$_GET['folder'];
$wordToSearch = $_GET['word'];
// Check if the folder exists and is readable
if (is_dir($folder) && is_readable($folder)) {
	// Get all files and directories in the folder
	// Use glob to get files that contain the specific word
	$files = glob($folder . "/*$wordToSearch*");

	foreach ($files as $file) {
		// Check if it's a file (not a directory)
		if (is_file($file)) {
			$matchingFiles[] =  basename($file);
		}
	}
} else {
	echo "The folder does not exist or is not readable.";
}

echo json_encode( $matchingFiles);