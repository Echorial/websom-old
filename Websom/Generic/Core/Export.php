<?php

function copydir($source, $dest, $permissions = 0755) {
	if (is_link($source))
		return symlink(readlink($source), $dest);

	if (is_file($source))
		return copy($source, $dest);

	if (!is_dir($dest))
		mkdir($dest, $permissions);

	$dir = dir($source);
	while (false !== $entry = $dir->read()) {
		if ($entry == '.' || $entry == '..')
			continue;
		copydir($source.'/'.$entry, $dest.'/'.$entry, $permissions);
	}

	$dir->close();
	return true;
}

class Exporter {
	private $el;
	private $locations = [];
	
	function __construct($exportLocation) {
		$this->el = rtrim('/', $exportLocation);
	}
	
	function addFile($location, $type) {
		if (array_key_exists($type, $this->locations)) {
			array_push($this->locations[$type], $location);
		}else{
			$this->locations[$type] = [$location];
		}
	}
	
	function prepare() {
		return copydir(Document_root, $this->el);
	}
}




//Module exporter\\

class Export_Module_Info {
	static public $file_types = [
		0 => 'Nothing',
		1 => 'Javascript',
		2 => 'Css'
	];
}

/**
*
*/
class Module_Exporter {
	
	function __construct($exportLocation, $moduleName) {
		$this->locations = [];
		$this->el = $exportLocation;
		$this->mn = $moduleName;
	}
	
	function addFile($location, $type, $required = false, $desc = "", $minify = false) {
		if (array_key_exists($type, $this->locations)) {
			array_push($this->locations[$type], [$location, $required, $desc, $minify]);
		}else{
			$this->locations[$type] = [[$location, $required, $desc, $minify]];
		}
	}
	
	function prepare() {
		return mkdir($this->el, 0777, true);
	}
	
	function export() {
		if (!array_key_exists('Module', $this->locations)) return Error('Export', 'No module file specified.');
		
		foreach ($this->locations as $loca) {
			foreach ($loca as $loc) {	
				$check = fopen($loc[0], "r");
				if ($check === false) {
					fclose($check);
					return Error('Export', 'Unable to open module file at "'.$loc[0].'".');
				}
				if (strpos(fread($check, filesize($loc[0])), ['&*Section*&', '&*SectionOptional*&', '&*SecSplit*&']) !== false) {
					fclose($check);
					return Error('Export', 'File '.$loc[0].' contains an invalid string &*Section*& or &*SectionOptional*& or &*SecSplit*&.');
				}
				fclose($check);
			}
		}
		
		//Start building the string
		$exp = [
			'name' => $this->mn,
			'j' => [],
			'c' => []
		];
		$exp['Module'] = file_get_contents($this->locations['Module'][0][0]);
		unset($this->locations['Module']);
		
		
		foreach ($this->locations as $type => $loca) {
			foreach ($loca as $loc) {
				$ca = [
					'r' => $loc[1],
					'd' => '',
					't' => 0,
					'a' => $loc[2],
					'f' => $loc[0]
				];
				//$exp .= (($loc[1]) ? '&*Section*&' : '&*SectionOptional*&').$loc[2].'&*SecSplit*&';
				if ($type == "Javascript" AND $loc[3] == true) {
					$post_d = 
						'compilation_level='.'SIMPLE_OPTIMIZATIONS&'.
						'output_format='.'text&'.
						'output_info='.'compiled_code&'.
						'js_code='.urlencode(file_get_contents(Document_root.'/'.$loc[0]))
					;
					
					/*$post_s = [];
					
					foreach ($post_d as $key => $value) {
						$post_s[] = $key.'='.$value;
					}*/

					$ch = curl_init('http://closure-compiler.appspot.com/compile');
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
					//curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
					curl_setopt($ch, CURLOPT_POSTFIELDS, $post_d);
					//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
					curl_setopt($ch, CURLOPT_HEADER, false);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

					$r = curl_exec($ch);
					//$r = implode('&', $post_s);
					if ($r === false) {
						$ca['d'] = file_get_contents(Document_root.'/'.$loc[0]);
					}else{
						$ca['d'] = $r;
					}
					curl_close($ch);
					$ca['t'] = 1;
					array_push($exp['j'], $ca);
				}else{
					$ca['d'] = file_get_contents(Document_root.'/'.$loc[0]);
					$ca['t'] = 2;
					array_push($exp['c'], $ca);
				}
				
			}
		}
		
		/*$moduleFile = fopen($this->locations['Module'][0][0], "r");
		$exp .= fread($moduleFile, filesize($this->locations['Module'][0][0]));
		fclose($exp);*/
		
		/*$exportFile = fopen($this->el.'/'.$this->mn.'.wbsmod', 'w');
		fwrite($exportFile, $exp);
		fclose($exportFile);*/
		
		file_put_contents($this->el.'/'.$this->mn.'.wbsmod', gzcompress(json_encode($exp)));
		
		return $this->el;
	}
}

class Module_Importer {
	
	function __construct() {
		$this->options = [];
	}
	
	function import($data) {
		$this->baked = json_decode(gzuncompress($data), true);
		foreach ($this->baked['j'] as $js) {
			array_push($this->options, [
				'Description' => $js['a'],
				'FilePath' => $js['f'],
				'Type' => Export_Module_Info::$file_types[$js['t']],
				'IsRequired' => $js['r'],
				'Data' => $js['d'],
				'Active' => $js['r']
			]);
		}
		unset($this->baked['j']);
		
		foreach ($this->baked['c'] as $js) {
			array_push($this->options, [
				'Description' => $js['a'],
				'FilePath' => $js['f'],
				'Type' => Export_Module_Info::$file_types[$js['t']],
				'IsRequired' => $js['r'],
				'Data' => $js['d'],
				'Active' => $js['r']
			]);
		}
		unset($this->baked['c']);
	}
	
	function getOptions() {
		return $this->options;
	}
	
	function activateOption($optionIndex) {
		$this->options[$optionIndex]['Active'] = true;
	}
	
	function execute() {
		$modules_imported = Storage::Get('Exporter.Modules');
		if ($modules_imported === false) Storage::Set('Exporter.Modules', []);
		$mod = [
			'name' => $this->baked['name'],
			'options' => []
		];
		
		file_put_contents(Modules_root.'/'.$mod['name'].'.php', $this->baked['Module']);
		
		foreach ($this->options as $op) {
			if ($op['Active']) {
				if (file_exists($op['FilePath'])) {
					$mod['options'][$op['FilePath']] = Export_Import_Update_File($op, $this->baked['name'], $modules_imported);
				}else{
					$mod['options'][$op['FilePath']] = Export_Import_Create_File($op, $this->baked['name'], $modules_imported);
				}
			}
		}
	}
}
function Export_Import_Update_File($op, $n, $modules_imported) {
	file_put_contents($op['FilePath'], $op['Data']);
	
	return ['dateCreated' => date('m/d/Y')];
}

function Export_Import_Create_File($op, $n, $modules_imported) {
	file_put_contents($op['FilePath'], $op['Data']);
	
	return ['dateCreated' => date('m/d/Y')];
}



function CmdExport () {
	$cmd = new Console_Command('Export', 'Export the websom project');
	$cmd->aliases = [
		'export'
	];
	
	$cmd->params = [
		Console_Param('exportPath', 'The path to export to.', 'string')
	];
	
	$cmd->call = function ($params, $flags) {
		$exp = new Exporter($params['exportPath']);
		
		$str = 'Exporting from '.Document_root.' to '.$params['exportPath'];
		$str .= '
		'.(($exp->prepare()) ? 'Success' : 'Failed');
		return $str;
	};
	
	return $cmd;
}

function CmdExportModule () {
	$cmd = new Console_Command('PackModule', 'Export and pack the websom module.');
	$cmd->aliases = [
		'pack',
		'exportmodule'
	];
	
	$cmd->params = [
		Console_Param('moduleName', 'The module to export.', 'string')
	];
	
	$cmd->flags = [
		Console_Flag('JavascriptFiles', 'These are the javascript files to be included.', ['js'], [
			Console_Param('filePath', 'Javascript file path. From the Document_root', 'string'),
			Console_Param('description', 'Description of file', 'string'),
			Console_Param('minify', 'If the javascript should be minified', 'boolean'),
			Console_Param('optional', 'true or false', 'boolean')
		], true),
		Console_Flag('CssFiles', 'These are the css files to be included.', ['css'], [
			Console_Param('filePath', 'Css file path. From the Document_root', 'string'),
			Console_Param('description', 'Description of file', 'string'),
			Console_Param('optional', 'true or false', 'boolean')
		], true)
	];
	
	$cmd->call = function ($params, $flags) {
		$exp = new Module_Exporter(Document_root.'/ExportedModules', $params['moduleName']);
		$exp->addFile(Websom_root.'/Website/Modules/'.$params['moduleName'].'.php', 'Module');
		
		foreach ($flags['JavascriptFiles'] as $js) {
			$exp->addFile(trim($js['filePath'], '/'), 'Javascript', ($js['optional'] == 'true') ? true : false, $js['description'], ($js['minify'] == 'true') ? true : false);
		}
		
		foreach ($flags['CssFiles'] as $css) {
			$exp->addFile(trim($css['filePath'], '/'), 'Css', ($css['optional'] == 'true') ? true : false, $css['description']);
		}
		
		$str = 'Exporting from '.Websom_root.'/Website/Modules/'.$params['moduleName'].'.php'.' to '.Document_root.'/ExportedModules';
		$str .= '
		'.'Making directory '.var_export($exp->prepare(), true).'
		'.'Making file '.($exp->export());
		return $str;
	};
	
	return $cmd;
}

function CmdImportModule () {
	$cmd = new Console_Command('UnpackModule', 'Unpack and install the websom module.');
	$cmd->aliases = [
		'unpack',
		'install'
	];
	
	$cmd->params = [
		Console_Param('moduleName', 'The module to import.', 'string')
	];
	
	$cmd->flags = [
		Console_Flag('run', 'Run', ['run']),
		Console_Flag('Option', 'Set option', ['o'], [
			Console_Param('index', 'The option index', 'integer')
		], true)
	];
	
	$cmd->call = function ($params, $flags) {
		$imp = new Module_Importer();
		$imp->import(file_get_contents(Document_root.'/'.$params['moduleName'].'.wbsmod'));
		$optionsString = '';
		if (isset($flags['run'])) {
			if (isset($flags['Option']))
				foreach ($flags['Option'] as $fl) {

					if ($fl['index'] < count($imp->options) AND $fl['index'] >= 0)
						$imp->activateOption($fl['index']);
				}
			$imp->execute();
			return 'Done';
		}else{
			$optionsString = 'Use "-o id" to set options to install.';
		}
		
		$optionsA = [];
		foreach ($imp->options as $i => $op) {
			if (!$op['IsRequired']) {
				array_push($optionsA, $i.' : '.$op['FilePath'].' : '.$op['Description'].' : Optional');
			}else{
				array_push($optionsA, $i.' : '.$op['FilePath'].' : '.$op['Description'].' : Required');
			}
		}
		
		$optionsString .= '
		'.implode('
		', $optionsA);
		
		return [$optionsString, '-run'];
	};
	
	return $cmd;
}

onEvent("ready", function () {
	Console_Register(CmdExport());
	Console_Register(CmdExportModule());
	Console_Register(CmdImportModule());
});

?>