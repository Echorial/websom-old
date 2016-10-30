<?php
$Console_Config;
$Console_Commands;

function Console_Config_Send () {
	return array("Console");
}

function Console_Config_Get ($Configs) {
	global $Console_Config;
	$Console_Config = $Configs[0]['Console'];
}

function Console_Config_Fail ($Config) {
return 'root_user = "admin"
root_password = "root"
token = "console> "';
}

function Console_Status(){
	global $Console_Config, $Console_Commands;
	$Console_Commands = [];
	return true;
}

function Console_Structure() {
	return false;
}



class Console_Command {
	function __construct($name, $description) {
		$this->name = $name;
		$this->description = $description;
	}
	public $name = "";
	public $description = "";
	public $namespace = false;
	public $aliases = [];
	public $flags = [];
	public $params = [];
	
	function call($params, $flags) {
		return 'Default Return Value';
	}
}

function Console_Flag($name, $description, $aliases, $params = [], $list = false) {
	return ['n' => $name, 'd' => $description, 'a' => $aliases, 'p' => $params, 'li' => $list];
}

function Console_Param($name, $description, $type) {
	return ['n' => $name, 'd' => $description, 't' => $type];
}

function Console_Register(Console_Command $command) {
	global $Console_Commands;
	foreach($Console_Commands as $cmd) {
		if (in_array($command->aliases, $cmd->aliases))
			return false;
	}
	
	array_push($Console_Commands, $command);
	
	return true;
}

class Console_Console_Responsive extends Responsive {
	function javascript() {
		global $Console_Config;
		
		return /*Exports.Js.Minify*/'
		
$(document).ready(function () {
	var cont = "";
	var tok = "'.$Console_Config['token'].'";

	$(".Console_Console").each(function () {
		var c = $(this);
		var cr = this;
		c.html(tok);
		c.focus();
		var oldInput = c.val();
		var cline = 0;
		c.keydown(function(e) {
		var lines = c.val().split("\n");
			if (e.keyCode == 13) {
				var sendCommand = lines[lines.length - 1].substring(tok.length, lines[lines.length - 1].length);
				if (cont != "") {
					sendCommand = cont+" "+sendCommand;
					cont = "";
				}
				respond({"command": sendCommand}, function (msg) {
					if (isset(msg.msg)) {
						c.val(c.val() + ("\n"+msg.msg));
						c.val(c.val() + ("\n"+msg.token));
					}else {
						cont = msg.cmd;
						c.val(c.val() + ("\n"+msg.msgb+"\n"+msg.token));
					}
					cline++;
					oldInput = c.val();
				});
				
				return false;
			}
			return true;
		});

		c.on("input", function (e) {
		var lines = c.val().split("\n");
			if (cr.value.substr(0, cr.selectionStart).split("\n").length != lines.length) {
				c.val(oldInput);
			}else{
				if (lines[lines.length-1].substr(0, tok.length) != tok) {
					c.val(oldInput);
				}else{
					oldInput = c.val();
				}
			}
		});
		
	});

});

		
		';
	}
	
	function response($r) {
		global $Console_Config, $Console_Commands;
		$msg = Console_Complete_Run($r['command']);
		$rtn = ['token' => $Console_Config['token']];
		if (is_array($msg)) {
			$rtn['cmd'] = $r['command'].' '.$msg[1];
			$rtn['msgb'] = $msg[0];
		}else{
			$rtn['msg'] = $msg;
		}
		
		return $rtn;
	}
}

class Console_Console extends Widget {
	function get() {
		return '<div class="Console_Container"><textarea name="Console" class="Console_Console"></textarea></div>';
	}
}

class Console_Lexer {
	public $namespace = "";
	public $command = [];
	public $errors;
	public $commands = [];
	
	function __construct($cmds) {
		$this->commands = $cmds;
	}
	
	public function process($s) {
		$state = "nspace";
		$structure = [];
		$space = "base";
		
		$cbuild = '';
		
		$escaped = false;
		
		$errors = [];
		$error = function ($msg) use (&$errors) {
			array_push($errors, $msg);
		};
		
		$s = preg_replace('/[ ]{2,}(?=(?:[^"]*"[^"]*")*[^"]*\Z)/', ' ', $s); //Remove multiple spaces
		
		$cmd = str_split(trim($s, " "));
		
		$place = -1;

		$splitup = [];
		
		foreach($cmd as $char) {
			$place++;
			
			if ($char == '"' AND $place != count($cmd) - 1) {
				$escaped = !$escaped;
				continue;
			}
			
			if ((!$escaped AND $char == ' ') OR $place == count($cmd) - 1) {
				$thisString = $cbuild;
				$placeAdd = 0;
				$cbuild = '';
				if ($char == $cmd[count($cmd) - 1] AND !$escaped) {$thisString .= $char; $placeAdd = 1;}
				$parsed;
				if (is_numeric($thisString)) {
					$parsed = 0 + $thisString;
				}else{
					$parsed = $thisString;
				}
				$tt = [];
				$tt['value'] = $parsed;
				$tt['type'] = gettype($tt['value']);
				$tt['place'] = ($place + $placeAdd) - strlen($tt['value']);
				
				if ($tt['value'][0] == '-') $tt['type'] = 'flag';
				
				array_push($splitup, $tt);
				
				continue;
			}
			
			$cbuild .= $char;
		}
		
		$commandFound = false;
		$paramsFound = false;
		
		$paramType = 0;
		
		$i = -1;
		foreach ($splitup as $c) {
			$i++;
			if (!$commandFound) {
				if ($c['type'] != 'string') {
					$error('Expected type string found '.$c['type'].' at '.$c['place']);
					break;
				}
				$commandFound = true;
				foreach ($this->commands as $checkCmd) {
					if (in_array(trim($c['value'], ' '), $checkCmd->aliases)) {
						$structure['Command_Alias'] = $c['value'];
						$structure['Command_Data'] = $checkCmd;
						$structure['Command_Structure'] = [
							'params' => [],
							'flags' => []
						];
						$paramType = 1;
						continue 2;
					}
				}
				$error('Command '.$c['value'].' not found');
				break;
			}
			
			if ($paramType == 2) {
				$thisFlag = $structure['Command_Structure']['flags'][count($structure['Command_Structure']['flags']) - 1];
				if (count($thisFlag['p']) == count($thisFlag['f']['p'])) {
					$paramType = 1;
				}else{
					if (count($thisFlag['f']['p']) == 0) {
						$paramType = 1;
					}else{
						
						array_push($structure['Command_Structure']['flags'][count($structure['Command_Structure']['flags']) - 1]['p'], $c);
					}
				}
			}
			
			if ($paramsFound AND $c['type'] != 'flag') {
				$error('Too many parameters');
			}
			
			if (!$paramsFound AND $paramType == 1) {
				if ($c['type'] != 'flag') {
					if (count($structure['Command_Structure']['params']) == count($structure['Command_Data']->params)) {
						$paramsFound = true;
						$paramType = 0;
					}
					
					array_push($structure['Command_Structure']['params'], $c);
				}
			}
			
			if ($paramsFound AND count($structure['Command_Structure']['params']) > count($structure['Command_Data']->params)) {
				$error('Too many parameters');
			}
			
			
			
			
			if ($c['type'] == 'flag') {
				$flagA = substr(trim($c['value'], ' '), 1);
				foreach ($structure['Command_Data']->flags as $flag) {
					if (in_array($flagA, $flag['a'])) {				
						array_push($structure['Command_Structure']['flags'], [
							'a' => $flagA,
							'p' => [],
							'f' => $flag
						]);
						$paramType = 2;
						continue 2;
					}
				}
				$error('Unknown flag '.$flagA);
			}	
		}
		if (isset($structure['Command_Structure'])) {
			if (count($structure['Command_Structure']['params']) < count($structure['Command_Data']->params)) {
				$error('Too few parameters');
			}
			
			foreach ($structure['Command_Structure']['flags'] as $flag) {
				if (count($flag['p']) < count($flag['f']['p'])) {
					$error('Too few parameters on flag '.$flag['a']);
				}
			}
		}
		
		$this->command = $structure;
		$this->errors = $errors;
	}
}

function Console_Run (Console_Lexer $cl) {
	$flags = [];
	$outParams = [];
	
	foreach ($cl->command['Command_Structure']['flags'] as $flag) {
		$params = [];
		foreach ($flag['p'] as $i => $param) {
			
			$params[$flag['f']['p'][$i]['n']] = $param['value'];
		}
		if (!$flag['f']['li']) {
			$flags[$flag['f']['n']] = $params;
		}else{
			if (!is_array($flags[$flag['f']['n']]))
				$flags[$flag['f']['n']] = [];
			array_push($flags[$flag['f']['n']], $params);
		}
	}
	
	foreach ($cl->command['Command_Structure']['params'] as $i => $pram) {
		$outParams[$cl->command['Command_Data']->params[$i]['n']] = $pram['value'];
	}
	
	$output = call_user_func($cl->command['Command_Data']->call, $outParams, $flags);
	return $output;
}

function Console_Complete_Run($command) {
	global $Console_Commands, $Console_Config;
	$CL = new Console_Lexer($Console_Commands);
	$CL->process($command);
	$output;
	if (count($CL->errors) > 0) {
		$output = 'Error: '.implode('<br> Error: ', $CL->errors);
	}else{
		$output = Console_Run($CL);
	}
	
	return $output;
}


function CmdVersion () {
	$cmd = new Console_Command('Version', 'This will return the websom version.');
	$cmd->aliases = [
		'ver',
		'version',
		'thisver'
	];
	
	
	$cmd->call = function ($params, $flags) {
		return Websom::$Version;
	};
	
	return $cmd;
}

function CmdReload () {
	$cmd = new Console_Command('Reload', 'This will reload all of the websom modules installed.');
	$cmd->aliases = [
		'reload'
	];
	
	$cmd->call = function ($params, $flags) {
return str_replace('\n', '
', Reload_Modules());
	};
	
	return $cmd;
}

function CmdInfo() {
	$cmd = new Console_Command('Info', 'This will return the info of the module passed.');
	$cmd->aliases = [
		'info',
		'i'
	];
	
	$cmd->params = [
		Console_Param('moduleName', 'Name of the module you want to view.', 'string')
	];
	
	$cmd->call = function ($params, $flags) {
		$module = Websom::Module($params['moduleName']);
		if ($module === false) return 'No module found';
		return list_key_values($module);
	};
	
	return $cmd;
}

function CmdHelp () {
	
	$cmd = new Console_Command('Help', 'View all commands.');
	$cmd->aliases = [
		'help',
		'h'
	];
	
	$cmd->flags = [
		Console_Flag('CommandDetails', 'Show the command details', ['c', 'd'], [Console_Param('CommandName', 'Name of the command', 'string')])
	];
	
	$cmd->call = function ($params, $flags) {
		global $Console_Commands;
		$list = "-- Help --
Use (help -d command name/aliase) for command details.

Commands
";
		
		if (!isset($flags['CommandDetails'])) {
			foreach ($Console_Commands as $cmd) {
$list .= $cmd->name.'('.$cmd->aliases[0].'): '.$cmd->description.'
';
			}
		}else{
			foreach ($Console_Commands as $cmd) {
				if ($cmd->name == $flags['CommandDetails']['CommandName'] OR in_array($flags['CommandDetails']['CommandName'], $cmd->aliases)) {
$commandDetails = "Details of ".$cmd->name."
--- Aliases ---
	".implode(",
	", $cmd->aliases)."
--- Description ---
".$cmd->description."
--- Parameters ---
";

foreach ($cmd->params as $param)
$commandDetails .= $param['t'].' '.$param['n'].': '.$param['d'].'
';

$commandDetails .= "
--- Flags ---

";

foreach ($cmd->flags as $flag) {
$commandDetails .= '('.($flag['li'] ? "List" : "Single").') '.$flag['n'].': '.$flag['d'].'
	- Aliases -
		'.implode(",
		", $flag['a'])."
	- Parameters -
";

foreach ($flag['p'] as $fparam)
$commandDetails .= '		> '.$fparam['t'].' '.$fparam['n'].': '.$fparam['d'].'
';
//['n' => $name, 'd' => $description, 'a' => $aliases, 'p' => $params, 'li' => $list];	
}

return $commandDetails;
					
				}
			}
			return "No command found.";
		}
		
		return $list;
	};
	
	return $cmd;
}


function CmdName () {
	$cmd = new Console_Command('Name', 'Get the website name.');
	$cmd->aliases = [
		'name'
	];
	
	$cmd->call = function ($params, $flags) {
		return Websom::$Config['Website_name'];
	};
	
	return $cmd;
}


onEvent("ready", function () {
	Console_Register(CmdVersion());
	Console_Register(CmdReload());
	Console_Register(CmdName());
	Console_Register(CmdInfo());
	Console_Register(CmdHelp());
	
});

onEvent("resourcesLoad", function () {
	if (defined('Console_Page')) {
		Theme::noTheme();
		Responsive_Once(new Console_Console_Responsive());
	}else{
		Resources::Rule("Css/", "console.css", false);
	}
});




/*class Console_Lexer {
	public $namespace = "";
	public $command = [];
	public $errors;
	public $commands = [];
	
	function __construct($cmds) {
		$this->commands = $cmds;
	}
	
	public function process($s) {
		$state = "nspace";
		$structure = [];
		$space = "base";
		
		$cbuild = '';
		
		$escaped = false;
		
		$errors = [];
		$cmd = str_split($s);
		
		$place = -1;
		
		$states = [
			'commandbuilding' => [
				'break' => function ($next) {
					global $cbuild;
					foreach ($this->commands as $checkCmd) {
						if (in_array($cbuild, $checkCmd->aliases)) {
							$this->command['commandCall'] = $cbuild;
							$this->command['commandCalling'] = $checkCmd;
							$this->command['commandData'] = ['params' => []];
							return 'commandParams';
						}
					}
					
					return ['Command '.$cbuild.' not found'];
				},
				'build' => function ($tchar) {
					global $cbuild;
					$cbuild .= $tchar;
				},
				'start' => function () {
					
				}
			],
			
			'commandParams' => [
				'break' => function ($next) {
					global $cbuild;
					if (count($this->command['commandData']['params']) <= count($this->command['commandCalling']->params)) {
						array_push($this->command['commandData']['params'], $cbuild);
						return 'commandParams';
					}else{
						if ($next === '-') {
							return 'commandFlags';
						}else if ($next === false){
							return 'commandParamEnd';
						}else{
							return ['Unexpected token "'.$next.'"'];
						}
					}
					return ['Param error'];
				},
				'build' => function ($tchar) {
					global $cbuild;
					$cbuild .= $tchar;
				},
				'start' => function () {
					global $cbuild;
					$cbuild = '';
				}
			],
			
			'commandParamEnd' => [
				'break' => function ($next) {
					global $cbuild;
					if (count($this->command['commandData']['params']) < count($this->command['commandCalling']->params)) {
						return ['Too few params'];
					}
					return 'commandEnd';
				},
				'build' => function ($tchar) {
				},
				'start' => function () {
				}
			],
			
			'commandFlags' => [
				'break' => function ($next) {
					global $cbuild;
					if (count($this->command['commandData']['params']) < count($this->command['commandCalling']->params)) {
						array_push($this->command['commandData']['params'], $cbuild);
						return 'commandParams';
					}else{
						return 'commandFlags';
					}
					return ['Param error'];
				},
				'build' => function ($tchar) {
					global $cbuild;
					$cbuild .= $tchar;
				},
				'start' => function () {
					global $cbuild;
					$cbuild = '';
				}
			]
		];

		foreach($cmd as $char) {
			$place++;
			
			if ($place == 0) {
				if (ctype_alpha($char)) {
					$state = "commandbuilding";
				}else{
					array_push($errors, 'Non allowed character at '.$place);
					continue;
				}
			}
		
			if ($char == '"') {
				$escaped = !$escaped;
				continue;
			}
			
			if ((!$escaped AND $char == ' ') OR $place == strlen($s) - 1) {
				if ($place == strlen($s) - 1) $states[$state]['build']($char);
				$getBack = $states[$state]['break'](($place+2 > count($cmd)) ? false : $cmd[$place+2]);
				if (gettype($getBack) == 'string') {
					$state = $getBack;
					if (isset($states[$state])) {
						$states[$state]['start']();
					}else{
						array_push($errors, 'Unknown token at '.$place);
					}
				}else if (gettype($getBack) == 'array'){
					$errors = array_merge($errors, $getBack);
				}else {
					array_push($errors, 'Unknown token at '.$place);
				}
				continue;
			}
			
			
			
			if (isset($states[$state])) {
				$states[$state]['build']($char);
			}else{
				array_push($errors, 'Unknown token at '.$place);
			}
			
			
		}

		$this->errors = $errors;
	}
}*/

?>