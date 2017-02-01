<?php

/**
* \ingroup Helper
* 
* Note: This helper need help. It does not contain all the string tools it should.
* 
* This class is used to centralize all string tools and functions in the goal that if the site admin wishes to have support for international characters they can without modules or code needing to change.
*/
class Str {
	static public $shouldUseMb = false;
	static public $encoding = "";
	
	static function init() {
		self::$shouldUseMb = ((Websom::$Config["Use_MultiByte_String"] == "yes") ? true : false);
		self::$encoding = mb_internal_encoding();
	}
	
	/**
	* @param string The string to check the length of.
	* @return int Number of characters in the $str.
	*/
	static public function length($str) {
		if (self::$shouldUseMb)
			return mb_strlen($str, self::$encoding);
		return strlen($str);
	}
	
	/**
	* Use this to find a string in a string.
	* 
	* @param string $haystack The string to search in.
	* @param string $needle The string to search for.
	* @param int $offset The position to start looking from.
	* @param bool $caseInsensitive If the search should ignore case.
	* 
	* @return This will return the position(int) if the $needle was found or false if not.
	*/
	static public function position($haystack, $needle, $offset = 0, $caseInsensitive = false) {
		if (self::$shouldUseMb) {
			if ($caseInsensitive)
				return mb_stripos($haystack, $needle, $offset, self::$encoding);
			return mb_strpos($haystack, $needle, $offset, self::$encoding);
		}else{
			if ($caseInsensitive)
				return stripos($haystack, $needle, $offset);
			return strpos($haystack, $needle, $offset);
		}
	}
	
	/**
	* @return True if the needle is in the haystack or false if not.
	*/
	static public function contains($haystack, $needle, $caseInsensitive = false) {
		return ((self::position($haystack, $needle, 0, $caseInsensitive) === false) ? false:true);
	}
	
	
}

?>