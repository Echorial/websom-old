<?php
class Control {
	public $Is_Control = true;
	public $owner = "None";
	public $name = "Untitled_Control";
	public function get(){
		return null;
	}
	function filter($val) {
		return $val;
	}
	public function load($val) {
		return $val;
	}
}
?>