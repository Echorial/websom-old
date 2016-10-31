<?php

function Input_email_Status(){
	return true;
}

function Input_email_Sanitize_Client(){
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
	
	var emailTest = /^(([^<>()\[\]\\.,;:\s@\"]+(\.[^<>()\[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    if (!emailTest.test($(_e).val()))
		return 'Invalid email.';
	
	return true;
	
";
}

function Input_email_Sanitize_Server($o, $v){
	//If there is an error return an error string, or return true
	if (isset($o['count'])) {
		$Count = explode(' ', $o['count']);
		if (strlen($v) > $Count[1]) {
			return 'Too long must be less than ' . $Count[1] . ' characters.';
		}
		if (strlen($v) < $Count[0]) {
			return 'Too short must be at least ' . $Count[0] . ' characters.';
		}
	}
	
	if (!filter_var($v, FILTER_VALIDATE_EMAIL))
		return 'Invalid email.';
	
	return true;
}

function Input_email_Html_Get($o, $args, $val = ''){
	return '<input type="email" '.$args.' value="'.$val.'" count="'.$o['count'].'" placeholder="'.$o['placeholder'].'">';
}
?>