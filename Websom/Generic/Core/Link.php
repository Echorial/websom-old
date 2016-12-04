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
	
	static public $links;
	
	static public function init() {
		$cfg = Config::Get("PageLinker", ";This is the map for page linking.
		Console = \"console.php\"");
		
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
	* @return false if not found or string if found.
	*/
	static public function get($name) {
		if (isset(self::$links[$name]))
			return self::$links[$name];
		return false;
	}
}

?>