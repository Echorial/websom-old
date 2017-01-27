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
		$attr .= ' not="'.$Options['not'][0].'" notdesc="'+$Options['not'][1]+'"';
	}
	if (isset($Options['only'])){
		$attr .= ' only="'.$Options['only'][0].'" onlydesc="'+$Options['only'][1]+'"';
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
*	- Text->not: This will not allow any characters found with the regex string. Example Text->not = ["[A-Z0-9]", "You cannot use the characters A-Z or 0-9"];
*	- Text->only: This will only allow the regex string characters. Example Text->only = ["[A-Z0-9]", "The allowed characters are A-Z and 0-9"];
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
	
	public $not = ["", ""];
	public $only = ["", ""];
	
	public $label = "input_text";
	public $placeholder = "Text";
	
	public $displayType = "text";
	public $blank = false;
	
	public $defaultValue = "";
	
	/**
	* \param string $displayType allowed values (text, password, email, multiline)
	*/
	function __construct($displayType = "text") {
		$this->displayType = $displayType;
	}
	
	/**
	* For making extending Text easier.
	*/
	function buildElement() {
		$e = Theme::input_text($this->defaultValue, $this->placeholder, $this->label, ["type" => $this->displayType]);
		
		if (!is_array($this->only) OR !is_array($this->not))
			throw new Exception("Input only/not property needs to be an array [regex string, error string].");
		
		$e->attr("id", $this->id);
		$e->attr("blank", ($this->blank ? 1:0));
		$e->attr("count", $this->character_min.' '.$this->character_max);
		$e->attr("not", $this->not[0]);
		$e->attr("notdesc", $this->not[1]);
		$e->attr("only", $this->only[0]);
		$e->attr("onlydesc", $this->only[1]);
		$e->attr("isinput", "");
		
		
		
		//$html = '<input isinput id="'.$this->id.'" blank="'.($this->blank ? 1:0).'" count="'.$this->min.' '.$this->max.'" not="'.$this->not.'" only="'.$this->only.'" name="'.$meta['name'].'" type="text" placeholder="Text"></input>';
		
		$this->doVisible($e);
		
		return $e;
	}
	
	function get() {
		return $this->buildElement()->get();
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
				return 'This contains an invalid character. The allowed characters are : ' + $(element).attr('notdesc');
		}
		if ($(element).hasAttr('only') && $(element).attr('only') !== '') {
			var Only = new RegExp($(element).attr('only'), 'g');
			value = value.replace(Only, '');
			if (value != '')
				return 'This contains an invalid character. The allowed characters are : ' + $(element).attr('onlydesc');
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
		
		if ($this->only[0] != "") {
			$str = preg_replace('~'.$this->only[0].'~', '', $data);
			if ($str != "")
				return 'Error there are some invalid characters. The allowed characters are '.$this->only[1];
		}
		
		if ($this->not[0] != ""){
			preg_match('~'.$this->not[0].'~', $data, $match2);
			if (count($match2) > 0)
				return 'This contains an invalid character. '.implode(', ', $match2);
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