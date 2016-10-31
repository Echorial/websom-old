<?php
function Input_multi_Status(){
	return true;
}

function Input_multi_Sanitize_Client(){
	return "
		return true;
	";
}

function Input_multi_Sanitize_Server($o, $v){
	return true;
}

function Input_multi_Html_Get($o, $args, $v = ''){
	$v = explode('^|', $v);
	$rtn = '';
	foreach ($o['multi'] as $name => $options) {
		$checked = '';
		if (in_array($options['value'], $v) || isset($options['checked']))
			$checked = 'checked';
		$rtn .= '<input type="checkbox" '.$checked.' '.str_replace('" ctype', '['.$options['value'].']" ctype', $args).' value="'.$options['value'].'" id="multi_chk_'.$options['value'].'"><label for="multi_chk_'.$options['value'].'"><span></span>'.$name.'</label>';
	}
	return $rtn;
}

function Input_multi_Override_Value($n, $o) {
	$rtn = array();
	$value = $_POST[$n];
	foreach ($o['multi'] as $name => $options) {
		if (in_array($options['value'], $value)) array_push($rtn, $options['value']);
	}
	return implode('^|', $rtn);
}
?>