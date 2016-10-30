<?php

function GetControl($ControlName, $Column){
	
	$ModuleControls = GetControls();
	
	foreach($ModuleControls as $Controls){
		foreach($Controls as $Control){
			if ($Control->Control_Name == $ControlName){
				$ControlOptions = $Control->Get();
				
				$Html = GetControlHtml($ControlOptions, count(GetForm()['controls']));
				$FormSet = & GetForm();
				
				$FormSet['controls'][count(GetForm()['controls'])] = array($ControlOptions, $Column);
				
				return $Html;
			}
		}
	}
	return false;
}

function GetControlHtml($ControlOptions, $Id){
	$Rtn = '';
	if ($ControlOptions['type'] == "group"){
		
	}else{
		InputHtml("");
	}

}

function InputHtml($ControlOptions, $Id){
	if ($ControlOptions['type'] == "text"){
		return "<input placeholder='".$ControlOptions['placeholder']."' controltype='".$ControlOptions['type']."' name='Cntrl".$Id."' count='".$ControlOptions['count']."' not='".$ControlOptions['not']."'>";
	}else if ($ControlOptions['type'] == "submit"){
		return "<input type='submit' value='".$ControlOptions['value']."' controltype='".$ControlOptions['type']."' name='Cntrl".$Id."'>";
	}
}

?>