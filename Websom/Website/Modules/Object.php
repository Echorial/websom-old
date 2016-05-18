<?php
//Object module/manager is the core of websom\\

//Set the appropriate config variables up\\

$Config;
$Connection;

//Make sure object has all config files needed\\
//This is called by websom to include the needed files\\
function Object_Config_Send () {

	//Websom will search for the config file in the Config folder located at the root directory\\
	return array("MySqlCredentials");
}
//This is called by websom to give the config data to the module\\
function Object_Config_Get ($Configs) {
	//The [$Configs] is an array of config data\\
	//Since we only asked for one config file we will store it in the [$Config]\\
	global $Config;
	$Config = $Configs[0]['MySqlCredentials'];
}
//This is called by websom if a config file failed to load\\
function Object_Config_Fail ($Config) {
//[$Config] will be the file that failed to load\\

//If you return true websom will continue loading the page. But if you return a String websom will create the config ini file with the string in it\\
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
//Websom calls this at the start, asking for a status of true/false\\ For instance this will check if it is able to connect to a database then it will return true, if it is unable it will return false
function Object_Status(){
	global $Config;
	global $Connection;
	$Rtn = true;
	$Connection['Select'] = '';
	$Connection['Insert'] = '';
	$Connection['Update'] = '';
	$Connection['Structure'] = '';
	$Connection['Remove'] = '';	
	//Check connection\\
	foreach ($Connection as $Name => $ConnetionTry){
		$ConnectionTry = new mysqli($Config['Server_Name'], $Config[$Name.'_Username'], $Config[$Name.'_Password'], $Config['Database_Name']);
		if ($ConnectionTry->connect_error) {
			$Rtn = false;
		}
		$Connection[$Name] = $ConnectionTry;
	}

	return $Rtn;
}

//Websom calls this asking for the storage structure (in sql)\\
function Object_Structure () {
	return false;
}

class Data_Finder {	//TODO: Make the history and query generate at getPrepared rather than durring the where, between, order
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
	
	public function order($column, $o) {
		array_push($this->_orderHistory, array($column, $o));
		$this->query .= ' ORDER BY `'.$column.'` '.$o.' ';
	}
	
	public function group($column) {
		array_push($this->_groupHistory, array($column));
		$this->query .= ' GROUP BY '.$column.' ';
	}
	
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

function Quick_Find($findsArray){
	$return = new Data_Finder();
	foreach($findsArray as $i => $a)
		$return->where(($i == 0) ?'':'AND', $a[0], $a[1], $a[2]);
	return $return;
}

/*

*/

class Data_Builder {
	public $types = '';
	public $values = array();
	public function add($columnName, $columnValue) {
		$type = '';
		$types['string'] = 's';
		$types['integer'] = 'i';
		$types['boolean'] = 'i';
		$types['double'] = 'd';
		if (isset($types[gettype($columnValue)])) {
			$type = $types[gettype($columnValue)];
		}else{
			Error('Data', 'Cannot store type "'.gettype($columnValue).'" in database.', true);
		}
		$this->types .= $type;
		
		array_push($this->values, array($columnName, $columnValue));
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
	//Very useful\\
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
		
		//Set up values into Post like array\\
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

function GetTable($mod) {
	$mod = explode('.', $mod);
	if (count($mod) < 2)
		return false;
	return GetModuleReference($mod[0]).'_'.$mod[1];
}

function UnTable($mod) {
	$mod = explode('_', $mod);
	return array_search($mod[0], GetModuleReference()).'.'.$mod[1];
}

class Data_Structure {
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
	public function addControl ($control, $column = false, $columnIsName = false) {
		array_push($this->inputs, array($control, $column, $columnIsName));
	}
	
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

//Easy database data editor tools\\
function Data_Input_Create($dataStructure) {
	//Do generating\\
	$inputId = 0;
	$output = '';
	
	$output .= Object_Start_Input(base64_encode($dataStructure->table));

	$output .= Data_Controls_Stringify($dataStructure);
	
	//Do checking\\
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
		//Gathering dust\\
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

function Data_Input_Plain($dataStructure) {
	//Do generating\\
	$inputId = 0;
	$output = '';
	
	$output .= Object_Start_Input(base64_encode($dataStructure->table));

	$output .= Data_Controls_Stringify($dataStructure);
	
	//Do checking\\
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
//Easy database data editor tools\\
function Data_Input_Edit($dataStructure, $rowId, $loadValues = true) {
	//Do generating\\
	$inputId = 0;
	$output = '';
	
	$output .= Object_Start_Input(base64_encode($dataStructure->table));
	$finder = new Data_Finder();
	$finder->where('', 'id', '=', $rowId);
	$currentData = Data_Select($dataStructure->table, $finder);
	if (!$loadValues) $currentData = null;
	$output .= Data_Controls_Stringify($dataStructure, $currentData);
	//Do checking\\
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

//This function is a complete mess, it will be fixed soon.\\
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

		//FOR: Loading data\\

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
		
		//FOR-END:\\
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

?>