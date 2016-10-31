<?php

//TODO: Rewrite to support external file links\\

//Create the directory iterator to include the javascript libs/dependicies\\
$Javascripts = new DirectoryIterator(Document_root."/Javascript");

$Include = "<script src='".Document_root_local."Javascript/Jquery.js'></script>";

foreach ($Javascripts as $Javascript) {
	if ($Javascript->getFilename() != 'Jquery.js')
		if ($Javascript->isFile()) {
			$Include .= "<script src='".Document_root_local."Javascript/".$Javascript->getFilename()."'></script>";
		}else if ($Javascript->isDir() AND !$Javascript->isDot()){
			$Include .= "<script src='".Document_root_local."Javascript/".$Javascript->getBasename()."/index.js'></script>";
		}
}
SetPropertie("Javascript", $Include);
?>