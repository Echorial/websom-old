<?php

function Input_submit_Status(){
	return true;
}

function Input_submit_Sanitize_Client(){
	return "
	
	";
}

function Input_submit_Sanitize_Server($Options, $Value){	
	return true;
}

function Input_submit_Html_Get($Options, $args, $Value = ''){

	return '<input type="submit" '.$args.' value="'.$Options['value'].'">';
}

?>