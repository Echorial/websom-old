<?php

function Input_checkbox_Status(){
	return true;
}

function Input_checkbox_Sanitize_Client(){
	return "
		return true;
	";
}

function Input_checkbox_Sanitize_Server($o, $v){
	return true;
}

function Input_checkbox_Html_Get($o, $args, $v = ''){
	$checked = '';
	if ($v == 1 || isset($o['checked']))
		$checked = 'checked';
	if (!isset($o['value'])) $o['value'] = '';
	return '<input type="checkbox" '.$checked.' '.$args.' value="1">'.$o['value'];
}



?>