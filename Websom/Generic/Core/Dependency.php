<?php
onEvent('modulesLoaded', function () {
	foreach (Websom::$Modules as $name => $module) {
		if (($module_dep = CallFunction($name."_Dependency")) !== false){
			$status = Websom::$Modules[$name]['status'];
			foreach ($module_dep as $mod) {
				$err = false;
				if (Websom::Module($mod) === false) $err = true;
				if (Websom::Module($mod)['status'] == false) $err = true;
				if ($err) {
					$status = false;
					break;
				}
			}
			Websom::$Modules[$name]['status'] = $status;
		}
	}
});
?>