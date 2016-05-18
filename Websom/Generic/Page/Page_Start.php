<?php
$Properties;

//Set the default properties\\
$Properties['Title'] = "Untitled Websom Page";
$Properties['TemplatePage'] = "Default.html";
$Properties['Body'] = "";
$Properties['Javascript'] = "";

function SetPropertie($p, $v){
	global $Properties;
	$Properties[$p] = $v;
}

function GetPropertie($p){
	global $Properties;
	$Properties[$p];
}

function GetProperties(){
	global $Properties;
	return $Properties;
}

function Title($n){
	global $Properties;
	$Properties['Title'] = $n;
	return true;
}

function Page($f){
	global $Properties;
	$Properties['TemplatePage'] = $f;
	return true;
}


include(Websom_root."/Generic/Core/Page_Functions.php");

include(Websom_root."/Generic/Core/Page_Javascript_Loader.php");

include(Websom_root."/Generic/Core/Page_Css_Loader.php");

ob_start();
?>