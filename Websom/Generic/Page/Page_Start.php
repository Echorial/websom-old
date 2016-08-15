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

/* -
* \ingroup PageFunctions
* 
* Use this function to insert a string into the html structure.
*
* <div class="warning">This will search the structure for $tag:$place and add $what into the found place. If the tag and or place is not found nothing will happen.</div>
*
* Example:
* \code
* 	Inject("head", "top", "This string will be injected into the top of the <head> tag");
* \endcode
*
* Information:
* 	- Author: Echorial
* 	- Date: 8/12/16
* 	- Version: 1.4
*
* Params:
* 	- tag: what tag to insert the $what into.
* 	- place: where to insert the $what.
* 		- top: at the top of the tag
* 		- bottom: at the bottom of the tag.
* 	- what: the string to insert.
*
*//*
function Inject($tag, $place, $what) {
	$add = $what;
	$prop = GetPropertie($tag.':'.$place);
	if (isset($prop)) {
		if ($place == 'top'){
			$add .= $prop;
		}else{
			$add = $prop.$add;
		}
	}
	SetPropertie($tag.':'.$place, $add);
}*/

include(Websom_root."/Generic/Core/Page_Functions.php");


//Old resource including.
/*
include(Websom_root."/Generic/Core/Page_Javascript_Loader.php");

include(Websom_root."/Generic/Core/Page_Css_Loader.php");
*/

//New way located in Resource.php




ob_start();
?>