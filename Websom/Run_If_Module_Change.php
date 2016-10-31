<?php
include('Start.php');

echo 'Loading modules.<br>';

$Modules = new DirectoryIterator(Websom_root."/Website/Modules");

foreach ($Modules as $Module) {
	if (!$Module->isDot()) {
		$ModuleName = basename($Module->getFilename(), ".php");
		echo 'Loading: '.$ModuleName.' [';
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
			Error('Module', 'Module '.$ModuleName.' is not cooperating.', true);
		}
		if ($tbls === false) {
			echo '<pre>		No data to load.</pre>]<br>';
			continue;
		}
		foreach ($tbls as $tableName => $tableColumns){
			Structure_Create("m".$Id.'_'.$tableName, "`id` INT NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`) ".StructureColumns($tableColumns));
			echo '<pre>		Loaded '.$ModuleName.'.'.$tableName.'</pre>';
		}
		echo ']<br>';
		
	}
}

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

include('End.php');
?>