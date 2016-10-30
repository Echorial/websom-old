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

/**
* \ingroup BuiltInInputs
*
* This is a very simple boolean input box.
*
* Compatible with `Input_List` and `Input_Group`
*
* Options:
*	- Checkbox->text: The text next to the box.
*
*/
class Checkbox extends Input {
	public $globalName = 'Checkbox';
	
	public $text = 'Checkbox';
	public $label = 'Checkbox';
	
	function get() {
		$e = Theme::input_check($this->text, $this->label);
		
		$e->attr("id", $this->id);
		$e->attr("isinput", "");
		
		return $e->get();
	}
	
	function send() {
		return '
		return window.Websom.Theme.get($(element));
		';
	}
	
	function validate_client() {
		return "
		return true;
		";
	}
	
	function validate_server($data) {
		if ($data !== true AND $data !== false)
			return false;
		
		return true;
	}
	
	function error() {
		return "
			return $('<div>'+error+'</div>').insertAfter(element);
		";
	}
	
	function receive($data) {
		return ($data === "true") ? true : false;
	}
	
	function load() {
		return '
			window.Websom.Theme.set($(element), data);
		';
	}
}


?>