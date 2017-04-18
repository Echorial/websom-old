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


/**
* \ingroup BuiltInInputs
*
* The `Date` input is a simple Theme::input_date wrapper.
*
* \note The date format is Y-m-d H:i:s 
* 
* Compatible with `Input_List` and `Input_Group`
*
* Options:
*
*/
class DateInput extends Input {
	public $globalName = 'DateInput';
	
	public $defaultDate = "";
	
	public $label = "date";
	
	function __construct() {
		$this->defaultDate = date("Y-m-d");
	}
	
	function get() {
		$e = Theme::input_date($this->defaultDate, $this->label);
		
		$e->attr("id", $this->id);
		$e->attr("isinput", "");
		
		$this->doVisible($e);
		
		return $e->get();
	}
	
	function send() {
		return 'return window.Websom.Theme.get($(element));';
	}
	
	function validate_client() {
		return "return true;";
	}
	
	function validate_server($data) {
		if ($data === false)
			return false;
		return true;
	}
	
	function into($val) {
		$split = explode("-", date("Y-m-d", strtotime($val)));
		return $split;
	}
	
	function error() {
		return "return $('<div>'+error+'</div>').insertAfter(element);";
	}
	
	function receive($data) {
		if (DateTime::createFromFormat('Y-m-d', $data) === false) {
			return false;
		}
		return date("Y-m-d H:i:s", strtotime($data));
	}
	
	function load() {
		return 'window.Websom.Theme.set($(element), data);';
	}
}

?>