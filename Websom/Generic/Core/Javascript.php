<?php


class Javascript {
	static private $globalPageValues = [];
	static private $globalPageEventHooks = [];
	
	static public function Set($key, $value) {
		Javascript::$globalPageValues[$key] = $value;
	}
	
	static public function On($event, $script) {
		array_push(Javascript::$globalPageEventHooks[$event], $script);
	}
	
	static public function get() {
		$script = '<script>';
		
		foreach (Javascript::$globalPageValues as $key => $value)
			$script .= 'window["'.$key.'"] = '.$value.';';
			
		foreach (Javascript::$globalPageEventHooks as $event => $scripts) {
			$script .= 'onEvent("'.$event.'", function () {';
			foreach ($scripts as $s) {
				$script .= '
				'.$s.'
				
				';
			}
			$script .= '});';
		}
		return $script.'</script>';
	}
}


?>