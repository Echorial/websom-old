<?php
/**
* \defgroup Data Data
* 
* The Data class is a new tool that will replace the Object data interfacing.
* 
* This tool only contains a MySql database table structure object for now.
* 
*/

/**
* \ingroup Data
*/
class Data {
	
	/**
	* This method is used to construct a Data_Structure object.
	* See Data_Structure::__construct.
	*/
	function structure($columns) {
		
	}
}

/**
* See Data::structure() for info about this class.
*/
class Data_Structure {
	
	/**
	* @param array $columns The $columns should be an array that is structured like so
	* \code
	* [
	* 	"column name" => [ //The column name
	* 		"type" => "type of value, for instance 'VARCHAR(123)'", //The type of information that will be stored in the column.
	* 		"default" => "blank", //Default value
	* 		"extra query string that will be inserted into the MySql query" => true //The key in this line is the string that will be inserted. It could be something like "NOT NULL"
	* 	]
	* ]
	* \endcode
	* 
	*/
	public function __construct($columns) {
		
	}
}

?>