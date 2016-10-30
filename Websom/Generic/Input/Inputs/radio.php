<?php
function Input_radio_Status(){
	return true;
}

function Input_radio_Sanitize_Client(){
	return "
		return true;
	";
}

function Input_radio_Sanitize_Server($o, $v){
	foreach ($o['options'] as $option)
		if ($option['value'] == $v)
			return true;
	return 'Selection error.';
}

function Input_radio_Html_Get($o, $args, $v = ''){
	$v = explode('^|', $v);
	$rtn = '';
	foreach ($o['options'] as $name => $options) {
		$checked = '';
		if (in_array($options['value'], $v) || isset($options['checked']))
			$checked = 'checked';
		$rtn .= '<input type="radio" '.$checked.' '.$args.' value="'.$options['value'].'" id="radio_rdi_'.$options['value'].'"><label for="radio_rdi_'.$options['value'].'"><span></span>'.$name.'</label>';
	}
	return $rtn;
}



/**
* \ingroup BuiltInInputs
*
* The `Radio` input is a single option selection list.
*
* Compatible with `Input_List` and `Input_Group`
*
* Options:
*	- Radio->default_key: The default key to be selected.
*
*/
class Radio extends Input {
	public $globalName = 'Radio';
	
	public $label = "Radio";
	
	public $default_key = "";
	
	function __construct($op) {
		$this->options = $op;
	}
	
	function get() {
		$ops = $this->options;
		
		$opts = ["default" => $this->default_key];
		if ($opts["default"] == "") unset($opts["default"]);
		
		$e = Theme::input_radio($ops, $this->label, $opts);
		
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
		
		if ($(element).attr('allow-default') === '0') {
			if (window.Websom.Theme.get($(element))[0] == $(element).attr('default-key'))
				return 'Please select an option.';
		}
		
		return true;
		";
	}
	
	function validate_server($data) {
		if ($data === false)
			return false;
		
		return true;
	}
	
	function error() {
		return "
			return $('<div>'+error+'</div>').insertAfter(element);
		";
	}
	
	function receive($data) {
		
		if (!in_array($data, $this->options))
			return false;
		
		return $data;
	}
	
	function load() {
		return '
			window.Websom.Theme.set($(element), data);
		';
	}
}

/*

function Input_radio_Override_Value($n, $o) {
	$rtn = array();
	$value = $_POST[$n];
	foreach ($o['multi'] as $name => $options) {
		if (in_array($options['value'], $value)) array_push($rtn, $options['value']);
	}
	return implode('^|', $rtn);
}
*/

?>