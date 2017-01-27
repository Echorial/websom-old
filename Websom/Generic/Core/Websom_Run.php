<?php
//Global websom resources\\ TODO: Fix this so that it will grab the document root with different server versions
$docRoot = $_SERVER['DOCUMENT_ROOT'];
$docChars = str_split($docRoot);
if ($docChars[count($docChars)-1] == '/'){
	$docRoot = substr($docRoot, 0, count($docChars)-1);
}



/**
* \defgroup Globals Globals
* These globals can be used all throughout websom
* <hr>
* Globals:
* 	- Websom_root: The root working folder of websom.
* 	- Document_root: The root public folder.
* 	- Local_root: The root directory using ./. This is set by the page.
* 	- Document_root_local: /
* 	- Host: The complete website url.
* 	- Website_name: The name of the website.
* 	- Modules_root: The modules directory.
*
*/


if (!defined("Local_root"))
	define("Local_root", "");

define("Websom_root", $docRoot.'/Websom');
define("Document_root", $docRoot);
define("Document_root_local", '/');
define("Host", 'http://www.'.$_SERVER['HTTP_HOST']);

define("Modules_root", Websom_root.'/Website/Modules');

/**
* \defgroup Helper Helpers
* 
* Currently Websom only provides 1 generic helper, but with time more will be added.
* 
* 
*/
//Helper classes
include("String.php");


include("Session.php");

include("Hookable.php");
include("Config.php");
include("Email.php");

Email::init();

function Websom_Reload_Config() {
	//Load Websom Config
	

	$Websom_Config = Config::Get('Websom',
";Websom Config File

live = false

;Set these to override their value
;---------------------------------
;Websom_root = SomePath
;Document_root = SomePath
;Host = http://www.example.com

;Website Details
;---------------
Website_name = DefaultName
;Version
Website_minor = 0
Website_major = 0

;Hepler settings
;--------------

;Str helper
Use_MultiByte_String = \"no\"

;End");

	if (isset($Websom_Config['Document_root'])) {
		define("Document_root", $Websom_Config['Document_root']);
	}

	if (isset($Websom_Config['Websom_root'])) {
		define("Websom_root", $Websom_Config['Websom_root']);
	}

	if (isset($Websom_Config['Host'])) {
		define("Host", $Websom_Config['Host']);
	}
	
	return $Websom_Config;
}

/**
* \ingroup Globals
* The static global Websom class contains information about the website and websom.
* 
* 
*/
class Websom {
	/**
	* This is the parsed Websom config file structured in a key/value array.
	*/
	public static $Config;
	
	/**
	* An array of modules that are loaded.
	*/
	public static $Modules;
	
	/**
	* The websom version.
	*/
	public static $Version;
	
	/**
	* If the website is live. True or false.
	*/
	public static $Live;
	
	/**
	* Returns an array like this(["status" => true or false, "info" => the module info array]) if the Module was found or false if not.
	*/
	public static function Module($name) {
		if (isset(Websom::$Modules[$name])) return Websom::$Modules[$name];
		return false;
	}
}

Websom::$Modules = [];
Websom::$Config = Websom_Reload_Config();
Str::init();
Websom::$Version = '1.5';
Websom::$Live = (Websom::$Config['live'] == "yes" ? true : false);

//Session start moved to the Session core.

//For stopping the page echo\\
$DoNotRender = false;
$_IncludeResources = false;


/**
* \ingroup PageFunctions
* This function will, rather than echo the normal page contents echo out the `$Html` param.
*
* <div class="warning">If the page is Canceled then the structuring, css and js will not be included. This means no Canceling forms or other js dependant features.</div>
* 
*/
function Cancel($Html = '', $keepResources = false){
	global $DoNotRender, $_IncludeResources;
	$DoNotRender = $Html;
	$_IncludeResources = $keepResources;
}

function IncludeResources() {
	global $_IncludeResources;
	return $_IncludeResources;
}

function Render(){
	global $DoNotRender;
	return $DoNotRender;
}

/**
* \ingroup PageFunctions
* This function will format an error.
*
* <div class="warning">If the error is fatal it will display the error and die.</div>
* 
*/
function Error($type, $msg, $fatal = false){
	$msg = '['.$type.' Error]: '.$msg;
	
	if ($fatal){
		throw new Exception("Fatal error ".$msg);
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

/**
* \defgroup Event Event
* Use events to handle, well events.
* <hr>
* Global events:
* 	- ready:  called when most functions/tools are ready to use.
* 	- modulesLoaded: called when all modules are locked and loaded.
* 	- resourcesLoad: called before Css and Js resouces are loaded.
* 	- end: This is called at the end but before resources are included on the page, the page is sent, and more.
*  - endAfter: This is called after the body of the page is loaded but before Properties are set.
*/

$onEvents = array();
/**
* \ingroup Event
* This function will register the event hook with the provided event name.
* 
* \param boolean $after This sets this event hook to be called after all of the other "normal" event hooks are called.
*
* Information:
* 	- Return: void
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
*/
function onEvent($name, callable $callback, $after = false) {
	global $onEvents;
	$str = "_after";
	if (!$after) $str = '';
	
	if (!isset($onEvents[$name.$str])) {
		$onEvents[$name.$str] = array($callback);
	}else{
		array_push($onEvents[$name.$str], $callback);
	}
}

/**
* \ingroup Event
* This function will invoke the provided event name.
* 
* Information:
* 	- Return: void
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
*/
function callEvent($name, $args = array()) {
	global $onEvents;
	if (isset($onEvents[$name]))
		foreach($onEvents[$name] as $event)
			$event($args);
			
	if (isset($onEvents[$name.'_after']))
		foreach($onEvents[$name.'_after'] as $event)
			$event($args);
}



include("Link.php");

Linker::init();

include("Responsive.php");

include("Element.php");

include("Javascript.php");



include("Resource.php");

include("Client.php");

include("Dependency.php");

include('process.php');

include(Websom_root."/Generic/Input/Input.php");

include("Data_Form.php");

include("Theme.php");

include("Websom_Run_Modules.php");

callEvent("resourcesLoad");

Resources::Register_All(Local_root.'Css/');
Resources::Register_All(Local_root.'Javascript/');

Resources::setInfo(Local_root."Javascript/Jquery.js", ["index" => 9999]);
Resources::setInfo(Local_root."Javascript/Tools.js", ["index" => 9998]);

Resources::setInfo(Local_root."Javascript/main.js", ["index" => 9997]);

Resources::setInfo(Local_root."Javascript/Form.js", ["index" => 9996]);
Resources::setInfo(Local_root."Javascript/Theme.js", ["index" => 9996]);
Resources::setInfo(Local_root."Javascript/Input.js", ["index" => 9996]);



include("Widget_Run.php");

include("Control_Run.php");

include("Export.php");

callEvent('ready');

/**
* \defgroup TemplateClasses Template Classes
*/
?>