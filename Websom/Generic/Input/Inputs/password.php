<?php

function Input_password_Status(){
	return true;
}

function Input_password_Sanitize_Client(){
return "
	
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
		var Not = $(_e).attr('not').split(' ');
		Not.replace('Space', ' ');
		if (_e.value.contains(Not)){
			return 'This contains an invalid character. They are : ' + $(_e).attr('not');
		}
	}
	if ($(_e).hasAttr('only')) {
		var Only = $(_e).attr('only').split(' ');
		Only.replace('Space', ' ');
		if (_e.value.notcontains(Only)){
			return 'This contains an invalid character. The allowed characters are : ' + $(_e).attr('only');
		}
	}
	
	return true;
	
";
}

function Input_password_Sanitize_Server($o, $val){
	if (isset($o['count'])) {
		$Count = explode(' ', $o['count']);
		if (strlen($val) > $Count[1]) {
			return 'Too long must be less than ' . $Count[1] . ' characters.';
		}
		if (strlen($val) < $Count[0]) {
			return 'Too short must be at least ' . $Count[0] . ' characters.';
		}
	}
	
	if (isset($o['only'])) {
		$Only = explode(' ', $o['only']);
		if (strnotcontains($val, arr_replace('Space', ' ', $Only)) == true) {
			return 'This contains an invalid character. The allowed characters are : ' . implode(' ', $Only);
		}
	}
	
	if (isset($o['not'])){
		$Not = explode(' ', $o['not']);
		if (strcontains($val, arr_replace('Space', ' ', $Not))) {
			return 'This contains an invalid character. They are : ' . implode(' ', $Not);
		}
	}
	
	return true;
}

function Input_password_Html_Get($o, $args, $val = ''){
	$attr = '';
	
	if (isset($o['count'])){
		$attr .= ' count="'.$o['count'].'" ';
	}
	if (isset($o['not'])){
		$attr .= ' not="'.$o['not'].'" ';
	}
	if (isset($o['only'])){
		$attr .= ' only="'.$o['only'].'" ';
	}
	if (isset($o['placeholder'])){
		$attr .= ' placeholder="'.$o['placeholder'].'" ';
	}
	return '<input type="password" '.$args.' value="'.$val.'" '.$attr.' ">';
}
?>