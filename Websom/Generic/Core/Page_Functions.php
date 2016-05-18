<?php
function Go($Location){
	header("Location: ".$Location);
}

//Does not realy fit in\\ 
function is_array_associative($a) {
	if (is_array($a)) {
		return array_keys($a) !== range(0, count($a) - 1);
	}else{
		return false;
	}
}
?>