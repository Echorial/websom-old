<?php

function Input_date_Status(){
	return true;
}

function Input_date_Sanitize_Client(){
return "return true;";
}

function Input_date_Sanitize_Server($Options, $Value){
	if (DateTime::createFromFormat('m/d/Y', $Value) === false) {
		return 'Invalid date format.';
	}
	return true;
}

function Input_date_Html_Get($Options, $args, $Value = ''){
	return '<input type="text" '.$args.' requi="datepicker" value="'.$Value.'">';
}

function Input_date_Override_Value ($val, $options, $valA) {
	if (isset($options['mysqlformat']))
		return date("Y-m-d H:i:s", strtotime($valA));
	return $valA;
}
?>