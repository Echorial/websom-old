<?php
/**
* \defgroup PageFunctions Page Functions
* This is a list of helpful functions provided by websom.
*/

/**
* \ingroup PageFunctions
* This function will send a header to forward the client to a new url.
* 
* Information:
* 	- Return: void
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
*/
function Go($Location){
	header("Location: ".$Location);
}


 /**
* \ingroup PageFunctions
* This function will check if a given array is associative.
* 
* Information:
* 	- Return: boolean
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
*/
function is_array_associative($a) { //Does not realy fit in\\
	if (is_array($a)) {
		return array_keys($a) !== range(0, count($a) - 1);
	}else{
		return false;
	}
}

 /**
* \ingroup PageFunctions
* This function will return a list of key/value pairs in string format.
* 
* Params:
* 	- $array: array of key/value pairs
*
* Information:
* 	- Return: string
* 	- Author: Echorial
* 	- Date: 8/11/16
* 	- Version: 1.3
*/
function list_key_values($array, $indent = false) {
	$rtn = [];
	foreach ($array as $key => $value) {
		if (is_array_associative($value)) {
			array_push($rtn, ($indent ? '	' : '').$key.': {
'.list_key_values($value, true).'
}');
		}else{
			array_push($rtn, ($indent ? '	' : '').$key.': '.json_encode($value, true));
		}
	}
	return implode('
', $rtn);
}
?>