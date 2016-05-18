<?php

$Input_Areas = array();

$Js = include_all(Websom_root."/Generic/Input/Inputs/");


$InputScript = '';
$InputScript .= '<script>
';
foreach($Js as $Name){
	$InputScript .= 'function '.$Name.'_Sanitize(_e, _c, _o) {';
		$InputScript .= CallFunction('Input_'.$Name.'_Sanitize_Client');
	$InputScript .= '}';
	if (function_exists('Input_'.$Name.'_Global_Javascript'))
		$InputScript .= CallFunction('Input_'.$Name.'_Global_Javascript');
}

$InputScript .= '
</script>';

function Get_Input_Scripts() {
	global $InputScript;
	return $InputScript;
}

function Start_Input_Area($id = 'nullid', $mode = 'Strict', $extra = ''){
	global $Input_Areas;
	
	$NewArea['inputs'] = array();
	$NewArea['mode'] = $mode;
	
	array_push($Input_Areas, $NewArea);											//       v      Makes it look complicated       v
	return '<form method="POST" id="'.$id.'" action="" '.$extra.' globalserverid="'.Random_Chars(5).(count($Input_Areas)).Random_Chars(6).'" enctype="multipart/form-data">';
}

function Random_Chars($count) {
	return substr(str_shuffle("1234567890"), 0, $count);
}

function Add_Input($Name, $Options, $value = null){
	global $Input_Areas;
	$Input_Areas[count($Input_Areas)-1]['inputs'][$Name] = $Options;
	$args = 'name="'.$Name.'" ctype="'.$Options['type'].'" ';
	if (isset($Options['submitonchange']))
		$args .= ' submitonchange="true" ';
	if (isset($Options['optional']))
		$args .= ' optionalpost="true" ';
	if (isset($Options['class']))
		$args .= ' class="'.$Options['class'].'" ';
	return CallFunctionArgs("Input_".$Options['type']."_Html_Get", array($Options, $args, $value));
}

function End_Input_Area(){
	return '</form>';
}

function Get_Input_Area(){
	
	global $Input_Areas;

	//Check if the current input area is submited\\
	if (count($_POST) == 0){
		return false;
	}else{
		//TODO: Look at the following mess.\\
		
		if (!isset($_POST['globalserverid'])){
			return false;
		}
		
		$formId = substr($_POST['globalserverid'], 5, 1)-1;
		
		if (!isset($Input_Areas[$formId])) {
			return false;
		}
		
		if ($formId != count($Input_Areas)-1) {
			return false;
		}

		$__POST = $_POST; //Real creative\\ 
		$__POST =  array_merge($__POST, $_FILES);
	
		unset($__POST['globalserverid']);

		if ($Input_Areas[$formId]['mode'] == 'Strict') {
			//if (Array_Key_Compare($Input_Areas[count($Input_Areas)-1]['inputs'], $__POST)){
				foreach ($Input_Areas[$formId]['inputs'] as $InputId => $InputOptions){
					if (isset($InputOptions['optional']))
						if ($__POST[$InputId] == '')
							continue;
					if (function_exists("Input_".$InputOptions['type']."_Override_Value"))
						$__POST[$InputId] = CallFunctionArgs("Input_".$InputOptions['type']."_Override_Value", array($InputId, $InputOptions, $__POST[$InputId]));
					
					$ErrorIf = CallFunctionArgs("Input_".$InputOptions['type']."_Sanitize_Server", array($InputOptions, $__POST[$InputId]));
					if ($ErrorIf !== true){
						Cancel('{"Error": "'.$ErrorIf.'"}');
						return false;
					}
				}
				return $__POST;
			//}
		}else if ($Input_Areas[$formId]['mode'] == 'Loose'){
			foreach($__POST as $Post_Name => $Post_Value){
				foreach ($Input_Areas[$formId]['inputs'] as $InputId => $InputOptions){
					if (strpos($Post_Name, $InputId) !== false){
						if (isset($InputOptions['optional']))
							if ($__POST[$InputId] == '')
								continue;
						if (function_exists("Input_".$InputOptions['type']."_Override_Value"))
							$__POST[$InputId] = CallFunctionArgs("Input_".$InputOptions['type']."_Override_Value", array($InputId, $InputOptions, $__POST[$InputId]));
					
						$ErrorIf = CallFunctionArgs("Input_".$InputOptions['type']."_Sanitize_Server", array($InputOptions, $Post_Value));
					
						if ($ErrorIf !== true) {
							Cancel('{"Error": "'.$ErrorIf.'"}');
							return false;
						}
						continue 2;
					}
				}
			}
			
			return $__POST;
		}
	}
}

function Array_Key_Compare($Array1, $Array2){
	foreach($Array1 as $K => $V){
		if (array_key_exists($K, $Array2) == false){
			return false;
		}
	}
	return true;
}
?>