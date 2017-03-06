<?php

/**
* \defgroup Linker Page linking.
* 
* See Linker for more info.
*/


/**
* \ingroup Linker
* 
* The linker class is used to store links for pages. 
* You would use linker as a way to standardize and control page references and url's.
* 
* Say you have a page "account.php". In your code you might just have this url statically inserted into href's and strings, but then when you want to change the "account.php" to "myaccount.php" you would need to update this throughout your code. Using Linker you can set a name "account" and a url "account.php" and use `Linker::get("account") //"account.php"` instead.
* 
*/
class Linker extends Hookable {
	/// \cond
	
	static public $injects = [];
	
	static private function normalVars($vars) {
		if (count($vars) == 0)
			return "";
		
		$rtn = [];
		foreach ($vars as $k => $v) {
			$rtn[] = $k."=".$v;
		}
		return "?".implode("&", $rtn);
	}
	
	static public $links;
	
	static public function init() {
		$cfg = Config::Get("PageLinker", ";This is the map for page linking.
		Console = \"console.php\"");
		
		foreach ($cfg as $k => $v) {
			if (strpos($v, "http://") === false AND strpos($v, "https://") === false)
				$cfg[$k] = Format_Link($v);
		}
		
		self::$links = $cfg;
	}
	
	/// \endcond
	
	/**
	* This will set the $name to the $location.
	*/
	static public function set($name, $location) {
		self::$links[$name] = $location;
	}
	
	/**
	* This will return the location associated with the $name.
	* 
	* @param string $name The name of the linker to get.
	* @param array [$vars] An associative array containing key(var name) value(var value) for $_GET variables.
	* 
	* @return false if not found or string if found.
	*/
	static public function get($name, $vars = []) {
		if (isset(self::$links[$name])) {
			if (isset(self::$injects[$name]))
				return call_user_func_array(self::$injects[$name], [self::$links[$name], $vars]);
			return self::$links[$name].self::normalVars($vars);
		}
		return false;
	}
	
	/**
	* Use this to inject a filter function($vars) into a specific Linker reference $_GET variable serializer.
	* 
	* @param string $name The reference name.
	* @param callable $filter A function($url(string), $variables[$key => $value]) that should return a url.
	* 
	*/
	static public function inject($name, callable $filter) {
		self::$injects[$name] = $filter;
	}
}

?>