<?php
/*
$Forms = array();

function GetForms(){
	global $Forms;
	return $Forms;
}

function &GetForm(){
	global $Forms;
	return $Forms[count($Forms)-1];
}

function FindForm($Id){
	global $Forms;
	foreach ($Forms as $Form){
		if ($Form['id'] == $Id){
			return $Form;
		}
	}
}

function Data_Widget_Edit_Start($ModuleName, $Where, $Message = "Success"){
	global $Forms;
	array_push($Forms, array("type" => "Edit", "id" => count($Forms), "module" => $ModuleName, "controls" => array(), "message" => $Message, "where" => $Where));
	return "<form method='POST' action='' id='Form".count($Forms)."'><input hidden name='FormId' id='".count($Forms)."'>"; //The hidden input is for form identification (Theres a better way, im just to lazy right now)
}

function Data_Widget_Edit_End(){
	return "</form>";
}
*/

?>