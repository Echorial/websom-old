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
* 
* Events:
* 	- "edit"(mixed $data(The control value after Control::to is called): This is called after a row is edited.
* 	- "create"(mixed $data(The control value after Control::to is called): This is called after the row is created and .
* 
*/
class Control extends Hookable {
	public $Is_Control = true;
	public $owner = "None";
	public $name = "Untitled_Control";
	
	/**
	* The parent Control_Structure
	*/
	public $cs;
	
	/**
	* If true this control will edit the row.
	*/
	public $_action_edit = true;
	/**
	* If true this control will insert a value into the row.
	*/
	public $_action_create = true;
	//public $_action_sort = true;
	
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
	
	/**
	* Override this to validate a value. Return true if its ok, or return an error string if not.
	* 
	* @param $value The value that needs to be validated.
	* @param $oldValue If the control is being used in an edit the stored value of the column will be pased.
	*/
	public function validate($value, $oldValue = null) {
		return true;
	}
}
?>