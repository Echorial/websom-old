<?php

//Make sure widget template class is included\\
include("Widget.php");

//Make sure control template class is included\\
include("Control.php");

//Make sure view template class is included\\
include("View.php");


//Create Loaded Modules\\
$LoadedModules;

//Create Loaded Widgets\\
$LoadedWidgets;

//Create Loaded Controls\\
$LoadedControls;

$LoadedViews;

//Create the module table reference\\
$ModuleTables = array();

//Create a new directory iterator to run modules\\
$Modules = new DirectoryIterator(Websom_root."/Website/Modules");

//Just doing some dependent Module garbage\\
if (file_exists(Websom_root."/Website/Modules/Object.php")){
	include(Websom_root."/Website/Modules/Object.php");
	Load_Configs('Object');
	
	$LoadedModules['Object'] = CallFunction("Object_Status");	
	
	
	//Create the module storage reference\\
	Structure_Create("websom_reference", "`id` INT NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`), `module` varchar(256)");
	
	$finder = new Data_Finder(true);
	$modTableSort = Data_Select("websom_reference", $finder);
	foreach ($modTableSort as $Row){
		$ModuleTables[$Row['module']] = "m".$Row['id'];
	}

}else{
	Error('Module', 'Cannot find object module in '.Websom_root.'/Modules.', true);
}

foreach ($Modules as $Module) {
	if (!$Module->isDot()) {
		$ModuleName = basename($Module->getFilename(), ".php");

	
		
		//Call proper module config functions\\
		Load_Configs($ModuleName);
		
		
		//Call Init on each module\\
		
		$LoadedModules[$ModuleName] = CallFunction($ModuleName."_Status");
		
		
		//Set module widgets up\\
		if (CallFunction($ModuleName."_Get_Widgets") !== false){
			$LoadedWidgets[$ModuleName] = array();
			
			foreach(CallFunction($ModuleName."_Get_Widgets") as $WidgetName){
				array_push($LoadedWidgets[$ModuleName], array($ModuleName, $WidgetName));
			}
		}
		
		//Set module controls up\\
		if (CallFunction($ModuleName."_Get_Controls") !== false){
			$LoadedControls[$ModuleName] = array();
			
			foreach(CallFunction($ModuleName."_Get_Controls") as $ControlName){
				array_push($LoadedControls[$ModuleName], array($ModuleName, $ControlName));
			}
		}
		
		//Set module views up\\
		if (CallFunction($ModuleName."_Get_Views") !== false){
			$LoadedViews[$ModuleName] = array();
			foreach(CallFunction($ModuleName."_Get_Views") as $ViewName)
				array_push($LoadedViews[$ModuleName], array($ModuleName, $ViewName));
		}
		
		//Get module information\\
		if (($module_info = CallFunction($ModuleName."_Info")) !== false){
			Websom::$Modules[$ModuleName] = [
				'info' => $module_info,
				'status' => $LoadedModules[$ModuleName]
			];
		}
	}
}

callEvent('modulesLoaded');

function Load_Configs($ModuleName){
	$ModuleConfigFunction = $ModuleName."_Config_";
	if ($ModuleName != 'Object') {
		include(Websom_root."/Website/Modules/".$ModuleName.'.php');
	}
	if (function_exists($ModuleConfigFunction.'Send') AND function_exists($ModuleConfigFunction.'Get') AND function_exists($ModuleConfigFunction.'Fail')) {
		
		$ModuleConfigReqs = CallFunction($ModuleConfigFunction."Send");
		$ModuleOutput;	

		foreach ($ModuleConfigReqs as $i => $req){
			if (file_exists(Document_root."/Config/".$ModuleConfigReqs[$i].".ini")){
				$ModuleOutput[$ModuleConfigReqs[$i]] = parse_ini_file(Document_root."/Config/".$ModuleConfigReqs[$i].".ini");
			}else{
				$Exeption = CallFunctionArgs($ModuleConfigFunction."Fail", array($ModuleConfigReqs[$i]));

				if ($Exeption !== true){
					Create_File(Document_root.'/Config/'.$ModuleConfigReqs[$i].'.ini', $Exeption);
					die();
				}
			}
		}
		CallFunction($ModuleConfigFunction."Get", array($ModuleOutput));
	}
}





//Functions\\



function GetControls(){
	global $LoadedControls;
	return $LoadedControls;
}

function GetWidgets(){
	global $LoadedWidgets;
	return $LoadedWidgets;
}

function GetViews(){
	global $LoadedViews;
	return $LoadedViews;
}

function GetModules(){
	global $LoadedModules;
	return $LoadedModules;
}

function GetModuleReference($ModuleName = false){
	global $ModuleTables;
	if ($ModuleName === false) return $ModuleTables;
	if (isset($ModuleTables[$ModuleName]))
		return $ModuleTables[$ModuleName];
	return false;
}

function Display_Modules(){
	$Echo = 'Loaded Modules : ';
	foreach (GetModules() as $ModuleName => $Module){
		if ($Module){
			$Echo .= '<label style="color:green;">'.$ModuleName.'</label>, ';
		}elseif(!$Module){
			$Echo .= '<label style="color:red;">'.$ModuleName.'</label>, ';
		}
	}
	echo rtrim($Echo, ", ");
}

//TODO: Move this some place else\\ 
function Create_File ($path, $cont) {
	$file = fopen($path, 'w');
	fwrite($file, $cont);
	fclose($file);
}

function Display_Widgets(){
	$e = 'Loaded Widgets : ';
	foreach (GetWidgets() as $wo => $ws)
		foreach($ws as $w)
			$e .= '<label style="color:green;">'.$w[0].".".$w[1].'</label>, ';
	echo rtrim($e, ", ");
}


function Display_Controls(){
	$e = 'Loaded Controls : ';
	foreach (GetControls() as $co => $cs)
		foreach($cs as $c)
			$e .= '<label style="color:green;">'.$c[0].".".$c[1].'</label>, ';
	echo rtrim($e, ", ");
}

function Display_Views(){
	$e = 'Loaded Views : ';
	foreach (GetViews() as $vo => $vs)
		foreach($vs as $v)
			$e .= '<label style="color:green;">'.$v[0].".".$v[1].'</label>, ';
	echo rtrim($e, ", ");
}


//New module loading code\\ 
function Reload_Modules() {
	$msgs = '';
	
	function StructureColumns($a=array()){
		$r = '';
		if (gettype($a) == 'array') {
			foreach($a as $v){
				$r .= ', '.$v;
			}
		}else{
			return ', '.$a;
		}
		return $r;
	}
	
	$Wbsm_Modules = new DirectoryIterator(Websom_root."/Website/Modules");

	foreach ($Wbsm_Modules as $mod) {
		if (!$mod->isDot()) {
			$ModuleName = basename($mod->getFilename(), ".php");
			//Start loading module\\ 
			$finder = new Data_Finder();
			$finder->where('', 'module', '=', $ModuleName);
			$Id = Data_Select("websom_reference", $finder);
			if (count($Id) == 0) {
				$builder = new Data_Builder();
				$builder->add('module', $ModuleName);
				$Id = Data_Insert("websom_reference", $builder);
			}else{
				$Id = $Id[0]['id'];
			}
			
			$tbls = CallFunction($ModuleName."_Structure");
			if (!is_array_associative($tbls) AND $tbls !== false) {
				return 'Module '.$ModuleName.' is not cooperating. \n';
			}
			if ($tbls === false) {
				$msgs .= '[Loaded '.$ModuleName.'] \n';
				continue;
			}
			foreach ($tbls as $tableName => $tableColumns){
				Structure_Create("m".$Id.'_'.$tableName, "`id` INT NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`) ".StructureColumns($tableColumns));
				$msgs .= '\n Loaded '.$ModuleName.'.'.$tableName;
			}
			$msgs .= '\n [Loaded '.$ModuleName.']\n';
		}
	}
	
	return $msgs;
}

?>