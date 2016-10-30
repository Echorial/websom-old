<?php

onEvent("resourcesLoad", function () {
	if (Theme::$theme !== false) {
		Resources::Rule("Css/", ">theme-".Theme::$name, true);
		Resources::Rule("Javascript/", ">theme-".Theme::$name, true);
	}
}, true);

/**
* \defgroup Theme Theme
*
* Themes are designed to be used by everyone so that the module/website will have a common and controllable look to it.
* 
* An example usage of a theme:
* \code
*  $e = Theme::button("Button text", "standalone");
*  $e->attr("id", "myNewButtonId");
* \endcode
* 
* This example shows how you can get a button `Element`. This button's style will adhere to the current Theme installed.
*
*
*/

class Theme {
	
	static private $rules = [];
	static public $name = '';
	static public $theme = false;
	
	static private function loadRules () {
		if (!Websom::$Live) if (!file_exists(Document_root.'/Websom/Website/Themes/Theme.rules.json')) throw new Exception("No Theme.rules.json in Themes directory.");
		$load = json_decode(file_get_contents(Document_root.'/Websom/Website/Themes/Theme.rules.json'), true);
		self::$rules = $load['rules'];
		self::$name = $load['theme'];
		
	}
	
	static private function loadTheme($name) {
		if (file_exists(Document_root.'/Websom/Website/Themes/'.$name.'.php')) {
			include(Document_root.'/Websom/Website/Themes/'.$name.'.php');
			self::$theme = new $name(self::$rules);
			return true;
		}else if (file_exists(Document_root.'/Websom/Website/Themes/'.$name.'.wbstheme')) {
			throw new Exception("You need to unpack the ".$name." theme. Use 'uptheme ".$name."' in the websom console to unpack the theme.");
		}
		return false;
	}
	
	static public function noTheme() {
		self::$name = '';
		self::$rules = '';
		self::$theme = false;
	}
	
	static public function override($name, $rules) {
		self::$name = $name;
		self::$rules = $rules;
		include(Document_root.'/Websom/Website/Themes/'.$name.'.php');
		self::$theme = new $name(self::$rules);
	}
	
	static public function run() {
		self::loadRules();
		self::loadTheme(self::$name);
	}
	
	
	
	
	
	static public function getRule($name, $label) {
		$rule = [];
		if (isset(self::$rules[$name.'.'.$label]))
			$rule = self::$rules[$name.'.'.$label];
		
		return $rule;
	}
	
	static public function mergeRules($rule, $options) {
		$final = array_merge($rule, $options);
		if (array_key_exists('class', $rule) AND array_key_exists('class', $options)) {
			$final['class'] = $rule['class'].' '.$options['class'];
		}
		
		return $final;
	}
	
	
	
	
	

	static public function button($text, $label, $options = []) {
		if (self::$theme !== false)
		return self::$theme->button($text, self::mergeRules(self::getRule('button', $label), $options));
	}

	static public function grid($_2dArray, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->grid($_2dArray, self::mergeRules(self::getRule('grid', $label), $options));
	}

	static public function head($text, $label, $options = []) {
		if (self::$theme !== false)
		return self::$theme->head($text, self::mergeRules(self::getRule('head', $label), $options));
	}

	static public function tabs($keyArray, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->tabs($keyArray, self::mergeRules(self::getRule('tabs', $label), $options));
	}

	static public function image($url, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->image($url, self::mergeRules(self::getRule('image', $label), $options));
	}

	static public function tooltip(Element &$element, $text, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->tooltip($element, $text, self::mergeRules(self::getRule('tooltip', $label), $options));
	}
	
	static public function accordion($keyArray, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->accordion($keyArray, self::mergeRules(self::getRule('accordion', $label), $options));
	}

	static public function slider($slides, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->slider($slides, self::mergeRules(self::getRule('slider', $label), $options));
	}

	static public function breadcrumbs($keyArray, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->breadcrumbs($keyArray, self::mergeRules(self::getRule('breadcrumbs', $label), $options));
	}

	static public function badge($content, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->badge($content, self::mergeRules(self::getRule('badge', $label), $options));
	}

	static public function group($array, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->group($array, self::mergeRules(self::getRule('group', $label), $options));
	}

	static public function card($content, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->card($content, self::mergeRules(self::getRule('card', $label), $options));
	}
	
	static public function input_text($value, $placeholder, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->input_text($value, $placeholder, self::mergeRules(self::getRule('input_text', $label), $options));
	}

	static public function input_select($keyArray, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->input_select($keyArray, self::mergeRules(self::getRule('input_select', $label), $options));
	}

	static public function input_radio($keyArray, $name, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->input_radio($keyArray, $name, self::mergeRules(self::getRule('input_radio', $label), $options));
	}

	static public function input_check($text, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->input_check($text, self::mergeRules(self::getRule('input_check', $label), $options));
	}

	static public function input_file($label, $options = []){
		if (self::$theme !== false)
		return self::$theme->input_file(self::mergeRules(self::getRule('input_file', $label), $options));
	}

	static public function input_range($min, $max, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->input_range($min, $max, self::mergeRules(self::getRule('input_range', $label), $options));
	}

	static public function input_date($date, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->input_date($date, self::mergeRules(self::getRule('input_date', $label), $options));
	}
	
	static public function input_submit($text, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->input_submit($text, self::mergeRules(self::getRule('input_submit', $label), $options));
	}

	static public function loader($label, $options = []){
		if (self::$theme !== false)
		return self::$theme->loader(self::mergeRules(self::getRule('loader', $label), $options));
	}

	static public function video($location, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->video($location, self::mergeRules(self::getRule('video', $label), $options));
	}

	//static public function input_shadow(&$element, $depth, $options){}
	
	static public function panel($text, $content, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->panel($text, $content, self::mergeRules(self::getRule('panel', $label), $options));
	}
	
	static public function container($content, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->container($content, self::mergeRules(self::getRule('container', $label), $options));
	}

	static public function tell(&$element, $level, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->tell($element, $level, self::mergeRules(self::getRule('tell', $label), $options));
	}
	
	
	
	
	
	
}

Theme::run();


/**
* \ingroup Theme
* This is the template to use when creating `Theme`'s
*
* All methods need to return an `Element` instance.
*
*
*
* Input:
* 	All themes must provide client and server side methods for getting, setting for their inputs.
*	<div class="note">The input element that Theme::input_example() gives may not be the real input element use `input_element` for this.</div>
* 	Standard:
* 		Server: All `Element` objects need to have a input_get, input_set and input_element extended onto the element. Each input has a section about what the standards for each method are.
* 		Client: Almost the same as server, accept using jQuery.
* 		
* 		Usage:
* 	 	 	server: \code
* 	 	 	$value = $myTextElement->call("input_get");
* 	 	 	$myTextElement->call("input_element")->addClass("cool-text-box");
* 	 	 	\endcode
*			client: \code
* 	 	 	var value = window.Websom.Theme.get($("#myInput"));
* 	 	 	window.Websom.Theme.set($("#myInput"), value+" extra.");
* 	 	 	\endcode
*
*
*
*
*/
interface iTheme {
	
	/**
	* Return an `Element` with the text displayed inside of a button
	*
	* Options: 
	* 	- class(string): The class(es) to apply to the button.
	* 	- size(int): The size of the button.
	* 		- Small: 1
	* 		- Medium: 2
	* 		- Large: 3
	* 	- round(bool): If the button is rounded or not.
	* 	- disabled(bool): If the button is disabled.
	*
	*/
	public function button($text, $options);
	
	/**
	* Return an `Element` with a grid.
	* Each column is an `Element` instance.
	* Array example:
	* \code
	* Theme::grid([
	* 	[col1, col2, col3],
	* 	[[colWithSize, 10], [col2, 2]]
	* ], "test");
	* \endcode
	*
	*
	* Options: 
	* 	- class(string): The class to apply to the grid.
	* 	- columnClass(string): The class(es) to apply to each column.
	* 	- rowClass(string): The class(es) to apply to each row.
	*/
	public function grid($_2dArray, $options);
	
	/**
	* Large text.
	*
	* Options:
	* 	- class(string): The class added to the head.
	*/
	public function head($text, $options);
	
	/**
	* The key is the tab name, and the value is the tab content.
	*
	* Options:
	* 	- class(string): The class to apply to the tab wrapper.
	* 	- default(string): What tab should be default
	* 	- contentClass(string): The class that should be added to every tab content section.
	* 	- tabClass(string): The class that should be added to every tab button.
	*
	*/
	public function tabs($keyArray, $options);
	
	/**
	* Image box.
	*
	* Options:
	* 	- class(string): The class to be attached to the image box.
	* 	- width(int or string): The width of the image.
	* 	- height(int or string): The height of the image.
	* 	- caption(string): The caption for the image.
	*/
	public function image($url, $options);
	
	/**
	* Apply a tooltip to the `element`.
	*
	* Options:
	* 	- delay(int): The duration of time until the tooltip shows.
	* 	- direction[not standard](string): "bottom", "top", "right" and "left"
	*/
	public function tooltip(&$element, $text, $options);
	
	
	/**
	* Return an accordion.
	*
	* Options:
	* 	- class(string): You get it by now.
	* 	- classHead(string): The header class.
	* 	- classBody(string): The body class.
	* 	- default(string): The default section.
	* 	- multi(bool): If multiple sections can be open at once.
	*/
	public function accordion($keyArray, $options);
	
	/**
	* A content slider.
	*
	* Options:
	* 	- class(string): The class wrapping the slider.
	* 	- slideClass(string): The class wrapping each slide.
	* 	- center(bool): If the slide content should be try to be at the center.
	*/
	public function slider($slides, $options);
	
	/**
	* `key` displayed text, `value` the url.
	*
	* Options:
	* 	- class(string): The class wrapping the breadcrumbs.
	* 	- breadClass(string): The class wrapping each breadcrumb.
	*/
	public function breadcrumbs($keyArray, $options);
		
	/**
	* Badge
	*
	* Options:
	* 	- color(string): "blue", "red", "green", "orange".
	* 	- new(bool): If the badge alerts the user to something new.
	*/
	public function badge($content, $options);
	
	/**
	* List of content.
	*
	* Options:
	*	- None
	*/
	public function group($array, $options);
	
	/**
	* Simple container.
	*/
	public function card($content, $options);
	
	
	
	/**
	* Text field.
	*
	* Methods: Server Prefix: input_
	*	- get(): Returns the string within the text field.
	*	- set(string value): Sets the string within the text field.
	*	- element(): Returns the real input element.
	*
	*/
	public function input_text($value, $placeholder, $options);
	
	/**
	* Selectable input.
	*
	* Methods: Server Prefix: input_
	*	- get(): Returns an array of selected value(s).
	*	- disable(mixed key): Disables the option.
	*	- set(array values): Sets the current option(s) to the `values`.
	*	- element(): Returns the real input element.
	*
	*
	* For setting a option to disabled set the value to an empty string ("")
	*
	* Options:
	*	- default(string): The key to be selected at first.
	*	- optionClass(string): The class to be applyed to each option.
	*	- multiple(bool): If the user should be able to select multiple options.
	*
	*/
	public function input_select($keyArray, $options);
	
	/**
	* Radio field.
	*
	*
	* Methods: Server Prefix: input_
	*	- get(): Returns the selected radio.
	*	- disable(mixed key): Disables the radio.
	*	- set(mixed key): Sets the current radio to the `key`.
	*
	*
	* For setting a radio to disabled set the value to an empty string ("")
	*
	* Options:
	*	- default(string): The key to be selected at first.
	* 	- radioClass(string): The class to be applyed to each radio.
	*
	*/
	public function input_radio($keyArray, $name, $options);
	
	/**
	* Checkbox
	*
	* Methods: Server Prefix: input_
	*	- get(): Returns true or false.
	*	- set(bool onOrOff): Sets the checkbox on or off
	*	- element(): Returns the real input element.
	*
	*
	* Options:
	* 	- default(bool): true or false.
	* 	- disabled(bool): true or false.
	*/
	public function input_check($text, $options);
	
	/**
	* File input
	*
	* Methods: Server Prefix: input_
	*	- get(): client only: Returns an array of base64 versions of the files.
	*	- element(): Returns the real input element.
	*
	*
	* Options:
	* 	- types(array): Array of file types with ".". Example [".png", ".jpg", ".jpeg"].
	* 	- multiple(bool): If multiple file selection is allowed.
	*/
	public function input_file($options);
	
	/**
	* Range slider
	*
	*
	* Methods: Server Prefix: input_
	*	- get(): Returns an array of two values(start, end).
	*	- set(number): Sets the current value to the `number`.
	*	- element(): Returns the real element.
	*
	*
	* Options:
	* 	- default(number): Sets the default value.
	* 	- step(number): Same as calling input_set(array).
	*/
	public function input_range($min, $max, $options);
	
	/**
	* Date field
	*
	* Format: "yyyy-mm-dd"
	*
	* Methods: Server Prefix: input_
	*	- get(): Returns a date string("yyyy-mm-dd").
	*	- set(array[year,month,day]): Sets the current date to the array.
	*	- element(): Returns the real element.
	*
	*
	* Options:
	* 	- min(array[year,month,day]): Sets the min date to the array.
	* 	- max(array[year,month,day]): Sets the max date to the array.
	* 	- not(array[array[year,month,day]]): restricts usage of the dates in the array.
	* 	- only(array[array[year,month,day]]): restricts dates to those in the array.
	* 	- notdays(number(1-7)): does not allow selection of the that day.
	*
	*
	*/
	public function input_date($date, $options);
	
	/**
	* Submit button.
	*
	* Methods: None
	*
	* Options:
	* 	- class: The button class.
	* 	- disabled: Disable the button.
	*/
	public function input_submit($text, $options);
	
	/**
	* Loader
	*
	* This will return a indeterminate loader.
	*/
	public function loader($options);
	
	/**
	* Video
	*
	* This will return a video player pointing to the location.
	*/
	public function video($location, $options);
	
	/*
	* Shadow
	*/
	//public function input_shadow(&$element, $depth, $options);
	
	/**
	* A conainer with text above.
	*
	*/
	public function panel($text, $content, $options);
	
	/**
	* A conainer with style
	*/
	public function container($content, $options);
	
	/**
	* Style the element with a warning, error, success and notice.
	*
	* Levels:
	* 	- Success: 1
	* 	- Notice: 2
	* 	- Warning: 3
	* 	- Error: 4
	*
	*
	*/
	public function tell(&$element, $level, $options);
}





class Theme_Exporter {
	
	function __construct($exportLocation, $themeName) {
		$this->locations = [];
		$this->el = $exportLocation;
		$this->tn = $themeName;
	}
	
	function addFile($location, $type, $required = false, $desc = "") {
		if (array_key_exists($type, $this->locations)) {
			array_push($this->locations[$type], [$location, $required, $desc]);
		}else{
			$this->locations[$type] = [[$location, $required, $desc]];
		}
	}
	
	function prepare() {
		return mkdir($this->el, 0777, true);
	}
	
	function export() {
		if (!array_key_exists('Theme', $this->locations)) return Error('Export', 'No theme file specified.');
		
		foreach ($this->locations as $loca) {
			foreach ($loca as $loc) {	
				$check = fopen($loc[0], "r");
				if ($check === false) {
					fclose($check);
					return Error('Export', 'Unable to open theme file at "'.$loc[0].'".');
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
			'name' => $this->tn,
			'j' => [],
			'c' => []
		];
		$exp['Theme'] = file_get_contents($this->locations['Theme'][0][0]);
		unset($this->locations['Theme']);
		
		
		foreach ($this->locations as $type => $loca) {
			foreach ($loca as $loc) {
				$ca = [
					'r' => $loc[1],
					'd' => '',
					't' => 0,
					'a' => $loc[2],
					'f' => $loc[0]
				];
				
					$ca['d'] = file_get_contents(Document_root.'/'.$loc[0]);
					$ca['t'] = 2;
					array_push($exp['c'], $ca);
				
				
			}
		}
		
		
		file_put_contents($this->el.'/'.$this->tn.'.wbstheme', gzcompress(json_encode($exp)));
		
		return $this->el;
	}
}


function CmdExportTheme () {
	$cmd = new Console_Command('PackTheme', 'Export and pack the websom theme.');
	$cmd->aliases = [
		'ptheme',
		'packtheme',
		'exporttheme'
	];
	
	$cmd->params = [
		Console_Param('themeName', 'The theme to export.', 'string')
	];
	
	$cmd->flags = [
		Console_Flag('JavascriptFiles', 'These are the javascript files to be included.', ['js'], [
			Console_Param('filePath', 'Javascript file path. From the Document_root', 'string'),
			Console_Param('description', 'Description of file', 'string'),
			Console_Param('optional', 'true or false', 'boolean')
		], true),
		Console_Flag('CssFiles', 'These are the css files to be included.', ['css'], [
			Console_Param('filePath', 'Css file path. From the Document_root', 'string'),
			Console_Param('description', 'Description of file', 'string'),
			Console_Param('optional', 'true or false', 'boolean')
		], true)
	];
	
	$cmd->call = function ($params, $flags) {
		$exp = new Theme_Exporter(Document_root.'/ExportedThemes', $params['themeName']);
		$exp->addFile(Websom_root.'/Website/Themes/'.$params['themeName'].'.php', 'Theme');
		
		foreach ($flags['JavascriptFiles'] as $js) {
			$exp->addFile(trim($js['filePath'], '/'), 'Javascript', ($js['optional'] == 'true') ? true : false, $js['description']);
		}
		
		foreach ($flags['CssFiles'] as $css) {
			$exp->addFile(trim($css['filePath'], '/'), 'Css', ($css['optional'] == 'true') ? true : false, $css['description']);
		}
		
		$str = 'Exporting from '.Websom_root.'/Website/Themes/'.$params['themeName'].'.php'.' to '.Document_root.'/ExportedThemes/'.$params['themeName'].'.wbstheme';
		$str .= '
		'.'Making directory '.var_export($exp->prepare(), true).'
		'.'Making file '.($exp->export());
		return $str;
	};
	
	return $cmd;
}



class Theme_Importer {
	
	function __construct() {
		$this->options = [];
	}
	
	function import($data) {
		$this->baked = json_decode(gzuncompress($data), true);
		Storage::set("import", $this->baked);
		Storage::set("raw", $data);
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
		$mod = [
			'name' => $this->baked['name'],
			'options' => []
		];
		
		file_put_contents(Websom_root.'/Website/Themes/'.$mod['name'].'.php', $this->baked['Theme']);
		
		foreach ($this->options as $op) {
			if ($op['Active']) {
				if (file_exists($op['FilePath'])) {
					$mod['options'][$op['FilePath']] = true;
					file_put_contents($op['FilePath'], $op['Data']);
				}else{
					$mod['options'][$op['FilePath']] = true;
					file_put_contents($op['FilePath'], $op['Data']);
				}
			}
		}
	}
}


function CmdImportTheme () {
	$cmd = new Console_Command('UnpackTheme', 'Unpack and install the websom theme.');
	$cmd->aliases = [
		'uptheme',
		'unpacktheme',
		'installtheme'
	];
	
	$cmd->params = [
		Console_Param('themeName', 'The theme to import.', 'string')
	];
	
	$cmd->flags = [
		Console_Flag('run', 'Run', ['run']),
		Console_Flag('Option', 'Set option', ['o'], [
			Console_Param('index', 'The option index', 'integer')
		], true)
	];
	
	$cmd->call = function ($params, $flags) {
		$imp = new Theme_Importer();
		Storage::set("name", $params['themeName']);
		$fo = file_get_contents(Websom_root.'/Website/Themes/'.$params['themeName'].'.wbstheme');
		if ($fo === false)
			return "Unable to find theme file. Make sure the ".$params['themeName'].".wbstheme file is located in the Themes folder";
		$imp->import($fo);
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
	Console_Register(CmdExportTheme());
	Console_Register(CmdImportTheme());
});

?>