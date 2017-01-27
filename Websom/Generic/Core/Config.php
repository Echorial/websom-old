<?php

class Config {
	static function Get($name, $default, $moduleName = false) {
		$pre = "";
		if ($moduleName !== false) {
			$pre = $moduleName."/";
			if (!file_exists(Document_root.'/Config/'.$moduleName))
				mkdir(Document_root.'/Config/'.$moduleName);
		}
		
		if (!file_exists(Document_root.'/Config/'.$pre.$name.'.ini')) {
			file_put_contents(Document_root.'/Config/'.$pre.$name.'.ini', $default);
		}
		return parse_ini_file(Document_root.'/Config/'.$pre.$name.'.ini');
	}
}

?>