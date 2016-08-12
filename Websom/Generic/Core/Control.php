<?php
/**
* \ingroup TemplateClasses
*
* Information: 
*
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
* \breif This is the template class for all 'Controls'.
*
* To create custom controls you would simply extend the Control class and override the needed methods.
*/
class Control {
	public $Is_Control = true;
	public $owner = "None";
	public $name = "Untitled_Control";
	/**
	* \breif This method is called by websom to get the control structure.
	*/
	public function get(){
		return null;
	}
	/**
	* \breif This method is used by object for sorting based on control values
	*
	* Information:
	*	- Return type: Data_Finder
	*/
	function filter($controlValue) {
		return new Data_Finder();
	}
	/**
	* \breif This method will be called expecting a value that will be inserted into the input on the page
	*
	* Information:
	*	- Return type: Mixed value
	*/
	public function load($controlValue) {
		$newControlValue = $controlValue;
		return $newControlValue;
	}
}
?>