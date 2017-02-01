<?php
callEvent('end');

$mainBody = ob_get_contents();
ob_end_clean();

$Page = '';

$Page = file_get_contents(Websom_root."/Website/Page/".$Properties['TemplatePage'], true);


preg_match_all("~!require-(.*?)-!~s", $Page, $Requires);

foreach($Requires[1] as $ReqSet){
	ob_start();
	include(Websom_root.'/Website/Requires/'.$ReqSet);
	$RequireInclude = ob_get_clean();
	$Page = str_replace($ReqSet, $RequireInclude, $Page);
}
$Page = preg_replace("~!require-(.*?)-!~s", '$1', $Page);

callEvent("endAfter");

Websom_Check_Responsive();

SetPropertie("Css", Resources::getCss());
SetPropertie("Javascript", Resources::getJs().Javascript::get());

$Properties = GetProperties();

$Properties['Body'] .=  $mainBody;


$Properties['Input'] = Get_Client_Scripts().Get_Input_Scripts().Get_Responsive_Scripts();


preg_match_all("~%(.*?)%~", $Page, $Propertie);

foreach($Propertie[0] as $PropertieSet){
	$rplc = str_replace("%", "", $PropertieSet);
	$rplcVal = "";
	if (isset($Properties[$rplc]))
		$rplcVal = $Properties[$rplc];
	
	$Page = str_replace($PropertieSet, $rplcVal, $Page);
}






//Do all form checking after the user has created the forms\\
include(Websom_root."/Generic/Core/Data_Form_Check.php"); //Old



$Render = Render();
if ($Render === false){
	echo $Page;
}else{
	
	//FIX THIS
	$resources = IncludeResources();
	if ($resources !== false) $Render = '<!DOCTYPE html><html><head>'.$Properties['Css'].$Properties['Javascript'].$Properties['Input'].'</head>'.'<body>'.$Render.'</body></html>';
	echo $Render;
}

?>