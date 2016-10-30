<?php
//Create the directory iterator to include the javascript libs/dependicies\\
$Css = new DirectoryIterator(Document_root."/Css");

$Include = "";
foreach ($Css as $Sheet) {
	if (!$Sheet->isDot()) {
		$Include .= "<link rel='stylesheet' type='text/css' href='".Document_root_local."Css/".$Sheet->getFilename()."'/>";
	}
}
SetPropertie("Css", $Include);
?>