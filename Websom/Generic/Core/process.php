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
* Information:
* 	- Return: void
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
*/
function onProcess($name, callable $callback){
	global $_PROCESSES;
	$_PROCESSES[$name] = $callback;
}

/**
* \ingroup Proccess
* This function is used to inkove a proccess event.
* 
* <div class="warning">Never call this function. Use callEvent() and onEvent() to handle events.</div>
* 
* Information:
* 	- Return: void
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
*/
function fireProcessEvent($name, $params){
	global $_PROCESSES;
	if (isset($_PROCESSES[$name])) call_user_func($_PROCESSES[$name], $params);
}

/**
* \ingroup Proccess
* This function is used to retrive a url to run a certain proccess.
* <br>
* The `fullUrl` option will return a full url including the host.
* 
* Information:
* 	- Return: string
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
*/
function createProcessLink($name, $params, $fullUrl = true) {
	$vars = '';
	foreach ($params as $p => $v){
	$vars .= $p.'='.$v.'&';}
	return (($fullUrl) ? Host : '').'/p.php?p='.$name.'&'.rtrim($vars, '&');
}

?>