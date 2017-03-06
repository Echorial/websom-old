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
			//throw new Exception("You need to unpack the ".$name." theme. Use 'uptheme ".$name."' in the websom console to unpack the theme.");
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
	
	static public function input_dictionary($params, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->input_dictionary($params, self::mergeRules(self::getRule('input_dictionary', $label), $options));
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
	
	static public function chip($text, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->chip($text, self::mergeRules(self::getRule('chip', $label), $options));
	}

	static public function icon($name, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->icon($name, self::mergeRules(self::getRule('icon', $label), $options));
	}

	static public function modal($content, $id, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->modal($content, $id, self::mergeRules(self::getRule('modal', $label), $options));
	}
	
	static public function modal_button($content, $id, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->modal_button($content, $id, self::mergeRules(self::getRule('modal_button', $label), $options));
	}
	
	static public function navigation($content, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->navigation($content, self::mergeRules(self::getRule('navigation', $label), $options));
	}
	
	static public function navigation_link($text, $where, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->navigation_link($text, $where, self::mergeRules(self::getRule('navigation_link', $label), $options));
	}
	
	static public function navigation_show($content, $id, $label, $options = []){
		if (self::$theme !== false)
		return self::$theme->navigation_show($content, $id, self::mergeRules(self::getRule('navigation_show', $label), $options));
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
	*	- link(string): If set the button will be a link.
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
	* Chip
	*
	* Options:
	* 	- class(string): The class to apply to the chip
	*/
	public function chip($text, $options);
	
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
	* Options:
	* 	- "type": A string for the input type. Example("password", "text", "number", "multiline")
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
	* 	- type(string): The type of text box it is. (text, password, email, ect.)
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
	*	- get(): client only: Returns an array of [base64 file, Image object]'s. Like: [["Base64 stuff", Image object], ["Base64", Image object], ect..]
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
	* Dictionary
	*
	* This is a simple key searching box that lets users input values and searches for those values in a javascript object or web service.
	* 
	* Methods:  Server Prefix: input_
	*	- get(): Returns an array of keys. Example: ["key1", "key2"]
	*	- set(array): Sets the current keys to the array.
	*	- element(): Returns the input text box.
	*
	* @param array $params This contains two options:
	* 								  - database(optional): A string that tells what url to get a search string to.
	* 								  - getKey(optional): A string for what get variable key to send.
	* 								  - source(optional): A string that tells where the source is in the window. Example: "TagsForSource" will be looked for like so window["TagsForSource"]
	*								  - subSource(optional): This is the extra sub object of the source object. Example: "subKey" will be looked for like so window["TagsForSource"]["subKey"]
	*								  - placeholder(optional): The placeholder before keys are inputed.
	*								  - extraPlaceholder(optional): The placeholder after keys are inputed.
	* 
	* Options:
	* 	- class: The dictionary box class.
	*/
	public function input_dictionary($params, $options);
	
	/**
	* Icon
	* 
	* Icon names should come from the Google material design icons(https://material.io/icons/)
	* 
	* This will return a simple icon element.
	* @param string $name The name of the icon to use. See https://material.io/icons/ for icon names
	*/
	public function icon($name, $options);

	/**
	* Navigation
	*
	* This is a container the navigation bar, it should respond to different screen sizes and content.
	* 
	* Use iTheme::navigation_link() for adding link to this bar.
	* 
	* @param array $content 
	* 
	*	Example: 
	* 		\code
	* 		$content = [
	* 			"id" => "mainNavBar", //The id for this navigation bar. (We will use this latter)
	* 			"content" => [ //Three array items for left, middle, and right aligned content
	* 				[ Theme::navigation_link("Link 1", "Link1.php", "link") ],
	* 				[],
	* 				[]
	* 			],
	* 			"class" => "hello", //Adds class to main nav bar.
	* 			"static" => [ //Static content will not automatically hide/show on screen size changes. Note: Its content can.
	*  			Theme::navigation_show( Theme::icon("menu"), "mainNavBar" <- remember from id, "main" ) //This will make a button that will show our navigation menu if the screen is too small.
	* 	   			Theme::navigation_show( Theme::icon("person"), "hiddenNavigationMenu", "account" ) 
	* 			],
	* 			"sideNavs" => [
	* 				[
	* 					"id" => "hiddenNavigationMenu",
	* 					"content" => [ //We do not use aligned items here.
	* 						"Hello world"
	* 					],
	* 					"side" => "right" //Make this menu start form the right side of the window.
	* 				]
	* 			]
	* 			
	* 		]
	* 		\endcode
	* 
	* 
	* 
	* 
	* Options:
	*/
	public function navigation($content, $options);
	
	/**
	* Navigation Link
	*
	* This is a link that fits in the iTheme::navigation() bar.
	* 
	* @param string $text The text inside of the link.
	* @param string $where The href location.
	* 
	* Options:
	* 	- (string) class: The class to append to the link.
	*  - (bool) active: If the link should be active.
	*/
	public function navigation_link($text, $where, $options);
	
	/**
	* Modal
	* 
	* This is a dialog that will open when a iTheme::modal_button with the same $id is clicked.
	* 
	* @param string/element $content The content of the modal
	* @param string $id A unique identifier for this modal.
	* 
	* Options:
	* 	- (string) where: Where the modal will appear. Options: "bottom", "center"
	*/
	public function modal($content, $id, $options);
	
	/**
	* Modal_button
	* 
	* This is a dialog that will open when a iTheme::modal_button with the same $id is clicked.
	* 
	* @param string/element $content The content of the modal
	* @param string               $id          A unique identifier for this modal.
	* 
	* Options:
	* 	- (string) class: The button class.
	* 	- (int) size: Same as iTheme::button
	* 	- (bool) round: Same as iTheme::button
	*/
	public function modal_button($content, $id, $options);
	
	/**
	* Navigation Show
	*
	* This is a button that will show a hidden navigation side/bar based on its id.
	* 
	* @param string $content The content of the button.
	* @param string $id The navigation/sideNav id.
	* 
	* Options:
	* 	- (string) class: The class to append to the button.
	* 	- (string) align: "left", "middle", "right"
	* 	- (bool) hideOnSmall(defualt true): If false this will always be visible.
	*/
	public function navigation_show($content, $id, $options);
	
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
		if (file_exists($this->el)) {
			return "Already created.";
		}else{
			mkdir($this->el, 0777, true);
			return "Created.";
		}
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
				//if (strpos(fread($check, filesize($loc[0])), ['&*Section*&', '&*SectionOptional*&', '&*SecSplit*&']) !== false) {
					//fclose($check);
					//return Error('Export', 'File '.$loc[0].' contains an invalid string &*Section*& or &*SectionOptional*& or &*SecSplit*&.');
				//}
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
			$exp->addFile(trim($js['filePath'], '/'), 'Javascript', ($js['optional'] == 'true') ? false : true, $js['description']);
		}
		
		foreach ($flags['CssFiles'] as $css) {
			$exp->addFile(trim($css['filePath'], '/'), 'Css', ($css['optional'] == 'true') ? false : true, $css['description']);
		}
		
		$str = 'Exporting from '.Websom_root.'/Website/Themes/'.$params['themeName'].'.php'.' to '.Document_root.'/ExportedThemes/'.$params['themeName'].'.wbstheme';
		$str .= '
		'.'Making directory '.$exp->prepare().'
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

function CmdViewTheme () {
	$cmd = new Console_Command('ViewTheme', 'View all the parts of the current theme.');
	$cmd->aliases = [
		'dtheme',
		'displaytheme',
		'viewtheme'
	];
	
	$cmd->call = function ($params, $flags) {
		$_SESSION["Theme_View_Allowed"] = true;
		return new Console_Open_Url(createProcessLink("themeView", [], false));
	};
	
	return $cmd;
}

onEvent("ready", function () {
	onProcess("themeView", function () {
		if (!isset($_SESSION["Theme_View_Allowed"]) OR $_SESSION["Theme_View_Allowed"] != true) {
			return;
		}
		Page("Blank.html");
		
		$navStruct = [
			"id" => "main_nav",
			"side" => "left",
			"static" => [
				Theme::navigation_show(Theme::icon("menu", "menu")->get(), "main_nav", "account"),
				Theme::navigation_show(Theme::icon("person", "account")->get(), "account", "account", ["align" => "right"])
			],
			"content" => [
				[
					Theme::navigation_link("Home", "#Home", "home", ["active" => false]),
					Theme::navigation_link("Page 2", "#Page2", "forum", ["active" => false]),
					Theme::navigation_link("Page 3", "#Page3", "forum", ["active" => false])
				],
				[],
				[]
			],
			"sideNavs" => [
				[
					"id" => "account",
					"side" => "right",
					"fixed" => true,
					"content" => [
						"Right navigation panel"
					]
				]
			]
		];



		echo Theme::navigation($navStruct, "main")->get();
		
		$head = Theme::head("Theme example", "test");
		echo $head->get();
		
		$buttonNormal = Theme::button("Button", "test");
		$buttonSmall = Theme::button("Small", "test", ["size" => 1]);
		$buttonMed = Theme::button("Medium", "test", ["size" => 2]);
		$buttonLarge = Theme::button("Large", "test", ["size" => 3]);
		$buttonRound = Theme::button("Round", "test", ["round" => true, "size" => 3]);
		$buttonTooltip = Theme::button("Tooltip", "test", ["size" => 1]);
		Theme::tooltip($buttonTooltip, "This is a tooltip", "test");
		
		$buttonContainer = Theme::container(Theme::grid([[$buttonNormal->get(),$buttonSmall->get(),$buttonMed->get(),$buttonLarge->get(),$buttonRound->get(),$buttonTooltip->get()]], "test"), "test");
		
		echo $buttonContainer->get();
		
		$img1 = Theme::image("http://placehold.it/350x150", "test");
		$img2 = Theme::image("http://placehold.it/800x600", "test");
		
		$group = Theme::group([new Element("a", "Group entry 1", []), new Element("a", "Group entry 2", []), new Element("a", "Group entry 3", [])], "test");
		
		$grid = Theme::grid([
			[
				$img1->get()."Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas et leo eget magna lacinia efficitur. Ut rhoncus diam nibh.", $img1->get()."Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas et leo eget magna lacinia efficitur. Ut rhoncus diam nibh.", $img1->get()."Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas et leo eget magna lacinia efficitur. Ut rhoncus diam nibh."
			],
			[
				["9 units", 9], ["3 units", 3]
			]
		], "test", ["columnClass" => "center"]);
		echo Theme::container($grid->get(), "test")->get();
		
		$tabs = Theme::tabs([
			"Number 1" => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus leo ligula, venenatis vel finibus at, feugiat a augue. Sed pretium turpis elementum mi ornare, mattis efficitur leo ultrices. Integer lobortis accumsan tincidunt. Mauris mollis dolor at nisi accumsan lacinia. Nam tincidunt auctor ultricies. Integer libero ligula, aliquam at molestie eget, mollis eget felis. Vivamus sapien diam, aliquam in nisl a, consectetur posuere nibh. Morbi et libero elementum dolor laoreet fermentum. Vestibulum tincidunt egestas lectus, non ornare libero blandit vel.

Interdum et malesuada fames ac ante ipsum primis in faucibus. Praesent varius condimentum ultrices. Pellentesque dictum sapien nec sem feugiat porta. Suspendisse convallis mollis mi vitae laoreet. Aliquam erat volutpat. Phasellus porttitor sapien odio, non feugiat urna aliquet non. Cras est enim, congue vitae elementum ut, molestie et tellus. Vestibulum id tellus efficitur, sodales tortor ut, maximus enim. Nunc viverra libero at varius tincidunt. Praesent quis imperdiet nisi, quis vulputate nunc. Nunc eleifend lacus a arcu sollicitudin molestie non in lacus. Nunc metus orci, efficitur sit amet risus sit amet, dapibus sagittis ipsum.

Vestibulum rhoncus, orci scelerisque fringilla blandit, ligula odio vulputate nisi, a iaculis erat enim a elit. Duis tristique tincidunt magna, non iaculis odio ultricies eget. Nunc eget dui lacus. Duis eleifend quis ligula in aliquam. Praesent pulvinar bibendum fermentum. Maecenas nulla enim, pharetra quis gravida et, lobortis a enim. Donec lobortis ligula dui, eget scelerisque odio maximus id. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nam dui enim, suscipit sit amet massa nec, bibendum consequat quam. Pellentesque tellus enim, faucibus vel sapien et, luctus ultrices arcu. Curabitur vel erat ligula. Morbi cursus finibus luctus."
,
"Number 2" => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed consectetur placerat tortor in blandit. Proin ac feugiat mi. Phasellus convallis suscipit est, in eleifend nibh vestibulum sit amet. Suspendisse sem ipsum, eleifend non mi eget, pharetra molestie mauris. Sed nec semper odio. Aliquam in maximus turpis. Mauris ligula neque, eleifend nec eros quis, hendrerit pharetra orci. Suspendisse in neque orci. Cras ullamcorper, urna quis eleifend interdum, neque dui congue orci, vitae iaculis mauris sem ac nisi. Quisque pharetra lobortis finibus. Vivamus fringilla lacus nec orci malesuada interdum. Ut porttitor efficitur enim eget dignissim. Phasellus ut dolor vel lorem volutpat cursus. Phasellus commodo justo dolor, sed auctor sapien condimentum in. Nullam arcu justo, sagittis eget tristique eu, gravida eu neque.

Etiam ut libero id ipsum malesuada rhoncus. Nam non iaculis erat. Integer eget diam ac felis porta sodales non eget tortor. Quisque dapibus dolor ac ipsum dignissim placerat. Etiam sit amet justo orci. Integer fermentum pharetra enim, quis consequat augue lobortis ut. In gravida fermentum turpis, nec feugiat elit. Donec a quam vel quam egestas placerat. Sed lacinia in sem et placerat. Ut laoreet neque elit, id interdum magna blandit pretium. Donec tincidunt ullamcorper est. Suspendisse porta justo eget nulla elementum ullamcorper.

Sed fringilla sed lorem commodo vehicula. Morbi eget feugiat enim. Donec et sagittis velit. Nulla congue elit dignissim nibh faucibus, id facilisis nunc dapibus. Quisque nibh neque, dictum sed pulvinar quis, egestas id nunc. Integer dolor augue, euismod nec varius at, cursus vel nulla. Aliquam erat volutpat. Mauris sit amet ultrices nulla. Nam ut lectus scelerisque, sagittis quam vel, sollicitudin sapien. Sed sagittis massa sed justo mattis interdum. Donec a augue enim. Suspendisse dolor ipsum, viverra nec justo vel, laoreet facilisis sapien. Sed ut orci sapien. Phasellus ultricies libero elit, vel tempor purus commodo ut."
,
"Number 3" => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque sit amet rhoncus libero, et ullamcorper erat. In fringilla, felis vel molestie bibendum, ligula massa ullamcorper quam, id lacinia ex est ut sem. Vestibulum at viverra augue. Quisque elit eros, rhoncus nec commodo ut, pellentesque id sem. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Vestibulum eget aliquet nisl. Nullam magna felis, porttitor a fringilla aliquam, porttitor ut diam. Etiam pharetra egestas lobortis. Nunc ac tempor enim.

Sed fermentum eget ipsum ut suscipit. Phasellus suscipit scelerisque orci vitae fermentum. Aliquam nec massa eu lorem egestas mattis. Nulla ac eros dolor. Nulla ac sapien orci. Vivamus tristique sem metus, sed fermentum leo mattis vitae. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus ut molestie justo, eget porta mi. Cras sodales sed nunc a congue. Nam eget tincidunt lectus, et volutpat magna. Sed semper sollicitudin risus, eu vehicula ex elementum eu. Donec dictum, purus venenatis scelerisque porta, tellus metus dignissim leo, sit amet mattis orci nibh eget eros. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nam consequat turpis odio, et ullamcorper velit tempus pellentesque.

Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc malesuada, enim ut mollis ornare, ligula tellus feugiat est, eget viverra orci massa sed purus. Pellentesque ut nisl dolor. Cras malesuada consequat ullamcorper. Duis neque nisl, finibus sit amet volutpat nec, pharetra sit amet leo. Cras sed eleifend nibh. Suspendisse convallis dignissim orci vitae auctor. Etiam viverra metus a ante pharetra, sed facilisis arcu varius. Mauris eu dui vestibulum, blandit risus suscipit, consectetur ligula. Morbi in laoreet libero, a ullamcorper felis. Donec commodo hendrerit maximus. Sed eu ornare orci. Phasellus nec sem velit."

		], "test");
		echo $tabs->get();
		
		echo Theme::container(Theme::modal("Modal content", "modal", "test")->get().Theme::modal_button("Open modal", "modal", "test")->get(), "test")->get();
		
		echo Theme::container(Theme::slider([$img1, $img1, $img1], "test"), "test")->get();
		
		$teller1 = Theme::container("Tell level 1", "test");
		$teller2 = Theme::container("Tell level 2", "test");
		$teller3 = Theme::container("Tell level 3", "test");
		$teller4 = Theme::container("Tell level 4", "test");
		Theme::tell($teller1, 1, "test");
		Theme::tell($teller2, 2, "test");
		Theme::tell($teller3, 3, "test");
		Theme::tell($teller4, 4, "test");
		echo Theme::container(Theme::grid([[$teller1, $teller2, $teller3, $teller4]], "test"), "test")->get();
		echo Theme::container(Theme::grid([[Theme::badge("Orange badge", "test", ["color" => "orange"]), Theme::badge("Red badge", "test", ["color" => "red"]), Theme::badge("Green badge", "test", ["color" => "green"]), Theme::badge("Blue badge", "test", ["color" => "blue"]), Theme::badge("5", "test", ["new" => true])]], "test"), "test")->get();
		echo Theme::container(Theme::breadcrumbs(["Root" => "#1", "Base" => "#2", "Sub" => "#3", "Top" => "#4"], "test"), "test")->get();
		echo Theme::container(Theme::accordion(["A" => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus leo ligula, venenatis vel finibus at, feugiat a augue. Sed pretium turpis elementum mi ornare, mattis efficitur leo ultrices.", "B" => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus leo ligula, venenatis vel finibus at, feugiat a augue. Sed pretium turpis elementum mi ornare, mattis efficitur leo ultrices.", "C" => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus leo ligula, venenatis vel finibus at, feugiat a augue. Sed pretium turpis elementum mi ornare, mattis efficitur leo ultrices.", "D" => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus leo ligula, venenatis vel finibus at, feugiat a augue. Sed pretium turpis elementum mi ornare, mattis efficitur leo ultrices."], "test"), "test")->get();
		
		echo Theme::panel("Panel title", "Panel content.", "test")->get();
		echo Theme::card("Card content", "test")->get();
		
		echo Theme::loader("test")->get();
		
		echo Theme::container(Theme::input_check("Checkbox", "test")->get().
		Theme::input_date("2017-01-26", "test")->get().
		 Theme::input_file("test")->get().
		Theme::input_radio(["Radio 1" => "Value 1", "Radio 2" => "Value 2", "Radio 3" => "Value 3"], "Radio", "test")->get().
		Theme::input_select(["Select 1" => "Value 1", "Select 2" => "Value 2", "Select 3" => "Value 3"], "test")->get().
		Theme::input_submit("Submit", "test")->get().
		Theme::input_text("", "Placeholder", "test")->get().
		Theme::input_range(0, 100, "test")->get(), "test")->get();
	});
});
onEvent("ready", function () {
	Console_Register(CmdViewTheme());
	Console_Register(CmdExportTheme());
	Console_Register(CmdImportTheme());
});

?>