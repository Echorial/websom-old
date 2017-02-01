<?php
/**
* \defgroup Proccess Proccess
* Proccess is used by modules to allow modules to do things when a proccess is invoked.
* <hr>
* Proccess uses `p.php` for its event hooking. Say a module needs to send a link to a new user to confirm their email. This module would use something like `onProcess("confirmEmail"
* , confirmEmail)` this would then register the `confirmEmail` callback to be invoked when the url `/p.php?p=confirmEmail` is navigated to. To create the url use createProcessLink().
* Extra params are also available. Read bellow for more info.
*/

$_PROCESSES;

/**
* \ingroup Proccess
* This function is used to add an event hook for when the provided proccess is called.
* 
* @param string $name The name of the proccess to listen for.
* @param callable $callback The function to call when the proccess is fired.
* 
*/
function onProcess($name, callable $callback){
	global $_PROCESSES;
	$_PROCESSES[$name] = $callback;
}

/**
* \ingroup Proccess
* This function is used to inkove a proccess event.
* 
* <div class="warning">Try not to call this function. Use callEvent() and onEvent() to handle events.</div>
* 
* @param string $name The proccess name.
* @param array(key/value) $params The params to pass.
* 
*/
function fireProcessEvent($name, $params){
	global $_PROCESSES;
	if (isset($_PROCESSES[$name])) call_user_func($_PROCESSES[$name], $params);
}

/**
* \ingroup Proccess
* This function will return a string url that when visted will run the proccess with the $params.
* 
* @param string $name The proccess name.
* @param array(key/value) $params The params to pass.
* @param bool $fullUrl If the link should be a full url with the Host. If false the link will be local.
* 
*/
function createProcessLink($name, $params, $fullUrl = true) {
	$vars = '';
	
	foreach ($params as $p => $v)
		$vars .= $p.'='.$v.'&';
		
	return (($fullUrl) ? Host : '').'/p.php?p='.$name.'&'.rtrim($vars, '&');
}

?>