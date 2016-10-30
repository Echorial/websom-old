<?php
/**
* \ingroup TemplateClasses
*
* Information: 
*
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
* \brief This is the template class for all 'Views'.
*
* Views are very useful for displaying information from a MySql database.
*
* Read the method descriptions for information on how View works.
*
* To create custom views you would simply extend the View class and override the needed methods.
*/
class View {
	public $Is_View = true;
	public $owner = 'none';
	public $name = 'Untitled_View';
	
	/**
	* This method is called requesting an html structured string about how the data container will be displayed.
	*
	* The $rows are where the rows of data will be displayed.
	* The $columns are where the sorting controls will be displayed.
	*
	* Information:
	* 	- Return: string
	*/
	public function full($rows, $columns) {
		return '';
	}
	
	/**
	* This method is called requesting the html structure for each row.<br>
	* Example return value: <br>
	* \code
	* return "<div> Name: ".$row['name'].", Description: ".$row['desc']." </div>"
	* \endcode
	* The above example will return a div containg the `name` and `desc` data from the MySql database
	*
	* Information:
	* 	- Return: string
	*/
	public function sub($row) {
		return '';
	}
}
?>