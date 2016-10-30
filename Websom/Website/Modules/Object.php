<?php
/**
* \defgroup Data Database tools
* These tools provide a very quick and easy way to create great database viewing and manipulation thats responsive and fast.
*/

//Object module/manager is the core of websom

//Set the appropriate config variables up

$Object_Config;
$Connection;

//Make sure object has all config files needed
//This is called by websom to include the needed files
function Object_Config_Send () {

	//Websom will search for the config file in the Config folder located at the root directory
	return array("MySqlCredentials");
}
//This is called by websom to give the config data to the module
function Object_Config_Get ($Configs) {
	//The [$Configs] is an array of config data
	//Since we only asked for one config file we will store it in the [$Config]
	global $Object_Config;
	$Object_Config = $Configs[0]['MySqlCredentials'];
}
//This is called by websom if a config file failed to load
function Object_Config_Fail ($Config) {
//[$Config] will be the file that failed to load

//If you return true websom will continue loading the page. But if you return a String websom will create the config ini file with the string in it
return '
Server_Name = "localhost"

Select_Username = "Select"
Select_Password = "websom"

Insert_Username = "Insert"
Insert_Password = "websom"

Update_Username = "Update"
Update_Password = "websom"

Structure_Username = "Structure"
Structure_Password = "websom"

Remove_Username = "Remove"
Remove_Password = "websom"

Database_Name = "websom"
';
}
//Websom calls this at the start, asking for a status of true/false. For instance this will check if it is able to connect to a database then it will return true, if it is unable it will return false
function Object_Status(){
	global $Object_Config;
	global $Connection;
	$Rtn = true;
	$Connection['Select'] = '';
	$Connection['Insert'] = '';
	$Connection['Update'] = '';
	$Connection['Structure'] = '';
	$Connection['Remove'] = '';	
	//Check connection
	foreach ($Connection as $Name => $ConnetionTry){
		$ConnectionTry = new mysqli($Object_Config['Server_Name'], $Object_Config[$Name.'_Username'], $Object_Config[$Name.'_Password'], $Object_Config['Database_Name']);
		if ($ConnectionTry->connect_error) {
			$Rtn = false;
		}
		$Connection[$Name] = $ConnectionTry;
	}

	return $Rtn;
}





//Websom calls this asking for the storage structure (in sql)
function Object_Structure () {
	return [
		'Storage' => '`skey` VARCHAR(256) NOT NULL , `sstorage` TEXT NOT NULL'
	];
}


/**
* \ingroup Data
* This class is used to search and find data quickly as well as safely.
* <br>
* Example usage:
*
* \code
* $finder = new Data_Finder(); //Create a new finder
* $finder->where("", "id", "=", 20);  //Add a where query to the finder
* 
* $found = Data_Select(GetTable("myModule.myTable"), $finder); //Search the myTable table for a row that has an id = 20
*
* echo $found[0]['name']; //echo out the first results name
*
* \endcode
*
* Information:
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
*/
class Data_Finder {	//TODO: Make the history and query generate at getPrepared rather than durring the where, between, order
	/**
	* The $all option will determine whether or not to find all rows.<br>
	* The $columns option will determine what columns are selected.
	*/
	public function __construct($all = false, $_columns = '*') {
		if ($all) $this->query = 'WHERE 1';
		$this->columns = $_columns;
	}
	public $columns = '*';
	public $query = '';
	public $types = '';
	public $values = array();
	public $_orderHistory = array();
	public $_whereHistory = array();
	public $_betweenHistory = array();
	public $_groupHistory = array();
	
	private $wrapa = false;
	
	/**
	* This function will add a where query to the finder.
	* 
	* Params:
	* 	- $separator: Use this when mixing statements together. For instance $finder->where("AND", "anotherColumn", "=", "both")
	* 	- $columnName: Name of column for the where statement.
	* 	- $operator: The logical operator or statement used in the where statement.
	* 	- $columnValue: The value to check for.
	* 	- $keepValueInQuery: If the finder should just insert the value into the query or if it should use SQL Injection protection and use the value literaly.
	*
	*/
	public function where($separator, $columnName, $operator, $columnValue, $keepValueInQuery = false) {
		array_push($this->_whereHistory, array($separator, $columnName, $operator, $columnValue, $keepValueInQuery));
		if (strpos($this->query, 'WHERE') === false) $this->query .= ' WHERE ';
		if ($keepValueInQuery) {
			$this->query .= $separator.' `'.$columnName.'` '.$operator.' '.$columnValue.' ';
			return true;
		}
		$this->query .= $separator.' `'.$columnName.'` '.$operator.' ? ';
		$type = '';
		$types['string'] = 's';
		$types['integer'] = 'i';
		$types['boolean'] = 'i';
		$types['double'] = 'd';
		if (isset($types[gettype($columnValue)])) {
			$type = $types[gettype($columnValue)];
		}else{
			Error('Data', 'Cannot find type "'.gettype($columnValue).'" in database.', true);
		}
		$this->types .= $type;
		
		array_push($this->values, $columnValue);
		return true;
	}
	
	/**
	* This function will check if the column value is BETWEEN the two given values `$cv1`, `$cv2`.
	*
	*/
	public function between($separator, $columnName, $cv1, $cv2) {
		array_push($this->_betweenHistory, array($separator, $columnName, $cv1, $cv2));
		if (strpos($this->query, 'WHERE') === false) $this->query .= ' WHERE ';
		$this->query .= $separator.' `'.$columnName.'` BETWEEN ? AND ? ';
		$type = '';
		$types['string'] = 's';
		$types['integer'] = 'i';
		$types['boolean'] = 'i';
		$types['double'] = 'd';
		if (isset($types[gettype($cv1)])) {
			$type = $types[gettype($cv1)];
		}else{
			Error('Data', 'Cannot find type "'.gettype($cv1).'" in database.', true);
		}
		$this->types .= $type.$type;
		
		array_push($this->values, $cv1);
		array_push($this->values, $cv2);
	}
	
	/**
	* How to order the results.
	*
	* <div class="warning">This does not use prepared statements, so make sure the input is safe.</div>
	*
	*/
	public function order($column, $o) {
		array_push($this->_orderHistory, array($column, $o));
		$this->query .= ' ORDER BY `'.$column.'` '.$o.' ';
	}
	
	/**
	* What column to group by.
	*
	* <div class="warning">This does not use prepared statements, so make sure the input is safe.</div>
	*
	*/
	public function group($column) {
		array_push($this->_groupHistory, array($column));
		$this->query .= ' GROUP BY '.$column.' ';
	}
	
	/**
	* This will start and stop `(` and `)`.<br>
	* Example:
	* \code
	* $finder->wrap();
	* $finder->where("", "ok", "=", "no");
	* $finder->wrap(); //The query now looks like this: (`ok` = ?)
	* \endcode
	*/
	public function wrap($separator = null) {
		$this->query .= ($this->wrapa) ? ') ' : $separator.' (';
		$this->wrapa = !$this->wrapa;
	}
	
	public function getPrepared() {
		foreach ($this->values as $key => $value) {
			$this->values[$key] = &$this->values[$key];
		}
		return array(array_merge(array($this->types), $this->values), $this->query);
	}
	
	/**
	* Use this to insert a plain string into the MySql query.
	*
	* \param string $query The string that will be appended to the query.
	*/
	public function addQuery($query) {
		$this->query .= ' '.$query.' ';
	}
	
	/**
	* This will merge two finders together.
	* <div class="note">A new version of finder will be more stable with wrap and other statements.</div>
	* <div class="warning">This will not merge any wraped or plain statements.</div>
	*
	* Information:
	* 	- Return: void
	* 	- Author: Echorial
	* 	- Date: Unkown
	* 	- Version: 1.0
	*/
	public function merge($otherFinder) {
		foreach ($otherFinder->_whereHistory as $where)
			$this->where($where[0], $where[1], $where[2], $where[3], $where[4]);
		foreach ($otherFinder->_betweenHistory as $between)
			$this->between($between[0], $between[1], $between[2], $between[3]);
		foreach ($otherFinder->_orderHistory as $order)
			$this->order($order[0], $order[1]);
		foreach ($otherFinder->_groupHistory as $group)
			$this->group($group[0]);
	}
}

/**
* \ingroup Data
*
* This will create a finder. This is nice for quick inline finding.
* Example usage:
* \code
* $finder = Quick_Find(
* 	[
*		["column", "=", "value"],
*		["anotherColumn", "=", "anotherValue"]
*	]
* );
* \endcode
* Information:
* 	- Return: Data_Finder
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
*/
function Quick_Find($findsArray){
	$return = new Data_Finder();
	foreach($findsArray as $i => $a)
		$return->where(($i == 0) ?'':'AND', $a[0], $a[1], $a[2]);
	return $return;
}

/*

*/

/**
* \ingroup Data
*
* The Data_Builder is used for building lists of column value pairs, to be inserted or updated in a MySql database.
*
* Example usage:
* \code
* $builder = new Data_Builder();
* $builder->add("name", "John Smith");
* $builder->add("age", 32);
* $rowId = Data_Insert(GetTable("Names.people"), $builder); //Inserts a new row with the `name` = `John Smith` and the `age` = `32`
* \endcode
*
* Information:
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
*/
class Data_Builder {
	public $types = '';
	public $values = array();
	/**
	* This will add the column, value pair to the builder.
	*
	* Information:
	* 	- Author: Echorial
	* 	- Date: Unkown
	* 	- Version: 1.0
	*/
	public function add($columnName, $columnValue) {
		$type = '';
		$types['string'] = 's';
		$types['integer'] = 'i';
		$types['boolean'] = 'i';
		$types['double'] = 'd';
		if (isset($types[gettype($columnValue)])) {
			$type = $types[gettype($columnValue)];
		}else{
			Error('Data', 'Cannot store type "'.gettype($columnValue).'" in database. Column: '.$columnName, true);
		}
		$this->types .= $type;
		
		array_push($this->values, array($columnName, $columnValue));
	}

	/**
	* Create key/value array from this builder.
	*/
	public function arrayify() {
		$rtn = [];
		
		foreach ($this->values as $v)
			$rtn[$v[0]] = $v[1];
		
		return $rtn;
	}
	
	public function getPrepared($type = 0) {
		$t1 = '(';
		$t2 = ')';
		if ($type == 1){
			$t1 = '';
			$t2 = '';
		}
		$q1 = $t1;
		$q2 = $t1;
		$vals = array();
		foreach ($this->values as $key => $value) {
			if ($type == 0) {
				$q1 .= '`'.$this->values[$key][0].'`, ';
				$q2 .= '?, ';
			}else{
				$q1 .= '`'.$this->values[$key][0].'` = ?, ';
			}
			$vals[$key] = &$this->values[$key][1];
		}
		$q1 = rtrim($q1, ', ').$t2;
		$q2 = rtrim($q2, ', ').$t2;
		if ($type == 0) $q2 = 'VALUES'.$q2;
		return array(array_merge(array($this->types), $vals), $q1.' '.$q2);
	}
}

/**
* \ingroup Data
*
* This will select data from the `$table` and search using the `$finder`.
*
* Information:
* 	- Return: Array
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
*/
function Data_Select ($table, $finder, $selects = '*') {
	global $Connection;

	$find = $finder->getPrepared();
	
	$query = $find[1];
	$query = 'SELECT '.$finder->columns.' FROM '.$table.' '.$query;
	if ($prepared = $Connection['Select']->prepare($query)) {
	}else{
		echo Error('Data', $Connection['Select']->error);
		return false;
	}

	if (count($finder->values) > 0)
		call_user_func_array(array($prepared, 'bind_param'), $find[0]);
	$prepared->execute();
	$result = $prepared->get_result();
	$return = array();
	while($row = $result->fetch_array(MYSQLI_ASSOC)) {
		array_push($return, $row);
	}
	$prepared->Close();

	return $return;
}
/*
function Data_Select($TableName, $WhereArray, $WhereArrayValues, $ExtraQuery=""){
	global $Connection;

	$Query = "SELECT * FROM `".$TableName."` WHERE ";
	foreach ($WhereArray as $Where) {
		$WhereGet = explode(' ', $Where);
		$Query .= "`".$WhereGet[0]."` ".$WhereGet[1]." ? AND ";
	}
	$Query = rtrim($Query, ' AND ');
	$Query .= " ".$ExtraQuery;

	if ($Prepared = $Connection['Select']->prepare($Query)) {
	}else{
		echo Error('Data', $Connection['Select']->error);
	}
	$Iterator = 0;
	$S_ = "";
	foreach ($WhereArrayValues as $Value) {
		$WhereArrayValues[$Iterator] = &$WhereArrayValues[$Iterator];
		$S_ .= "s";
		$Iterator += 1;
	}
	if (count($WhereArray) > 0 AND count($WhereArray) == count($WhereArrayValues)){
		call_user_func_array(array($Prepared, 'bind_param'), array_merge(array($S_), $WhereArrayValues));
	}
	$Prepared->execute();
	$Result = $Prepared->get_result();
	$Return = array();
	while($row = $Result->fetch_array(MYSQLI_ASSOC)) {
		array_push($Return, $row);
	}
	$Prepared->Close();

	return $Return;
}*/

/**
* \ingroup Data
*
* This will insert the `$builder` into the `$tableName`.
*
* Information:
* 	- Return: int(id of the inserted row)
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
*/
function Data_Insert($tableName, $builder) {
	global $Connection;
	$insert = $builder->getPrepared();
	$insertQuery = $insert[1];
	
	$query = 'INSERT INTO `'.$tableName.'` '.$insertQuery;
	
	if ($prepared = $Connection['Insert']->prepare($query)) {
	}else{
		echo Error('Data', $Connection['Insert']->error);
		return false;
	}

	call_user_func_array(array($prepared, 'bind_param'), $insert[0]);
	$prepared->execute();
	$prepared->Close();

	return  $Connection['Insert']->insert_id;
}

/**
* \ingroup Data
*
* This will delete the found row(s) in the `$tableName`.
*
* Information:
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
*/
function Data_Delete($tableName, $finder) {
	global $Connection;
	$delete = $finder->getPrepared();
	
	$query = 'DELETE FROM `'.$tableName.'` '.$delete[1];

	if ($prepared = $Connection['Remove']->prepare($query)) {
	}else{
		echo Error('Data', $Connection['Remove']->error);
		return false;
	}

	call_user_func_array(array($prepared, 'bind_param'), $delete[0]);
	$prepared->execute();
	$prepared->Close();

	return true;
}

/*
function Data_Insert($TableName, $InsertArray, $InsertArrayValues){
	global $Connection;

	$Query = "INSERT INTO `".$TableName."` (".ArrayToString($InsertArray).") VALUES(".ArrayToStringValue($InsertArrayValues, '?').")";

	if ($Prepared = $Connection['Insert']->prepare($Query)) {
	}else{
		echo Error('Data', $Connection['Insert']->error);
	}
	$Iterator = 0;
	$S_ = "";
	foreach ($InsertArrayValues as $Value) {
		$InsertArrayValues[$Iterator] = &$InsertArrayValues[$Iterator];
		$S_ .= "s";
		$Iterator += 1;
	}
	call_user_func_array(array($Prepared, 'bind_param'), array_merge(array($S_), $InsertArrayValues));

	$Prepared->execute();
	$Prepared->Close();
	return $Connection['Insert']->insert_id;
}*/

/**
* \ingroup Data
*
* This will update the found row(s) with the `$builder` in the `$tableName`.
*
* Information:
* 	- Return: boolean
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
*/
function Data_Update($tableName, $builder, $finder) {
	global $Connection;
	
	$find = $finder->getPrepared();
	$findQuery = $find[1];
	
	$update = $builder->getPrepared(1);
	$updateQuery = $update[1];

	
	$query = 'UPDATE `'.$tableName.'` SET '.$updateQuery.' '.$findQuery;
	
	if ($prepared = $Connection['Update']->prepare($query)) {
	}else{
		echo Error('Data', $Connection['Update']->error);
		return false;
	}


	$update[0][0] .= $find[0][0];
	unset($find[0][0]);
	call_user_func_array(array($prepared, 'bind_param'), array_merge($update[0], $find[0]));
	$prepared->execute();
	$prepared->Close();

	return true;
}
/*
function Data_Update($TableName, $UpdateArray, $UpdateArrayValues, $ExtraQuery=""){
	global $Connection;

	$Query = "UPDATE `".$TableName."` SET ";
	foreach ($UpdateArray as $Update) {
		$UpdateGet = $Update;
		$Query .= "`".$UpdateGet."` = ?, ";
	}
	$Query = rtrim($Query, ', ');
	$Query .= " ".$ExtraQuery;
	if ($Prepared = $Connection['Update']->prepare($Query)) {
	}else{
		echo Error('Data', $Connection['Update']->error);
		return false;
	}
	$Iterator = 0;
	$S_ = "";
	foreach ($UpdateArrayValues as $Value) {
		$UpdateArrayValues[$Iterator] = &$UpdateArrayValues[$Iterator];
		$S_ .= "s";
		$Iterator += 1;
	}
	call_user_func_array(array($Prepared, 'bind_param'), array_merge(array($S_), $UpdateArrayValues));

	$Prepared->execute();
	$Prepared->Close();
	return true;
}*/

/**
* \ingroup Data
*
* This will return true or false depending on whether or not it found and rows.
*
* Information:
* 	- Return: boolean
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
*/
function Data_Find($tableName, $finder){
	$found = Data_Select($tableName, $finder);
	
	if (is_array($found)) {
		if (count($found) > 0){
			return true;
		}else{
			return false;
		}
	}else{
		return false;
	}
}

/**
* \ingroup Data
*
* This will create the the table in the Websom database with the given columns.
*
* <div class="warning">Do not use this. If you need tables use the module structure.</div>
*
* Information:
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
*/
function Structure_Create($TableName, $Columns){
	global $Connection;

	$Query = 'CREATE TABLE IF NOT EXISTS '.$TableName.'('.$Columns.')';

	if ($Connection['Structure']->query($Query) === TRUE) {
		return true;
	} else {
		echo  Error('Data', $Connection['Structure']->error);
		return false;
	}
}


function ArrayToString($Array){
	$Rtn = '';
	foreach($Array as $Value){
		$Rtn .= $Value.', ';
	}
	return rtrim($Rtn, ', ');
}

function ArrayToStringValue($Array, $ValueSet){
	$Rtn = '';
	foreach($Array as $Value){
		$Rtn .= $ValueSet.', ';
	}
	return rtrim($Rtn, ', ');
}

class Group {
	public function __construct($setName, $_values = array()){
		$this->name = $setName;
		$this->values = $_values;
	}
	public $name = 'Untitled';
	public $structure = array();
	public function Add_Input($options, $val = '') {
		array_push($this->structure, array($options, $val));
	}
	public function getInputs(){
		$vals = '';
		$Rtn = '';
		$id = -1;
		foreach ($this->structure as $input) {
			$id++;

			$Rtn .= Add_Input('Group_'.$this->name.'_'.$id, $input[0], $input[1]);
		}
		return $Rtn;
 	}
}

function Object_Start_Input($Module, $extra = ''){
	return Start_Input_Area($Module, 'Loose', $extra);
}

function Object_Add_Group($Group){
	return $Group->getInputs();
}

function Object_Add_Input($Name, $Options, $value = null){
	return Add_Input($Name, $Options, $value);
}

function Object_End_Input(){
	return End_Input_Area();
}

function Object_Start_List($name, $addText, $addClass, $removeClass){
	return '<dynamic id="L'.$name.'" index="0" addbutton="'.$addText.'" addbuttonclass="'.$addClass.'" removebuttonclass="'.$removeClass.'" nameset="List_'.$name.'_%_"><dynamictemplate>';
}

function Object_Start_List_Add () {
	return '<listadd class="listaddlower">';
}

function Object_End_List_Add () {
	return '</listadd>';
}

function Object_Structure_List($Structure){
	//Very useful
	return $Structure;
}

function Object_End_List(){
	return '</dynamic></dynamictemplate>';
}

function Object_Get_Input() {
	$Result = Get_Input_Area();
	
	$Values;
	
	$StructuredValues = array();
	$checker = false;
	if ($Result !== false){
		foreach ($Result as $Name => $Value){
			if (strpos($Name, 'List') !== false) {
				$listName = explode('_', $Name);
				if (!array_key_exists($listName[1], $StructuredValues)){
					$StructuredValues[$listName[1]] = array('type' => 'list', 'values' => array());
				}
				$Name = str_replace('List_'.$listName[1].'_'.$listName[2].'_', '', $Name);
			}
			
			if (strpos($Name, 'Group') !== false){
				$listName = explode('_', $Name);
				if (!array_key_exists($listName[1], $StructuredValues)){
					$StructuredValues[$listName[1]] = array('type' => 'group', 'values' => array());
				}
				
				$T = 0;
				if ($listName[2] == 0){
					$T = 1;
				}
				array_push($StructuredValues[$listName[1]]['values'], array('type' => $T, 'value' => $Value));
			}else{
				if (!array_key_exists($Name, $StructuredValues)){
					$StructuredValues[$Name] = array('type' => 'plain', 'values' => array());
				}
				array_push($StructuredValues[$Name]['values'], array('type' => 'PlainValue', 'value' => $Value));
			}
		}
		
		//Set up values into Post like array
		foreach($StructuredValues as $Name => $Properties){
			if ($Properties['type'] == 'list'){
				
				foreach($Properties['values'] as $Value){
					if ($Value['type'] == '1'){
						$Values[$Name] .= '^|';
						$Values[$Name] .= $Value['value'].'%|';
					}else if($Value['type'] == '0'){
						$Values[$Name] .= $Value['value'].'%|';
					}else if($Value['type'] == 'PlainValue'){
						$Values[$Name] .= '^|';
						$Values[$Name] .= $Value['value'];
					}
				}
			}else if ($Properties['type'] == 'group'){
				foreach($Properties['values'] as $Value){
					$Values[$Name] .= $Value['value'].'%|';
				}
			}else if ($Properties['type'] == 'plain'){
				$Values[$Name] = $Properties['values'][0]['value'];
			}
			if (gettype($Values[$Name]) == 'string')
				$Values[$Name] = trim(trim($Values[$Name], '^|'), '%|');
		}
	}else{
		$Values = false;
	}

	return $Values;
}

/**
* \ingroup Data
*
* This will return the real table for the ModuleName.ModuleTable pair.
* 
* Example:
* \code
* $realTable = GetTable("MyModule.moduleTable"); //May return something like "m6_moduleTable"
* \endcode
*
* Information:
* 	- Return: string
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
*/
function GetTable($mod) {
	$mod = explode('.', $mod);
	if (count($mod) < 2)
		return false;
	return GetModuleReference($mod[0]).'_'.$mod[1];
}

/**
* \ingroup Data
*
* Does the opposite of GetTable().
*
* Information:
*  	- Return: string
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
*/
function UnTable($mod) {
	$mod = explode('_', $mod);
	return array_search($mod[0], GetModuleReference()).'.'.$mod[1];
}

/**
* \ingroup Data
* \deprecated Use the new `Control_Structure` class.
* Data_Structure's are used by the Data_Output_* and Data_Input_* tools for quick and easy MySql viewing and manipulation.
*
* Example usage:
* \code
* $ds = new Data_Structure("PersonLogger.people", "Inserted!"); //The second param is the success message
* $ds->addControl(new PersonLogger_PersonName(), "name"); //Add a control for the column `name`
* $ds->addControl(new Submit("Add Person")); //Add a button for submiting the form
* 
* echo Data_Input_Create($ds); //Now you just created a form that will allow users to add a person to the PersonLogger.people table
* \endcode
*
* Information:
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
*/
class Data_Structure {
	/**
	* Params:
	* 	- `$mod`: The module table path.
	* 	- `$sMsg`: Success message.
	* 	- `$_action`: The action that will be called with a success.
	*
	*/
	public function __construct($mod = "", $sMsg = false, $_action = false){
		$this->table = GetTable($mod);
		if ($this->table === false) $this->table = $mod;
		$this->success = $sMsg;
		$this->action = $_action;
		$this->finder = new Data_Finder();
	}
	public $action = '';
	public $success = '';
	public $table = '';
	public $inputs = array();
	public $html = array();
	public $htmlStructure = '';
	public $sets = array();
	public $finder;
	/**
	*
	* This will add a control to the data structures list of control, column pairs.<br>
	* Params:
	* 	- `$conrol`: The control object to be used.
	* 	- `$column`: The colum that the control will be associated with. If no value is provided the control will not be associated with a column.
	* 	- `$columnIsName`: If this is true the `$column` will be a name and the control wont interact with the database.
	*
	* Information:
	* 	- Return: void
	* 	- Author: Echorial
	* 	- Date: Unkown
	* 	- Version: 1.0
	*/
	public function addControl ($control, $column = false, $columnIsName = false) {
		array_push($this->inputs, array($control, $column, $columnIsName));
	}
	
	/**
	* This will add a column, value pair to the Data_Structure for when the database is changed.
	*
	* Information:
	* 	- Return: void
	* 	- Author: Echorial
	* 	- Date: Unkown
	* 	- Version: 1.0
	*/
	public function addSet ($col, $val) {
		array_push($this->sets, array($col, $val));
	}
	
	public function addHtml($html) {
		$this->html[count($this->inputs)-1] = $html;
	}
}

class Data_Input {
	public $column = '';
	
}

/**
* \ingroup Data
* \deprecated Use the new `Control_Structure` class.
* This function will return a string containg a form that will when submited create a row in the $dataStructure's table with the $dataStructure's controls
*
* Example usage:
* \code
* class myModule_Controls_Name extends Control {
*	function get() {
*		return ['type' => 'text', 'count' => '5 255', 'placeholder' => 'Name']; //Return a text control with a character min of 5 and max of 255
* 	}
* }
* 
* $ds = new Data_Structure("myModule.myTable", "Row inserted!!!");
* $ds->addControl(new myModule_Controls_Name(), "name"); //Adds the custom control
* $ds->addControl(new Submit("Add row")); //Add an empty control just for submiting the form
*
* echo Data_Input_Create($ds);
*
* \endcode
*
* The form returned should look something like this:
*
* <div><form>
* 	<input type="text" count="5 255" placeholder="name"></input><input type="submit" value="Add row"></input>
* </form></div>
*
* When the form is sumbited a row with the `name` column = to `name control's value` will be inserted.
*
* Information:
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
*/

function Data_Input_Create($dataStructure) {
	//Do generating
	$inputId = 0;
	$output = '';
	
	$output .= Object_Start_Input(base64_encode($dataStructure->table));

	$output .= Data_Controls_Stringify($dataStructure);
	
	//Do checking
	$userInput = Object_Get_Input();

	if ($userInput !== false){
		$rtns = Data_Structure_Cook($dataStructure, $userInput);
		$builder = $rtns[0];
		$valArray = $rtns[1];
		
		$override = false;
		if (isset($dataStructure->onSuccess))
			$override = !call_user_func($dataStructure->onSuccess, $valArray);
		
		if (!$override) {
			$id = Data_Insert($dataStructure->table, $builder);
			if (isset($dataStructure->onInsert))
				call_user_func($dataStructure->onInsert, $id);
			Data_Send_Messages($dataStructure);
		}
		
	}
	
	
	$output .= Object_End_Input();
	
	return $output;
	
	
}

function Data_Structure_Cook ($dataStructure, $userInput) {
	$builder = new Data_Builder();
	$valArray = array();
	foreach($userInput as $key => $currentInput) {
		//Gathering dust
		/*$bakedInput = explode('^|', $rawInput);
		foreach ($bakedInput as $listKey => $listValue){
			$bakedInput[$listKey] = explode('%|', $listValue);
		}*/
		if ($dataStructure->inputs[$key-1][1] !== false) {
			$columnKey = $dataStructure->inputs[$key-1][1];
			if ($dataStructure->inputs[$key-1][2] != true) {
				$setInput = $currentInput;
				$setInput = $dataStructure->inputs[$key-1][0]->filter($currentInput);
				$builder->add($columnKey, $setInput);
			
			}
			$valArray[$columnKey] = $currentInput;
		}
	}
	foreach ($dataStructure->sets as $set)
		$builder->add($set[0], $set[1]);
	return array($builder, $valArray);
}

/**
* \ingroup Data
* \deprecated Use the new `Control_Structure` class.
* This function will return a string containg a form that will when submited call a function that you can hook into.
*
* Example usage:
* \code
* class myModule_Controls_Name extends Control {
*	function get() {
*		return ['type' => 'text', 'count' => '5 255', 'placeholder' => 'Name']; //Return a text control with a character min of 5 and max of 255
* 	}
* }
* 
* $ds = new Data_Structure("myModule.myTable", "Submited!!!");
* $ds->addControl(new myModule_Controls_Name(), "name"); //Adds the custom control
* $ds->addControl(new Submit("Add row")); //Add an empty control just for submiting the form
*
* $ds->onSuccess = function ($values) {
*	InputSend(InputSuccess('The name submited is '.$values['name']));
* }
*
* echo Data_Input_Plain($ds);
*
* \endcode
*
* When the form is submited if the input is valid the `onSuccess` callback is fired with the input passed as a param.
* 
* Information:
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
*/
function Data_Input_Plain($dataStructure) {
	//Do generating
	$inputId = 0;
	$output = '';
	
	$output .= Object_Start_Input(base64_encode($dataStructure->table));

	$output .= Data_Controls_Stringify($dataStructure);
	
	//Do checking
	$userInput = Object_Get_Input();

	if ($userInput !== false){
		$valArray;
		foreach($userInput as $key => $currentInput) {
			if ($dataStructure->inputs[$key-1][1] !== false) {
				$columnKey = $dataStructure->inputs[$key-1][1];
				$valArray[$columnKey] = $currentInput;
			}
		}
		call_user_func($dataStructure->onSuccess, $valArray);
	}
	
	
	$output .= Object_End_Input();
	
	return $output;
	
	
}

function Data_Input_Create_Start($Data_Structure){
	
}

//Easy database data editor tools
/**
* \ingroup Data
* \deprecated Use the new `Control_Structure` class.
* This function works much the same as Data_Input_Create(), but rather than create a row it will edit the row given.
*
* Example usage:
* \code
* class myModule_Controls_Name extends Control {
*	function get() {
*		return ['type' => 'text', 'count' => '5 255', 'placeholder' => 'Name']; //Return a text control with a character min of 5 and max of 255
* 	}
* }
* 
* $ds = new Data_Structure("myModule.myTable", "Row inserted!!!");
* $ds->addControl(new myModule_Controls_Name(), "name"); //Adds the custom control
* $ds->addControl(new Submit("Save")); //Add an empty control just for submiting the form
*
* echo Data_Input_Edit($ds, 12);
*
* \endcode
*
* The form returned should look something like this:
*
* <div><form>
* 	<input type="text" count="5 255" placeholder="name"></input><input type="submit" value="Add row"></input>
* </form></div>
*
* When the form is sumbited the row 12 will be updated with the new `name` value.
*
* Information:
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
*/
function Data_Input_Edit($dataStructure, $rowId, $loadValues = true) {
	//Do generating
	$inputId = 0;
	$output = '';
	
	$output .= Object_Start_Input(base64_encode($dataStructure->table));
	$finder = new Data_Finder();
	$finder->where('', 'id', '=', $rowId);
	$currentData = Data_Select($dataStructure->table, $finder);
	if (!$loadValues) $currentData = null;
	$output .= Data_Controls_Stringify($dataStructure, $currentData);
	//Do checking
	$userInput = Object_Get_Input();

	if ($userInput !== false){
		$rtns = Data_Structure_Cook($dataStructure, $userInput);
		$builder = $rtns[0];
		$valArray = $rtns[1];
		
		$override = false;
		if (isset($dataStructure->onSuccess))
			$override = !call_user_func($dataStructure->onSuccess, $valArray);

		if (!$override) {
			$finder = new Data_Finder();
			$finder->where('', 'id', '=', $rowId);
			Data_Update($dataStructure->table, $builder, $finder);
			Data_Send_Messages($dataStructure);
		}
		

		
	}
	
	
	$output .= Object_End_Input();
	
	return $output;	
}


/**
* \ingroup Data
* \deprecated Use the new `Control_Structure` class.
* This function will return a html string that allows clients to sort and view information in a MySql table.
*
* Example usage:
* \code
* class myModule_Views_SideBySide extends View {
*	function full($rows, $columns) {
*		return '<div class="clearfix"><div class="col-md-4">'.$columns.'</div><div class="col-md-8">'.$rows.'</div></div>';
* 	}
* 	function sub ($row) {
*		return '<div>Name: '.$row['name'].'</div>';
* 	}
* }
* 
* $ds = new Data_Structure("myModule.myTable");
* $ds->addControl(new Search(), "name"); //Adds the search control to allow searching
* $ds->addControl(new Submit("Search")); //Add an empty control just for submiting the form
*
* echo Data_Output_Sort($ds, new myModule_Views_SideBySide());
*
* \endcode
*
* This will echo out a responsive searchable data viewing area.
*
* Information:
* 	- Author: Echorial
* 	- Date: Unkown
* 	- Version: 1.0
*/
function Data_Output_Sort($dataStructure, $view, $viewOnCreate = true) {
	$containerId = ('for'.$dataStructure->table.'21');
	$controls = Object_Start_Input(base64_encode($dataStructure->table), 'refreshplace="'.$containerId.'"');
	$controls .= Data_Controls_Stringify($dataStructure);
	$reSort = Object_Get_Input();
	$controls .= Object_End_Input();
	
	$startRows = '';
	
	if ($reSort !== false OR $viewOnCreate){
		$extraQuery = '';
		$id = 0;
		$finder = $dataStructure->finder;
		if ($reSort !== false) {
			foreach ($reSort as $id => $val) {
				$finder2 = $dataStructure->inputs[$id-1][0]->data_sort($val, $dataStructure->inputs[$id-1][1]);
				if ($finder2 !== false)
					$finder->merge($finder2);
			}
		}
		$rows = Data_Select($dataStructure->table, $finder);
		$data = '';
		foreach ($rows as $row) {
			$data .= $view->sub($row);
		}
		if ($reSort !== false) {
			Cancel($data);
		}else{
			$startRows = $data;
		}
	}
	
	
	$view = $view->full('#%?^&*&^?%#', $controls);
	if ($view !== false) {
		return str_replace('#%?^&*&^?%#', '<div container id="'.$containerId.'">'.$startRows.'</div>', $view);
	}else{
		return Error('Data', 'View '.$view->name.' not working.');
	}
}

function Data_Output_Plain($mod, $finder, $view) {
	if (strpos($mod, '.') !== false)
		$mod = GetTable($mod);
	$rows = Data_Select($mod, $finder);
	$data = '';
	foreach ($rows as $row) 
		$data .= $view->sub($row);
	$viewDisplay = $view->full('#%?^&*&^?%#', '');
	if ($viewDisplay !== false) {
		return str_replace('#%?^&*&^?%#', $data, $viewDisplay);
	}else{
		return Error('Data', 'View '.$view->name.' not working.');
	}
}

function Data_Send_Messages($ds) {
	if ($ds->action !== false)
		InputSend(InputAction($ds->action, array()));
	if ($ds->success !== false)
		InputSend(InputSuccess($ds->success));
}

/**
* \deprecated Use the new `Control_Structure` class.
*/
function Data_Controls_Stringify($dataStructure, $values = null) {
	$rtn = $dataStructure->htmlStructure;
	$noStruct = ($rtn == '');
	$ind = 0;
	foreach ($dataStructure->inputs as $key => $value) {
		$cKey = $value[1];
		$okToCreateControl = true;
		if ($noStruct) $rtn .= '%'.$cKey.'%';
		$val = '';
		if (isset($values[0][$value[1]])) {
			$val = $values[0][$value[1]];
		}

		$control = $value[0]->get();
		$ind++;
		$inputId = $ind.'objIn';
		
		$OSL = '';
		
		$addbuttonclass = '';
		$removebuttonclass = '';

		if (isset($control['list_add_class'])) $addbuttonclass = $control['list_add_class'];
		if (isset($control['list_remove_class'])) $removebuttonclass = $control['list_remove_class'];
		
		if (isset($control['list'])) $OSL .= Object_Start_List($inputId, 'Add', $addbuttonclass, $removebuttonclass);
		if ($values !== null){

		//FOR: Loading data

		$val = $value[0]->load($val);
			if (isset($control['list'])) {
				$lists = explode('^|', $val);
				foreach ($lists as $listValue) {
					$OSL .= Object_Start_List_Add();
						$groups = explode('%|', $listValue);
						$groupIterator = 0;
						if ($control['type'] == 'group') {
							$controlGroup = new Group($inputId);		
							foreach ($control['inputs'] as $inputKey => $inputValue) {
								$controlGroup->Add_Input($inputValue, $groups[$groupIterator]);
								
								$groupIterator++;
							}
							$OSL .= Object_Add_Group($controlGroup);
						}else{
							$OSL .= Object_Add_Input($inputId, $control, $listValue);
						}
					 $OSL .= Object_End_List_Add();
				}
			}else{
				$okToCreateControl = false;
				$groups = explode('%|', $val);
						$groupIterator = 0;
						if ($control['type'] == 'group') {
							$controlGroup = new Group($inputId);		
							foreach ($control['inputs'] as $inputKey => $inputValue) {
								$controlGroup->Add_Input($inputValue, $groups[$groupIterator]);
								$groupIterator++;
							}
							$OSL .= Object_Add_Group($controlGroup);
						}else{
							$OSL .= Object_Add_Input($inputId, $control, $val);
						}
			}
		
		//FOR-END:
		}
		
		if ($okToCreateControl) {
			if ($control['type'] == 'group'){
				$controlGroup = new Group($inputId);
				foreach ($control['inputs'] as $inputKey => $inputValue) {
					$controlGroup->Add_Input($inputValue);
				}
				$OSL .= Object_Add_Group($controlGroup);
			}else{
				$OSL .= Object_Add_Input($inputId, $control);
			}
		}
		
		if (isset($control['list'])) $OSL .= Object_End_List();
		
		if (isset($dataStructure->html[$key])) {
			$OSL .= $dataStructure->html[$key];
		}

		$rtn = str_replace('%'.$cKey.'%', $OSL, $rtn);
	}
	return $rtn;
}


function Object_Get_Controls () {
	return array("Date_Between");
}

class Date_Between extends Control {
	function __construct($sep = '', $_params = array()) {
		$this->params = $_params;
		$this->sep = $sep;
	}
	public $params;
	public $sep = '';
	function get(){
		$c['type'] = 'group';
		$c['inputs'] = array (
			array_merge($this->params, array('type' => 'date', 'placeholder' => 'Date from')),
			array_merge($this->params, array('type' => 'date', 'placeholder' => 'Date to'))
		);
		return $c;
	}
	function data_sort($value, $column) {
		if (isset($this->params['optional'])) if ($value == '') return false;
		$value = explode('%|', $value);
		$finder = new Data_Finder();
		$finder->between($this->sep, $column, date('Y-m-d', strtotime($value[0])), date('Y-m-d', strtotime($value[1])));
		return $finder;
	}
}

class Search extends Control {
	function __construct($sep = '', $_params = array()) {
		$this->params = $_params;
		$this->sep = $sep;
	}
	public $params;
	public $sep = '';
	function get(){
		$c = $this->params;
		$c['type'] = 'text';
		return $c;
	}
	function data_sort($value, $column) {
		$finder = new Data_Finder();
		$finder->where($this->sep, $column, 'LIKE', '%'.$value.'%');
		return $finder;
	}
}

class Submit extends Control {
	function __construct($text = 'ok') {
		$this->text = $text;
	}
	function get(){
		$control['type'] = "submit";
		
		$control['value'] = $this->text;

		return $control;
	}
}


function InputSuccess($msg) {
	return '"Success": "'.str_replace('"', '\"', $msg).'"';
}

function InputError($msg) {
	return '"Error": "'.str_replace('"', '\"', $msg).'"';
}
function InputRefresh($keepOldMessages = true) {
	InputAction('Refresh', array());
	return '';
}
function InputGo($url, $keepOldMessages = true) {
	InputAction('Go', array('url'=>$url));
	return '';
}


$actions = array();
function InputAction($action, $actionData) {
	global $actions;
	$actions[$action] = $actionData;
	return '';
}

function Get_Actions() {
	global $actions;
	$a = '"Action": [';
	foreach ($actions as $an => $ac) $a .= '{'.rtrim('"name": "'.$an.'",'.arrayToJSON($ac), ',').'},';
	return rtrim($a, ',').']';
}

function arrayToJSON ($a){
	$j = '';
	foreach($a as $k => $d) {
		if (gettype($d) == 'string') $d = '"'.codeQ($d).'"';
		if (gettype($d) == 'array') $d = '{'.arrayToJSON($d).'}';
		$j .= '"'.$k.'":'.$d.',';
	}
	return rtrim($j, ',');
}

function codeQ($string) {
	$string = trim(preg_replace('/\s\s+/', '', $string));
	return str_replace('"', '&quot;', $string);
}

$sending = '';
function InputSend($str) {
	global $sending;
	$sending .= (($sending != '') ? ', ':'').$str;
	if ($sending !== '') $sending = $sending.',';
	Cancel('{'.$sending.Get_Actions().'}');
}


//Storage class
/**
* \ingroup Data
*
* Use this class to store info
*/
class Storage {
	static public function Set($key, $value) {
		if (strlen($key) > 255) Error('Storage', 'Key cannot be larger than 255 characters', true);
		$finder = Quick_Find([['skey', '=', $key]]);
		$tbl = GetTable('Object.Storage');
		$builder = new Data_Builder();
		$builder->add('sstorage', json_encode($value));
		if (Data_Find($tbl, $finder)) {
			Data_Update($tbl, $builder, $finder);
		}else{
			$builder->add('skey', $key);
			Data_Insert($tbl, $builder);
		}
		return true;
	}
	
	static public function Get($key) {
		if (strlen($key) > 255) Error('Storage', 'Key cannot be larger than 255 characters', true);
		$finder = Quick_Find([['skey', '=', $key]]);
		$tbl = GetTable('Object.Storage');
		$found = Data_Select($tbl, $finder);
		if (count($found) == 0) return false;
		return json_decode($found[0]['sstorage'], true);
	}
	
	static public function Remove($key) {
		if (strlen($key) > 255) Error('Storage', 'Key cannot be larger than 255 characters', true);
		$finder = Quick_Find([['skey', '=', $key]]);
		$tbl = GetTable('Object.Storage');
		Data_Delete($tbl, $finder);
		return true;
	}
}

function CmdStorageGet() {
	$cmd = new Console_Command('StorageGet', 'Get value from storage.');
	$cmd->aliases = ['get'];
	$cmd->params = [
		Console_Param('key', 'Key of storage value', 'string')
	];
	$cmd->call = function ($params, $flags) {
		if (strlen($params['key']) > 255) return 'Key is too long. Max characters 255';
		return Storage::Get($params['key']);
	};
	return $cmd;
}

function CmdStorageRemove() {
	$cmd = new Console_Command('StorageRemove', 'Remove a value from storage.');
	$cmd->aliases = ['delete'];
	$cmd->params = [
		Console_Param('key', 'Key of storage value', 'string')
	];
	$cmd->call = function ($params, $flags) {
		if (strlen($params['key']) > 255) return 'Key is too long. Max characters 255';
		Storage::Remove($params['key']);
		return 'Deleted';
	};
	return $cmd;
}

function CmdStorageSet() {
	$cmd = new Console_Command('StorageSet', 'Set a value in storage.');
	$cmd->aliases = ['set'];
	$cmd->params = [
		Console_Param('key', 'Key of storage value', 'string'),
		Console_Param('value', 'Value to set', 'mixed')
	];
	$cmd->call = function ($params, $flags) {
		if (strlen($params['key']) > 255) return 'Key is too long. Max characters 255';
		$value = $params['value'];
		Storage::Set($params['key'], $value);
		return 'Set';
	};
	return $cmd;
}

onEvent('ready', function () {
	Console_Register(CmdStorageGet());
	Console_Register(CmdStorageSet());
	Console_Register(CmdStorageRemove());
	
	$osl = new Object_Sort_Listener();
	Register_Action($osl);
});





class Object_Sort_Listener extends Action {
	public $name = 'Object_Sort_Listener';
	
	function javascript () {
		return '
		
		var parent = $(element).closest(".Object_Sort_Wrap");
		var viewer = parent.find(".Object_Sort_View");
		
		viewer.html(data["html"]);
		';
	}
}






/**
* \defgroup DataTools Data Tools
*
* Welcome to the nice new shiny object oriented data tools.
* This toolkit lets you construct Controls that interface with a `Control_Structure` that interfaces with the database.
*
* This is very new, and may be unstable. Nevertheless you should use it due to its compatiblity with the equaly new `Input` system.
*
* Examples are not yet ready but should appear soon.
*/

/**
* \ingroup DataTools
*
* The control structure is built to provide an easy to use database manipulation tool.
* 
*
*/
class Control_Structure extends Hookable {
	private $controls = [];
	private $options = [];
	private $table = "";
	
	/**
	* Use this to delay operations in seconds.
	*/
	public $delay = 1;
	
	public $structure = false;
	
	static private $count = 0;
	
	/**
	* \param array $options The options are structured like so ["type" => "create", "table" => "myTable"]
	*
	* Current options:
	* 	- char type: The type of operation the Control_Structure will do to the data base. Accepted values: "c", "e", "s", "p". More detail below.
	* 	- string table: The table name that the Control_Structure will use. Accepted value type: string
	*
	* Types:
	* 	- c: This type will create a new row with the provided column and control pairs. <br>
	* 		Events:
	* 			- "create"($index, $data): When the client creates a new row. Params: index(The id for the new row), data(the new data)
	* 	- e: This will load the row with the `id`(put the id in the options. "id" => 123), then allow the client to edit and save to the same row. <br>
	* 		Options:
	* 			- integer "id": Id of the row to edit.
	* 		<br>
	* 		Events:
	* 			- "edit"($data): When the client edits a row. Params: data(the new data), Return false to cancel the edit.
	* 	- s: This will create a sortable html element with the column/control pairs soring the view.
	* 		Options:
	* 			- Structure "areaStructure": A Structure object that goes around the control area and view area. Variables %sort%, %view%
	* 			- boolean "viewOnStart": If view area should show results at the start.
	* 			- View "view"(Required): The view object that will be used to display the sorted rows. Note: Only the sub() method is used on the view.
	* 		Events:
	* 			- "sortData"($data): This is called before the viewer creates a finder to find the data. Return the modified $data object.
	* 			- "sortFinder"(&$finder): This is called with a reference to a finder. You can add or modify the finder.
	* 	- p: Plain input.
	*
	*/
	public function __construct($options) {
		if (!isset($options['type'])) throw new Exception("No type set in Control_Structure");
		if (!isset($options['table'])) throw new Exception("No table set in Control_Structure");
		
		$this->table = GetTable($options['table']);
		
		$this->options = $options;
		
		
				
		$this->on("success", function ($data) {
			$m = new Message();
			$m->add("form", Message::Success("Success"));
			return $m;
		});
		
		$this->on("error", function ($data) {
			$m = new Message();
			$m->add("form", Message::Error("Error"));
			return $m;
		});
		
		$this->on("create", function ($index, $data) {
			
		});
		
		$this->on("edit", function ($data) {
			return true;
		});
		
		$this->on("sortData", function ($data) {
			return $data;
		});
		
		$this->on("sortFinder", function (&$finder) {
			
		});
		
	}
	
	public $clientEvents = [];
	
	public function client($event, $javascript) {
		$this->clientEvents[$event] = $javascript;
	}
	
	public function addControl($name, $control) {
		array_push($this->controls, ['n' => $name, 'c' => $control]);
	}
	
	public function sorting(callable $callback) {
		
	}
	
	public function get() {
		if ($this->options["type"] == 's')
			return $this->get_sort();
		
		self::$count++;
		$f = new Form("Object_Form_".self::$count);
		
		$f->clientEvents = $this->clientEvents;
		
		$f->on("success", function ($data) {
			return $this->event("success", [$data]);
		});
		
		$f->on("error", function ($data) {
			return $this->event("error", [$data]);
		});
		
		
		
		$sendStructure = $this->structure;
	
		$editLoads = [];
		
		foreach ($this->controls as $c) {
			$f->addInput($c['n']."_con", $c['c']->get());
			
			array_push($editLoads, $c['n']);
			
			if ($sendStructure !== false) {
				$sendStructure->html = str_replace('%'.$c['n'].'%', '%'.$c['n'].'_con%', $sendStructure->html);
			}
		}
		
		$f->structure = $sendStructure;
		
		if ($this->options['type'] == 'e') {
			if (!isset($this->options["id"])) throw new Exception("No id provided in Control_Structure edit operation");
			
			$find = new Data_Finder(false, implode(", ", $editLoads));
			$find->where("", "id", "=", $this->options["id"]);
			$row = Data_Select($this->table, $find);
			
			foreach ($row[0] as $k => $v) {
				$row[0][$k."_con"] = $this->getControl($k)->from($row[0][$k]);
				unset($row[0][$k]);
			}
			
			if (count($row) > 0) {
				$f->load($row[0]);
			}
		}
		
		$rtn = $f->check();
		if ($rtn !== false) {
			Wait($this->delay);
			$get = false;
			switch ($this->options['type']) {
				case 'c':
					$get = $this->get_create($rtn, $f);
					break;
				case 'e':
					$get = $this->get_edit($rtn, $f);
					break;
			}
			
			return $get;
		}
		
		return $f->get();
	}
	
	public function get_create($data, $form) {
		$rtn = true;
		
		$b = new Data_Builder();
		foreach ($this->controls as $c) {
			$name = $c['n'].'_con';
			if (!array_key_exists($name, $data)) continue;
				$val = $data[$name];
				
				$err = $c['c']->validate($data[$name]);
				if (!$err) {
					$rtn = false;
				}
				
				$b->add($c['n'], $c['c']->to($val));
		}
		
		if ($rtn) {
			$index = Data_Insert($this->table, $b);
			
			$this->event("create", [$index, $b->arrayify()]);
		}
		
		return $rtn;
	}
	
	private function getControl($name) {
		foreach ($this->controls as $c)
			if ($c['n'] == $name)
				return $c['c'];
		return false;
	}
	
	public function get_edit($data, $form) {
		$rtn = true;
		
		
		$f = Quick_Find([["id", "=", $this->options["id"]]]);
		
		$b = new Data_Builder();
		foreach ($this->controls as $c) {
			$name = $c['n'].'_con';
			if (!array_key_exists($name, $data)) continue;
				$val = $data[$name];
				
				$err = $c['c']->validate($data[$name]);
				if (!$err) {
					$rtn = false;
				}
				
				$b->add($c['n'], $c['c']->to($val));
		}
		
		if ($rtn AND $this->event("edit", [$b->arrayify()]) !== false) {
			Data_Update($this->table, $b, $f);
		}
		
		return $rtn;
	}
	
	private function findSorted($data) {
		$finder = new Data_Finder();
		
		$rtn = true;
		foreach ($this->controls as $c) {
			$name = $c['n'].'_con';
			if (!array_key_exists($name, $data)) continue;
				$val = $data[$name];
				
				$err = $c['c']->validate($data[$name]);
				if (!$err) {
					$rtn = false;
					return 'Error.';
				}
				$c['c']->filter($val, $finder, $c['n']);
		}
		
		$found = Data_Select($this->table, $finder);
		
		$html = "";
		
		foreach ($found as $row) {
			$html .= $this->options["view"]->sub($row);
		}
		
		return $html;
	}
	
	public function get_sort() {
		
		
		
		self::$count++;
		$f = new Form("Object_Form_".self::$count);
		
		$f->clientEvents = $this->clientEvents;
		
		$f->on("success", function ($data) {
			$m = $this->event("success", [$data]);
			
			$html = $this->findSorted($data);
			
			$m->add("form", Message::Action("Object_Sort_Listener", ["html" => $html]));
			return $m;
		});
		
		$f->on("error", function ($data) {
			return $this->event("error", [$data]);
		});
		
		$sortStructure = $this->structure;
		
		$areaStructure;
		
		if (isset($this->options["areaStructure"])) {
			$areaStructure = $this->options["areaStructure"];
		}else{
			$areaStructure = new Structure(Theme::grid([[["%sort%", 4], ["%view%", 8]]], "Sorting")->get());
		}
		
		$sorts = [];
		foreach ($this->controls as $c) {
			$f->addInput($c['n']."_con", $c['c']->get());
			
			$sorts[$c['n']] = $c['c'];
			
			if ($sortStructure !== false) {
				$sortStructure->html = str_replace('%'.$c['n'].'%', '%'.$c['n'].'_con%', $sortStructure->html);
			}
		}
		
		if (isset($this->options["viewOnStart"]))
			if ($this->options["viewOnStart"])
				$f->submitOnStart = true;
		
		$f->structure = $sortStructure;
		
		$val = $f->check();
		
		
		
		$html = $areaStructure->get([
			"sort" => $f->get(),
			"view" => "<div class='Object_Sort_View'></div>"
		]);
		
		return "<div class='Object_Sort_Wrap'>".$html."</div>";
	}
	
}


?>