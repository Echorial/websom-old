<?php
//Global websom resources\\ TODO: Fix this so that it will grab the document root with different server versions
$docRoot = $_SERVER['DOCUMENT_ROOT'];
$docChars = str_split($docRoot);
if ($docChars[count($docChars)-1] == '/'){
	$docRoot = substr($docRoot, 0, count($docChars)-1);
}
define("Websom_root", $docRoot.'/Websom');
define("Document_root", $docRoot);
define("Document_root_local", '/');
define("Host", 'http://www.'.$_SERVER['HTTP_HOST']);
define("Website_name", "Websom_Website");

//Start session\\ 
session_start();

//For stopping the page echo\\
$DoNotRender = false;

function Cancel($Html = ''){
	global $DoNotRender;
	$DoNotRender = $Html;
}

function Render(){
	global $DoNotRender;
	return $DoNotRender;
}

//For formating errors\\ 
function Error($type, $msg, $fatal = false){
	$msg = '['.$type.' Error]: '.$msg;
	
	if ($fatal){
		echo 'Som ting wong '.$msg;
		die();
	}else{
		return $msg;
	}
}

//TODO: Move these to a sperate file, like Websom_Functions.php
function include_all($Path){
	$Dir = new DirectoryIterator($Path);
	$Names = array();
	foreach ($Dir as $File) {
		if (!$File->isDot()) {
			include($Path.$File->getFilename());
			array_push($Names, basename($File->getFilename(), '.php'));
		}
	}
	return $Names;
}

function CallFunction($Name, $Params = array()){
	if (function_exists($Name)){
		return call_user_func($Name, $Params);
	}else{
		return false;
	}
}


function CallFunctionArgs($Name, $Args = array()){
	if (function_exists($Name)) {
		return call_user_func_array($Name, $Args);
	}else{
		return false;
	}
}

function Wait($time) {
	sleep($time);
}

function Send_Mail($to, $subject, $body, $extraHeaders = '') {
$headers = "From: " . $NewsLetter['sender'] . "\r\n";
$headers .= "Reply-To: ". $NewsLetter['sender'] . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
$headers .= $extraHeaders;
mail($to, $subject, $body, $headers);
}

$onEvents = array();
function onEvent($name, callable $callback) {
	global $onEvents;
	if (!isset($onEvents[$name])) {
		$onEvents[$name] = array($callback);
	}else{
		array_push($onEvents[$name], $callback);
	}
}

function callEvent($name, $args = array()) {
	global $onEvents;
	if (isset($onEvents[$name]))
		foreach($onEvents[$name] as $event)
			$event($args);
}



include("Responsive.php");

include('process.php');

include(Websom_root."/Generic/Input/Input.php");

include("Data_Form.php");

include("Websom_Run_Modules.php");

include("Widget_Run.php");

include("Control_Run.php");
?>