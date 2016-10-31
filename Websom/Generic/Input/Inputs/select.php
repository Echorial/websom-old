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




/**
* \ingroup BuiltInInputs
*
* The `Select` input is a multiple or single option select drop down.
*
* Compatible with `Input_List` and `Input_Group`
*
* Options:
*	- Select->multiple: If the user can select multiple values.
*
*/
class Select extends Input {
	public $globalName = 'Select';
	
	public $placeholder = 'Select';
	public $label = 'Select';
	
	public $options = [];
	
	public $multiple = false;
	
	public $allowDefault = false;
	
	function __construct($op) {
		$this->options = $op;
	}
	
	function get() {
		$ops = $this->options;
		
		
		$ops = [$this->placeholder => ""] + $ops;
		
		$e = Theme::input_select($ops, $this->label, ["default" => $this->placeholder, "multiple" => $this->multiple]);
		
		$e->attr("id", $this->id);
		$e->attr("allow-default", ($this->allowDefault) ? 1:0);
		$e->attr("default-key", $this->placeholder);
		$e->attr("is-multiple", ($this->multiple) ? 1:0);
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
		if ($(element).attr('is-multiple') == '1')
			return true;
		
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
		if ($this->multiple) {
			$built = [];
			
			foreach ($data as $d) {
				if (!in_array($d, $this->options)) {
					return false;
				}else{
					array_push($built, $d);
				}
			}
			return $built;
		}else{
			if (!isset($this->options[$data[0]]))
				return false;
			
			if ($this->options[$data[0]] !== $data[1])
				return false;
		}
		
		return $data[1];
	}
	
	function load() {
		return '
			window.Websom.Theme.set($(element), data);
		';
	}
}



?>