<?php

$Client_Lists = [
	'Action' => []
];

function Register_Action($action) {
	global $Client_Lists;
	
	array_push($Client_Lists['Action'], $action);
}


function Get_Client_Scripts() {
	global $Client_Lists;
	
	$script = '<script>
	
	';
	
	foreach ($Client_Lists['Action'] as $a) {
		$script .= 'window["Action_'.$a->name.'"] = function (form, data, allData) {'.$a->javascript().'}';
	}
	
	return $script.'</script>';
}


class Action {
	public $name = 'Blank';
	
	function javascript() {
		
	}
}

class Input {
	
}

?>