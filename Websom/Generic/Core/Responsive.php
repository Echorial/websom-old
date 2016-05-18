<?php
$Responives = array();
function Get_Responsive ($responsive) {
	global $Responives;
	array_push($Responives, $responsive);
}

function Responsive_Included($response) {
	global $Responives;
	foreach($Responives as $resp)
		if ($response == get_class($resp)) return true;
	return false;
}

function Websom_Check_Responsive () {
	global $Responives;
	if (count($_POST) == 0) return false;
	if (!isset($_POST['responiveid'])) return false;
	if ($_POST['responiveid'] > count($Responives) OR $_POST['responiveid'] < 0) return false;
	$__POST = $_POST;
	unset($__POST['responiveid']);
	$Responives[$_POST['responiveid']-1]->response($__POST);
}

function Get_Responsive_Scripts() {
	global $Responives;
	$scripts = '<script>var responsives = [';
	foreach ($Responives as $responsive) {
		$scripts .= 'function (respond) {'.$responsive->javascript().'},';
	}
	return rtrim($scripts, ',').'];</script>';
}

class Responsive {
	function javascript() {
		
	}
	function response() {
		
	}
}
?>