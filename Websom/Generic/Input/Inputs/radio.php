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