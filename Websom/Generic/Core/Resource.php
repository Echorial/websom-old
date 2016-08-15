<?php

class Resources {
	
	public static $resourceList = [];
	
	public static function Register($info) {
		array_push(Resources::$resourceList, $info);
	}
	
	public static function Register_All($path, $register = 'Resources', $recursive = true) {
		if (substr($path, -1) != '/') $path .= '/';
		$ignore = [];
	/*	if (file_exists($path.'.resourceignore')) $ignore = json_decode(file_get_contents($path.'.resourceignore'))['ignore'];*/
		$direcetory = new DirectoryIterator($path);
		foreach ($direcetory as $file) {
			
			if (!$file->isDot()) {
				$cpath = $path.$file->getFilename();
				if ($file->isDir()) {
					if (!$recursive) continue;
					Register_All($cpath, $register, true);
				}else{
					$ext = pathinfo($cpath)['extension'];
					$type = 'file';
					if ($ext == 'css')
						$type = 'stylesheet';
					else if($ext == 'js')
						$type = 'javascript';
					Resources::Register([
						'register' => $register,
						'external' => false,
						'path' => $cpath,
						'type' => $type
					]);
				}
			}
		}
	}
	
	public static function Remove($place) {
		foreach (Resources::$resourceList as $i => $resource) {
			if (isset($resource['url'])) if ($resource['url'] == $place) {unset(Resources::$resourceList[$i]);return true;}
			if (isset($resource['path'])) if ($resource['path'] == $place) {unset(Resources::$resourceList[$i]);return true;}
		}
		return false;
	}
	
	public static function getPath($resource) {
		if (isset($resource['path'])) return $resource['path'];
		if (isset($resource['url'])) return $resource['url'];
		return false;
	}
	
	public static function getCss() {
		$rtn = '';
		foreach (Resources::$resourceList as $resource) {
			if ($resource['type'] == 'stylesheet')
				$rtn .= "<link rel='stylesheet' type='text/css' href='".Resources::getPath($resource)."'/>";
		}
		return $rtn;
	}
	
	public static function getJs() {
		//Make sure jQuery is first NOTE: In the future an importance index will be implemented.
		$rtn = '<script src="Javascript/jQuery.js"></script>';
		foreach (Resources::$resourceList as $resource) {
			if ($resource['type'] == 'javascript')
				if ($resource['path'] != 'Javascript/jQuery.js')
					$rtn .= "<script src='".Resources::getPath($resource)."'></script>";
		}
		return $rtn;
	}
}


Resources::Register_All('Css/');
Resources::Register_All('Javascript/');

?>