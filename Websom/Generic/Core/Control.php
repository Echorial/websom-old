<?php
/**
* \ingroup TemplateClasses
* Information: 
*
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
* \brief This is the template class for all 'Controls'.
*
* To create custom controls you would simply extend the Control class and override the needed methods.
*/
class Control {
	public $Is_Control = true;
	public $owner = "None";
	public $name = "Untitled_Control";
	
	/**
	* This is called when the Control_Structure is storing the value into a table.
	*/
	public function to($value) {
		return json_encode($value);
	}
	
	/**
	* This is called to deserialize a value stored in the database.
	*/
	public function from($value) {
		return json_decode($value);
	}
	
	/**
	* \brief This method is called by websom to get the control structure.
	*/
	public function get(){
		return null;
	}
	/**
	* \brief This method is used by object for sorting based on control values
	*
	* Add to the finder reference.
	*
	* Information:
	*	- Return type: void
	*/
	function filter($controlValue, &$finder, $column) {
		
	}
	/**
	* \brief This method will be called expecting a value that will be inserted into the input on the page
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