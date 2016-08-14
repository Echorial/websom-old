<?php

class Config {
	function Get($name, $default) {
		if (!file_exists(Document_root.'/Config/'.$name.'.ini')) {
			file_put_contents(Document_root.'/Config/'.$name.'.ini', $default);
		}
		return parse_ini_file(Document_root.'/Config/'.$name.'.ini');
	}
}

?>