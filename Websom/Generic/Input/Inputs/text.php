<?php

function Input_text_Status(){
	return true;
}

function Input_text_Sanitize_Client(){
	//write the following in javascript. 
	//The element variable is _e
	//The check variable is _c it can be "Submit" or "Input" (if the value of the element changed or was submited via form button press)
	//If there is an error. make sure to return the error string
return "
	
	if (_e.value == '')
		return 'Cannot be blank.';
	
	if ($(_e).hasAttr('count')) {
		var Count = $(_e).attr('count').split(' ');
		if (_e.value.length > Count[1]) {
			return 'Too long must be less than ' + Count[1] + ' characters.';
		}
		
		if (_e.value.length < Count[0] && _c == 'Submit') {
			return 'Too short must be at least ' + Count[0] + ' characters.';
		}
	}
	if ($(_e).hasAttr('not')) {
		var Not = new RegExp($(_e).attr('not'), 'g');
		if (_e.value.match(Not) !== null)
			return 'This contains an invalid character. The allowed characters are : ' + $(_e).attr('not');
	}
	if ($(_e).hasAttr('only')) {
		var Only = new RegExp($(_e).attr('only'), 'g');
		if (_e.value.match(Only) !== null)
			return 'This contains an invalid character. The allowed characters are : ' + $(_e).attr('only');
	}
	
	return true;
	
";
}

function Input_text_Global_Javascript () {
	return '
	onEvent("DOMChange", function () {
		$("[autocompletejs]").each(function () {
			$(this).autocomplete({source: $(this).attr("autocompletejs").split(",")});
		});
	});
	';
}

function Input_text_Sanitize_Server($Options, $Value){
	if ($Value == '')
		return 'Cannot be blank.';
	//If there is an error return an error string, or return true
	if (isset($Options['count'])) {
		$Count = explode(' ', $Options['count']);
		if (strlen($Value) > $Count[1]) {
			return 'Too long must be less than ' . $Count[1] . ' characters.';
		}
		if (strlen($Value) < $Count[0]) {
			return 'Too short must be at least ' . $Count[0] . ' characters.';
		}
	}
	
	if (isset($Options['only'])) {
		preg_match('~'.$Options['only'].'~', $Value, $match);
		if (count($match) > 0)
			return 'This contains an invalid character. '.implode(', ', $match);
	}
	
	if (isset($Options['not'])){
		preg_match('~'.$Options['not'].'~', $Value, $match);
		if (count($match2) > 0)
			return 'This contains an invalid character. '.implode(', ', $match);
	}
	
	return true;
}

function Input_text_Html_Get($Options, $args, $Value = ''){
	//Always make value optional. The value is for, if the input is already set.
	$attr = '';
	$type = 'input type="text"';
	$typeAfter = '';
	if (isset($Options['count'])){
		$attr .= ' count="'.$Options['count'].'" ';
	}
	if (isset($Options['not'])){
		$attr .= ' not="'.$Options['not'].'" ';
	}
	if (isset($Options['only'])){
		$attr .= ' only="'.$Options['only'].'" ';
	}
	if (isset($Options['placeholder'])){
		$attr .= ' placeholder="'.$Options['placeholder'].'" ';
	}
	if (isset($Options['multiline'])) {
		$lines = explode(' ', $Options['multiline']);
		$attr .= ' cols="'.$lines[0].'" rows="'.$lines[1].'" ';
		$type = 'textarea';
		$typeAfter = $Value.'</textarea>';
		$Value = '';
	}
	if (isset($Options['autocomplete'])) {
		$a = '';
		foreach($Options['autocomplete'] as $val) $a .= $val.',';
		$attr .= ' autocompletejs="'.trim($a, ',').'" ';
	}
	return '<'.$type.' '.$args.' value="'.$Value.'" '.$attr.' >'.$typeAfter;
}

function strcontains($Str, $Array){
	foreach (str_split($Str) as $char){
		foreach ($Array as $Check){
			if ($char == $Check) {
				return true;
			}
		}
	}
	return false;
}

function strnotcontains($Str, $Array){
	$test = false;
	foreach (str_split($Str) as $char){
		$test2 = false;
		foreach ($Array as $Check){
			if ($char == $Check) {
				$test2 = true;
			}
		}
		if ($test2 == false) return true;
	}
	return false;
} 

function arr_replace($Search, $Replace, $Array){
	foreach ($Array as $K => $Find){
		if ($Find == $Search){
			$Array[$K] = $Replace;
		}
	}
	return $Array;
}




/**
* \ingroup BuiltInInputs
*
* The `Text` input is a nice easy to use validated text box.
*
* Compatible with `Input_List` and `Input_Group`
*
* Options:
*	- Text->not: Warning this is experimental and may not work. A regex string.
*	- Text->only: Warning this is experimental and may not work. A regex string.
*	- Text->placeholder: A placeholder string.
*	- Text->blank: If blank values are allowed.
*	- Text->character_min: The least number of characters allowed.
*	- Text->character_max: The most number of characters allowed.
*
*/
class Text extends Input {
	public $globalName = 'Text';
	
	public $character_min = 0;
	public $character_max = 999999;
	
	public $not = "";
	public $only = "";
	
	public $label = "input_text";
	public $placeholder = "Text";
	
	public $displayType = "text";
	public $blank = false;
	
	public $defaultValue = "";
	
	/**
	* \param string $displayType allowed values (text, password, email)
	*/
	function __construct($displayType = "text") {
		$this->displayType = $displayType;
	}
	
	function get() {
		$e = Theme::input_text($this->defaultValue, $this->placeholder, $this->label);
	
		
		$e->attr("id", $this->id);
		$e->attr("blank", ($this->blank ? 1:0));
		$e->attr("count", $this->character_min.' '.$this->character_max);
		$e->attr("not", $this->not);
		$e->attr("only", $this->only);
		$e->attr("isinput", "");
		
		//$html = '<input isinput id="'.$this->id.'" blank="'.($this->blank ? 1:0).'" count="'.$this->min.' '.$this->max.'" not="'.$this->not.'" only="'.$this->only.'" name="'.$meta['name'].'" type="text" placeholder="Text"></input>';
		
		return $e->get();
	}
	
	function send() {
		return '
		return window.Websom.Theme.get($(element));
		';
	}
	
	function validate_client() {
		return "
		
		var value = window.Websom.Theme.get($(element));
		if ($(element).attr('blank') == '0' && value == '')
		return 'Cannot be blank.';
	
		if ($(element).hasAttr('count')) {
			var Count = $(element).attr('count').split(' ');
			if (value.length > Count[1]) {
				return 'Too long must be less than ' + Count[1] + ' characters.';
			}
			
			if (value.length < Count[0]) {
				return 'Too short must be at least ' + Count[0] + ' characters.';
			}
		}
		if ($(element).hasAttr('not') && $(element).attr('not') !== '') {
			var Not = new RegExp($(element).attr('not'), 'g');
			if (value.match(Not) !== null)
				return 'This contains an invalid character. The allowed characters are : ' + $(element).attr('not');
		}
		if ($(element).hasAttr('only') && $(element).attr('only') !== '') {
			var Only = new RegExp($(element).attr('only'), 'g');
			if (value.match(Only) !== null)
				return 'This contains an invalid character. The allowed characters are : ' + $(element).attr('only');
		}
		
		return true;
		";
	}
	
	function validate_server($data) {
		if (!$this->blank AND $data == '') return "Cannot be blank.";
		
		if (strlen($data) > $this->character_max) {
			return 'Too long must be less than ' . $this->character_max . ' characters.';
		}
		if (strlen($data) < $this->character_min) {
			return 'Too short must be at least ' . $this->character_min . ' characters.';
		}
		
		if ($this->only != "") {
			preg_match('~'.$this->only.'~', $data, $match);
			if (count($match) > 0)
				return 'This contains an invalid character. '.implode(', ', $match);
		}
		
		if ($this->not != ""){
			preg_match('~'.$this->not.'~', $data, $match);
			if (count($match2) > 0)
				return 'This contains an invalid character. '.implode(', ', $match);
		}
		
		return true;
	}
	
	function error() {
		return "
			return $('<div>'+error+'</div>').insertAfter(element);
		";
	}
	
	function receive($data) {
		return $data;
	}
	
	function load() {
		return '
			window.Websom.Theme.set($(element), data);
		';
	}
}




?>