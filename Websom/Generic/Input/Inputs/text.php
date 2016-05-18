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
?>