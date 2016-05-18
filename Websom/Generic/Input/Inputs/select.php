<?php

function Input_select_Status(){
	return true;
}

function Input_select_Sanitize_Client(){
return "
	return true;
";
}

function Input_select_Sanitize_Server($Options, $Value){
	if (isset($Options['hideplaceholder']))
		if ($Value == 'placeholder' AND $Options['hideplaceholder'] == false)
			return true;
	if ($Value == 'placeholder')
		return $Options['placeholder'].' not set';
	
	foreach ($Options['options'] as $id => $op) {
		if (!is_array($op))
			$op = array($op, $id);
		if ($op[1] == $Value) {
			return true;
		}
	}
	
	return 'Invalid value.';
}

function Input_select_Html_Get($Options, $args, $Value = null){
	if ($Value === null) $Value = -10;
	$h = '<select '.$args.' type="select">';
	$setDefault = false;
	if (isset($Options['placeholder'])) {
		$hide = 'hidden';
		if (isset($Options['hideplaceholder'])) if ($Options['hideplaceholder'] == false) $hide = '';
		$h .= '<option '.$hide.' selected="selected" value="placeholder">'.$Options['placeholder'].'</option>';
		$setDefault = true;
	}
	
	foreach ($Options['options'] as $id => $op) {
		if (!is_array($op))
			$op = array($op, $id);
		$s = '';
		if (!$setDefault OR $Value == $op[1]) {
			if ($Value == $op[1] OR isset($op['default'])) {
				$s .= ' selected="selected" ';
				$setDefault = true;
			}
		}
		$h .= '<option '.$s.' value="'.$op[1].'">'.$op[0].'</option>';
	}
	return $h.'</select>';
}

?>