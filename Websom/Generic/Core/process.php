<?php

$_PROCESSES;

function onProcess($name, callable $callback){
	global $_PROCESSES;
	$_PROCESSES[$name] = $callback;
}

function fireProcessEvent($name, $params){
	global $_PROCESSES;
	if (isset($_PROCESSES[$name])) call_user_func($_PROCESSES[$name], $params);
}

function createProcessLink($name, $params) {
	$vars = '';
	foreach ($params as $p => $v){
	$vars .= $p.'='.$v.'&';}
	return Host.'/p.php?p='.$name.'&'.rtrim($vars, '&');
}

?>