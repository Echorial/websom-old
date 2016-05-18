<?php
/*
if (count($_POST) != 0) {
	$Form = FindForm($_POST['FormId']);
	
	if ($Form['type'] == 'Edit'){
		$ColumnKeys = array();
		$ColumnValues = array();
		foreach ($_POST as $Key => $Value){
			if (strpos($Key, 'Cntrl') !== false){
				array_push($ColumnKeys, $Form['controls'][str_replace('Cntrl', '', $Key)][1]);
				array_push($ColumnValues, $Value);
			}
		}
		Data_Update(GetModuleReference($Form['module']), $ColumnKeys, $ColumnValues);
	}

	Cancel('{"Success": "'.$Form['message'].'"}');
}*/
?>