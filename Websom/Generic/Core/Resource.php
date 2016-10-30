<?php
/**
* \defgroup Resources Resource Management
*/

/**
* \ingroup Resources
* Resources in websom are internal or external css or js files. These files are included automaticly by the static Resources object and allow for you to remove, re index and more.
*
* <div class='note'>The Resources object only supports css and js files currently.</div>
*
* resourceignore.json files:
* 	A resourceignore file is a json structured file that tells Resources what files and directories to ignore.
*<hr>
* 	These files can be made in any directory that is included via Resources::Register_All().
*<hr>
* 	How to structure a resourceignore file:
* 	Example:
* 	\code
* 	{
* 		">myModule_": false, //This will not include any files starting with myModule_. So files named myModule_main.js, myModule_style.css will not be included.
* 		"<.css": false, //This will not include any files ending in .css 
* 		"Jquery.js": false //This will not include the Jquery.js file.
* 		"myModule_tools.js": true //This will include the myModule_tools.js despite that it was removed above.
* 	}
* 	\endcode
* 	ignore commands:
* 		- ">": This will search for files/directories with the string beginning in the command value. Example ">a" does not include anything thats name starts with "a".
* 		- "<": This does the same as ">" except searches at the end of a name. Names include the file extension.
*
*/
class Resources {
	
	public static $resourceList = [];
	
	public static $pathignore = [];
	
	public static function Rule($path, $rule, $value) {
		if (!isset(self::$pathignore[$path])) self::$pathignore[$path] = [];
		self::$pathignore[$path][$rule] = $value;
	}
	
	private static function checkIndex($info) {
		if (isset($info["index"])) return $info;
		
		$info["index"] = 0;
	}
	
	/**
	* This will register a new resource with the $info associated to it.
	*
	* Example:
	* \code
	* 	Resources::Register([
	* 		"path" => "Javascript/Jquery.js", //This will tell Resources that the current resource is a internal file and where it is located.
	* 		"index" => 10 //This is an index for the order in which it will be included. High values mean higher in the list of files.
	* 	]);
	* \endcode
	*
	* The options in info are:
	* 	- string path(optional): The local path at which the file is located.
	* 	- string url(optional): The url where the file is located.
	* 	- string register(optional): The code or module that registered this resource.
	* 	- string type(required): The type of resource. values: "javascript", "stylesheet"
	* 	- integer index(default: 0): The z-index for when the resource will be included. Higher means closer to the top.
	*
	*
	*
	*/
	public static function Register($info) {
		array_push(self::$resourceList, self::checkIndex($info));
	}
	
	public static function setInfo($place, $info) {
		$doToIt = function (&$i) use ($info) {
			$i = array_merge($i, $info);
			return true;
		};
		foreach (self::$resourceList as $i => $resource) {
			if (isset($resource['url'])) if ($resource['url'] == $place) {$doToIt(self::$resourceList[$i]);}
			if (isset($resource['path'])) if ($resource['path'] == $place) {$doToIt(self::$resourceList[$i]);}
		}
		return false;
	}
	
	/**
	* This will loop over all files in an array and include them based on their file type.
	*
	* \param string $path The local directory path to include.
	* \param string $register The code or module that registered the file(s).
	* \param string $recursive If sub directories should be included as well.
	*/
	public static function Register_All($path, $register = 'Resources', $recursive = true) {
		if (substr($path, -1) != '/') $path .= '/';
		$ignore = [];
		if (isset(self::$pathignore[$path])) $ignore = self::$pathignore[$path];
		if (file_exists($path.'resourceignore.json')) $ignore = array_merge($ignore, json_decode(file_get_contents($path.'resourceignore.json'), true));
		$direcetory = new DirectoryIterator($path);
		
		foreach ($direcetory as $file) {
			
			if (!$file->isDot()) {
				$cpath = $path.$file->getFilename();
				if (self::check($file->getFilename(), $ignore) == false) continue;
				if ($file->isDir()) {
					if (!$recursive) continue;
					self::Register_All($cpath, $register, true);
				}else{
					$ext = pathinfo($cpath)['extension'];
					$type = 'file';
					if ($ext == 'css')
						$type = 'stylesheet';
					else if($ext == 'js')
						$type = 'javascript';
					self::Register([
						'register' => $register,
						'external' => false,
						'path' => $cpath,
						'type' => $type,
						'index' => 0
					]);
				}
			}
		}
	}
	
	/**
	* This will remove a resource.
	*
	* \param string $place The path or url to remove.
	*/
	public static function Remove($place) {
		foreach (self::$resourceList as $i => $resource) {
			if (isset($resource['url'])) if ($resource['url'] == $place) {unset(self::$resourceList[$i]);return true;}
			if (isset($resource['path'])) if ($resource['path'] == $place) {unset(self::$resourceList[$i]);return true;}
		}
		return false;
	}
	
	private static function getPath($resource) {
		if (isset($resource['path'])) return $resource['path'];
		if (isset($resource['url'])) return $resource['url'];
		return false;
	}
	
	
	private static function sortByIndex() {
		$rtn = self::$resourceList;
		usort($rtn, function ($a, $b) {
			$a = $a['index'];
			$b = $b['index'];

			if ($a == $b) {
				return 0;
			}

			return ($a > $b) ? -1 : 1;
		});
		return $rtn;
	}
	
	/**
	* This is called by websom itself to include files.
	*/
	public static function getCss() {
		$rtn = '';
		$s = self::sortByIndex();
		
		foreach ($s as $resource) {
			if ($resource['type'] == 'stylesheet')
				$rtn .= "<link rel='stylesheet' type='text/css' href='".self::getPath($resource)."'/>";
		}
		return $rtn;
	}
	
	/**
	* This is called by websom itself to include files.
	*/
	public static function getJs() {
		$rtn = "";
		$s = self::sortByIndex();
		foreach ($s as $resource) {
			if ($resource['type'] == 'javascript')
				$rtn .= "<script src='".self::getPath($resource)."'></script>";
		}
		
		return $rtn;
	}
	
	private static function check($name, $i) {
		$rtn = true;
		foreach ($i as $r => $a) {
			if ($r[0] == '>') {
				$r = substr($r, 1);
				if (substr($name, 0, strlen($r)) == $r)
					if ($a === true) {
						return true;
					}else{
						$rtn = false;
					}
			}else if ($r[0] == '<') {
				$r = substr($r, 1);
				if (substr($name, -strlen($r)) == $r)
					if ($a === true) {
						return true;
					}else{
						$rtn = false;
					}
			}else{
				if ($name == $r)
					if ($a === true) {
						return true;
					}else{
						$rtn = false;
					}
			}
		}
		return $rtn;
	}
}


?>