<?php

$Client_Lists = [
	'Action' => []
];

/**
* Use this to register an action to be used on a particular page.
*
* \param Object that extends Action $action An instance of a class that extends from Action.
*/
function Register_Action($action) {
	global $Client_Lists;
	
	array_push($Client_Lists['Action'], $action);
}


function Get_Client_Scripts() {
	global $Client_Lists;
	
	$script = '<script>
	
	';
	
	foreach ($Client_Lists['Action'] as $a) {
		$script .= 'window["Action_'.$a->name.'"] = function (element, data, allData) {'.$a->javascript().'};';
	}
	
	return $script.'</script>';
}

/**
* Actions are used by the Input system to handle success, error and other messages.
*
* To make an Action you first need to create a class that extends Action. This class needs to override the $name propertie and javascript method.
* Then you need to use Register_Action() to let the page know that this action is available.
*/
class Action {
	/**
	* The name that the action is referenced by.
	*/
	public $name = 'Blank';
	
	/**
	* The method that returns a javascript string that executes the action.
	*
	* Magic variables:
	* 	- element: The element that this action was called on.
	* 	- data: The data that was sent with this action.
	* 	- allData: The entire message.
	*/
	function javascript() {
		
	}
}

?>