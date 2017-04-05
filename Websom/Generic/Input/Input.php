<?php
/**
* \defgroup BuiltInInputs Built In Input Objects
*/

/**
* \defgroup Input Input
*
* This is the new input system.
* 
*
*/

$Input_Areas = array();

$Js = include_all(Websom_root."/Generic/Input/Inputs/");

$InputScript = '';
$InputScript .= '<script>
';
foreach($Js as $Name){
	$InputScript .= 'function '.$Name.'_Sanitize(_e, _c, _o) {';
		$InputScript .= CallFunction('Input_'.$Name.'_Sanitize_Client');
	$InputScript .= '}';
	if (function_exists('Input_'.$Name.'_Global_Javascript'))
		$InputScript .= CallFunction('Input_'.$Name.'_Global_Javascript');
}

$InputScript .= '
</script>';

function Get_Input_Scripts() {
	global $InputScript;
	return $InputScript;
}

function Start_Input_Area($id = 'nullid', $mode = 'Strict', $extra = ''){
	global $Input_Areas;
	
	$NewArea['inputs'] = array();
	$NewArea['mode'] = $mode;
	
	array_push($Input_Areas, $NewArea);											//       v      Makes it look complicated       v
	return '<form method="POST" id="'.$id.'" action="" '.$extra.' globalserverid="'.Random_Chars(5).(count($Input_Areas)).Random_Chars(6).'" enctype="multipart/form-data">';
}

function Random_Chars($count) {
	return substr(str_shuffle("1234567890"), 0, $count);
}

function Add_Input($Name, $Options, $value = null){
	global $Input_Areas;
	$Input_Areas[count($Input_Areas)-1]['inputs'][$Name] = $Options;
	$args = 'name="'.$Name.'" ctype="'.$Options['type'].'" ';
	if (isset($Options['submitonchange']))
		$args .= ' submitonchange="true" ';
	if (isset($Options['optional']))
		$args .= ' optionalpost="true" ';
	if (isset($Options['class']))
		$args .= ' class="'.$Options['class'].'" ';
	return CallFunctionArgs("Input_".$Options['type']."_Html_Get", array($Options, $args, $value));
}

function End_Input_Area(){
	return '</form>';
}

function Get_Input_Area(){
	
	global $Input_Areas;

	//Check if the current input area is submited\\
	if (count($_POST) == 0){
		return false;
	}else{
		//TODO: Look at the following mess.\\
		
		if (!isset($_POST['globalserverid'])){
			return false;
		}
		
		$formId = substr($_POST['globalserverid'], 5, 1)-1;
		
		if (!isset($Input_Areas[$formId])) {
			return false;
		}
		
		if ($formId != count($Input_Areas)-1) {
			return false;
		}

		$__POST = $_POST; //Real creative\\ 
		$__POST =  array_merge($__POST, $_FILES);
	
		unset($__POST['globalserverid']);

		if ($Input_Areas[$formId]['mode'] == 'Strict') {
			//if (Array_Key_Compare($Input_Areas[count($Input_Areas)-1]['inputs'], $__POST)){
				foreach ($Input_Areas[$formId]['inputs'] as $InputId => $InputOptions){
					if (isset($InputOptions['optional']))
						if ($__POST[$InputId] == '')
							continue;
					if (function_exists("Input_".$InputOptions['type']."_Override_Value"))
						$__POST[$InputId] = CallFunctionArgs("Input_".$InputOptions['type']."_Override_Value", array($InputId, $InputOptions, $__POST[$InputId]));
					
					$ErrorIf = CallFunctionArgs("Input_".$InputOptions['type']."_Sanitize_Server", array($InputOptions, $__POST[$InputId]));
					if ($ErrorIf !== true){
						Cancel('{"Error": "'.$ErrorIf.'"}');
						return false;
					}
				}
				return $__POST;
			//}
		}else if ($Input_Areas[$formId]['mode'] == 'Loose'){
			foreach($__POST as $Post_Name => $Post_Value){
				foreach ($Input_Areas[$formId]['inputs'] as $InputId => $InputOptions){
					if (strpos($Post_Name, $InputId) !== false){
						if (isset($InputOptions['optional']))
							if ($__POST[$InputId] == '')
								continue;
						if (function_exists("Input_".$InputOptions['type']."_Override_Value"))
							$__POST[$InputId] = CallFunctionArgs("Input_".$InputOptions['type']."_Override_Value", array($InputId, $InputOptions, $__POST[$InputId]));
					
						$ErrorIf = CallFunctionArgs("Input_".$InputOptions['type']."_Sanitize_Server", array($InputOptions, $Post_Value));
					
						if ($ErrorIf !== true) {
							Cancel('{"Error": "'.$ErrorIf.'"}');
							return false;
						}
						continue 2;
					}
				}
			}
			
			return $__POST;
		}
	}
}

function Array_Key_Compare($Array1, $Array2){
	foreach($Array1 as $K => $V){
		if (array_key_exists($K, $Array2) == false){
			return false;
		}
	}
	return true;
}






class InputController {
	static public $staticJavascripts = [];
	static public $clientFormInfo = [];
	
	
	static public function stringify(Form $form) {
		$rtn = '';
		
		$inputs = [];
		$inp = [];
		
		InputController::$clientFormInfo[$form->name]["dynamicFormEvents"] = $form->clientEvents;
		
		foreach ($form->inputs as $input) {
			$input['i']->id = $form->name.'__'.$input['n'];
			$inputs[$input['n']] = InputController::buildify($input['i']);
			$inp[$input['n']] = $inputs[$input['n']]['html'];
			InputController::$clientFormInfo[$form->name][$input['i']->id] = [
				'events' => $inputs[$input['n']]['js'],
				'type' => $input['i']->_type,
				'globalName' => $input['i']->globalName,
				'serverName' => $input['n']
			];
			
		}
		
		$starter = "submit-on-start";
		if (!$form->submitOnStart)
			$starter = '';
		$formStart = '<websform '.$starter.' name="'.$form->name.'">';
		
		if ($form->loadData !== false) {
			$formStart .= '<websformloader>'.json_encode($form->loadData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK | JSON_HEX_TAG).'</websformloader>';
		}
		
		$formEnd = '</websform>';
		
		if (gettype($form->structure) === 'string') throw new Exception("Form structure must be false or an instance of Structure. Found string.");
		if (get_class($form->structure) !== 'Structure') throw new Exception("Form structure must be false or an instance of Structure");
		
		return $formStart.$form->structure->get($inp).$formEnd;
	}
	
	static public function buildify($input) {
		if ($input->_type == 0) {
			return InputController::construct_input($input);
		}else if($input->_type == 1){
			return InputController::construct_group($input);
		}else{
			return InputController::construct_list($input);
		}
	}
	
	static public function construct_input($input){
		$rtn = [];
		
		$rtn['html'] = $input->get([
			
		]);
		
		$rtn['js'] = InputController::build_script($input);
		
		return $rtn;
	}
	
	static public function construct_group($input){
		
	}
	
	static public function construct_list($input){
		
	}
	
	static public function build_script($input){
		
		InputController::register_js($input);
		return false;
		
	}
	
	static public function register_js($input) {
		InputController::$staticJavascripts[$input->globalName] = InputController::get_js($input);
	}
	
	static public function get_js($input) {
		return [
			'validate' => $input->validate_client(),
			'send' => $input->send(),
			'error' => $input->error(),
			'init' => $input->init(),
			'load' => $input->load()
		];
	}
	
	
	/**
	* This will try to get a single input value for the $inputName in the $form.
	* Note: This does not send any messages.
	* 
	* @param Form $form The form to check against.
	* @param string $inputName The name of the input to find.
	* 
	* @return An array [true if input was validated or false if not , the error message if any , the input value if any or null if not]
	*/
	static function get_single_input_value($form, $inputName) {
		$inp = $form->getInput($inputName);
		
		if ($inp === false) {
			return [false, "No input with that name found.", null];
		}
		
		if (isset($_POST['inputPost'][$form->name.'__'.$inputName])) {
			//Deserialize the input from the client and store it.
			$inpValue = $inp->receive($_POST['inputPost'][$form->name.'__'.$inputName]);
			//Validate the input on the server.
			$error = $inp->validate_server($inpValue);
			if ($error !== true) {
				return [false, $error, null];
			}
			
			return [true, "", $inpValue];
		}else{
			return [false, "Not submited.", null];
		}
	}
	
	static function get_post_and_message ($form) {
		if (!isset($_POST)) return false;
		if (!isset($_POST['inputPost'])) return false;
		if ($form->name !== $_POST['inputPost_Form']) return false;
		
		$errors = [];
		
		$data = [];
		
		foreach ($form->inputs as $input) {
			//Check if input sent from the client exists.
			if (isset($_POST['inputPost'][$form->name.'__'.$input['n']])) {
				//Deserialize the input from the client and store it.
				$data[$input['n']] = $input['i']->receive($_POST['inputPost'][$form->name.'__'.$input['n']]);
				//Validate the input on the server.
				$error = $input['i']->validate_server($data[$input['n']]);
				if ($error !== true) {
					$errors[$form->name.'__'.$input['n']] = $error;
				}
			}
		}
		
		$passed = false;
		
		if (isset($_POST["inputPost_Pass"])) {
			$passed = $_POST["inputPost_Pass"];
		}
		
		return [$data, $errors, $passed];
	}
	
	static function get_post ($form, LateCall $sendMessages = null) {
		$de = self::get_post_and_message($form);
		
		if ($de === false)
			return false;
		
		if (count($de[1]) > 0) {
			LateCall::inject($sendMessages, function () use ($de, $form) {
				self::send_error_messages($form, $de[0], $de[1]);
			});
			return false;
		}
		
		LateCall::inject($sendMessages, function () use ($de, $form) {
			if ($de[2] !== false) {
				Cancel(json_encode($form->event("pass", [$de[2], $de[0]], false)));
				return $de[0];
			}
				
			self::send_success_messages($form, $de[0]);
		});
		
		return $de[0];
		
		/* Remove after approved 11/29/16
		if (!isset($_POST)) return false;
		if (!isset($_POST['inputPost'])) return false;
		if ($form->name !== $_POST['inputPost_Form']) return false;
		
		$errors = [];
		
		$data = [];
		
		foreach ($form->inputs as $input) {
			//Check if input sent from the client exists.
			if (isset($_POST['inputPost'][$form->name.'__'.$input['n']])) {
				//Deserialize the input from the client and store it.
				$data[$input['n']] = $input['i']->receive($_POST['inputPost'][$form->name.'__'.$input['n']]);
				//Validate the input on the server.
				$error = $input['i']->validate_server($data[$input['n']]);
				if ($error !== true) {
					$errors[$form->name.'__'.$input['n']] = $error;
				}
			}
		}
		
		
		
		if (count($errors) != 0) {
			
			$m = $form->event("error", [$data], false);
			if(get_class($m) !== "Message") throw new Exception("error event must return a Message object.");
			
			Cancel('{"serverMsg": 0, "msg": '.json_encode($errors).', "actions": '.$m->get().'}');
			return false;
		}
		
		$m = $form->event("success", [$data], false);
		if(get_class($m) !== "Message") throw new Exception("onSuccess must return a Message object.");
		Cancel('{"serverMsg": 1, "actions": '.$m->get().'}');
		return $data;
		*/
	}
	
	static function send_success_messages($form, $data) {
		$m = $form->event("success", [$data], false);
		if(get_class($m) !== "Message") throw new Exception("success event must return a Message object.");
		Cancel('{"serverMsg": 1, "actions": '.$m->get().'}');	
	}
	
	static function send_error_messages($form, $data, $errors) {
		$m = $form->event("error", [$data, $errors], false);
		if (get_class($m) !== "Message") throw new Exception("error event must return a Message object.");
		
		Cancel('{"serverMsg": 0, "msg": '.json_encode($errors).', "actions": '.$m->get().'}');
	}
	
}

onEvent('endAfter', function () {
	
	$values = [];
	
	foreach (InputController::$staticJavascripts as $gn => $events) {
		$set = [];
		
		foreach ($events as $name => $event) {
			if ($name == "load") {
				array_push($set, $name.': function (element, data) {
					'.$event.'
				}');
				continue;
			}
			array_push($set, $name.': function (element, name, error, later) {
				'.$event.'
			}');
		}
		array_push($values, $gn.': {'.implode(',', $set).'}');
	}
	
	
	foreach (InputController::$clientFormInfo as $fn => $inps) {
		$set = [];
		foreach ($inps as $inputId => $inputInfo) {
			if ($inputId == "dynamicFormEvents") {
				
				$eventSets = [];
				foreach ($inputInfo as $dfen => $func) {
					array_push($eventSets, $dfen.": function (event) {".$func."}");
				}
				$set[$inputId] = $inputId.": {".implode(',', $eventSets)."}";
			}else{
				$js = [];
				if (isset($inputInfo['event']) AND $inputInfo['event'] !== false)
					foreach ($inputInfo['event'] as $funcName => $funcBody)
						array_push($js, $funcName.': function (element, name, error) {
							'.$funcBody.'
						}');
						
				$events = 'false';
				if (isset($inputInfo['event']) AND $inputInfo['event'] !== false){
					$events = '{'.implode(',', $js).'}';
				}
				$set[$inputId] = $inputId.': {type: '.$inputInfo['type'].', globalName: "'.$inputInfo['globalName'].'", events: '.$events.', serverName: "'.$inputInfo['serverName'].'"}';
			}
		}
		array_push($values, $fn.': {'.implode(',', $set).'}');
	}
	
	
	Javascript::Set('InputForms', '{'.implode(',', $values).'}');
});









/**
* \ingroup input
*
* This class is a template class for creating custom input types.
*
* Simply extend `Input`
*
*
* Magic javascript variables: 
* 	root element: `element`
* 	name: `name`
* 	[error: the error string(if there is one)]
* 	
*
*/
class Input {
	
	public $globalName = '_NULL_';
	
	public $id = '_NULL_';
	
	public $_type = 0;
	
	protected function doVisible(Element $element) {
		if (!$this->visible)
			$element->addClass("no-display");
	}
	
	/**
	* Inputs will check this when Input::get() is called and set its visibility accordingly.
	*/
	public $visible = true;
	
	/**
	* This is the method called by websom to get the input html string.
	*/
	public function get() {
		
	}
	
	/**
	* This is the javascript function that is called on input start.
	*/
	public function init() {
		return '';
	}
	
	/**
	* Return true or false if value is valid or not.
	*
	* <div class="note">This is called after the `receive()` method</div>
	*/
	public function validate_server($data) {
		
	}
	
	/**
	* Return a javascript string.
	*
	* 
	*/
	public function validate_client() {
		return 'return true';
	}
	
	/**
	* Return a javascript string that will return the serialized input.
	*
	* 
	*/
	public function send() {
		return false;
	}
	
	/**
	* Return a javascript string that will display the `error`.
	*
	* 
	*/
	public function error() {
		return "$(element).after(error);";
	}
	
	/**
	* Deserialize the input sent from the client, then return it.
	*/
	public function receive($data) {
		
	}
	
	/**
	* Return a javascript string that will load the `data` into the input/element.
	*
	* Magic variables:
	* 	- `data`: The data to load
	*/
	public function load() {
		return 'alert("Cannot load into input.");';
	}
	
	/**
	* This is called on the server when loading into a form input.
	*/
	public function into($val) {
		return $val;
	}
}


class Input_Group extends Input {
	
	public $groupStructure = false;
	public $structure = false;
	
	public $globalName = "Group";
	
	function __construct() {
		$this->inputs = [];
	}
	
	function addInput($name, $input) {
		array_push($this->inputs, ['n' => $name, 'i' => $input]);
	}
	
	function send() {
		return '
		
		var data = {};
		var groupItems = $.data(element, "groupids");
		for (var l in groupItems) {
			if (groupItems[l] === false) continue;
			for (var i = 0; i < groupItems[l].items.length; i++) {
				var input = $("#"+groupItems[l].items[i]);				
				
				if(!isset(input.attr("groupgn"))) continue;
				
				if (error !== true) {
					data[input.attr("groupn")] = Websform.build.callInput({events: false, globalName: input.attr("groupgn")}, "send")(input[0], input.attr("id"));
				}
			}
		}
		return data;
		
		';
	}
	
	function validate_client() {
		return '
		var hasError = true;
		var groupItems = $.data(element, "groupids");
		for (var l in groupItems) {
			if (groupItems[l] === false) continue;
			for (var i = 0; i < groupItems[l].items.length; i++) {
				var input = $("#"+groupItems[l].items[i]);
				if(!isset(input.attr("groupgn"))) continue;
				var error = Websform.build.callInput({events: false, globalName: input.attr("groupgn")}, "validate")(input[0], input.attr("id"));
				
				if (error !== true) {
					Websform.build.callInput({events: false, globalName: input.attr("groupgn")}, "error")(input[0], input.attr("id"), error);
					hasError = false;
				}
			}
		}
		return hasError;
		';
	}
	
	function validate_server($data) {
		
		return true;
		
	}
	
	function getInputAt($name) {
		foreach ($this->inputs as $i) {
			if ($i['n'] == $name) return $i['i'];
		}
		return false;
	}
	
	function receive($data) {
		$rtn = [];
		$item = $data;
			$sIndex = 0;
			if (count($item) != count($this->inputs)) return "Unbalanced input count. Server:".count($this->inputs).", Client:".count($item).".";
			foreach ($item as $iName => $iValue) { 
				$cInput = $this->getInputAt($iName);
				if ($cInput === false) return 'Unable to find input template at '.$iName;
				$rtn[$iName] = $cInput->receive($iValue);
				
				$error = $cInput->validate_server($rtn[$iName]);
				if ($error !== true) {
					return "Input error at ".$sIndex.':'.$iName.' "'.$error.'"';
				}
				$sIndext++;
				
			}
		return $rtn;
		
	}
	
	function load() {
		return '
		var ids = $.data(element, "groupids");
		for (var i = 0; i < ids[Object.keys(ids)[0]].items.length; i++) {
			var input = $("#"+ids[Object.keys(ids)[0]].items[i]);
			if (input.attr("groupn") in data) {
				Websform.build.callInput({events: false, globalName: input.attr("groupgn")}, "load")(input[0], data[input.attr("groupn")]);
			}
		}
		
		';
	}
	
	function init() {
		return '
		$(element).children("grouptemplate").find("groupinfo *[isinput]").each(function () {
			var that = $(this);
			var info = that.closest("groupinfo");
			that.attr("groupgn", info.attr("globalname"));
			that.attr("groupn", info.attr("groupn"));
			info.after(info.html());
			
			info.remove();
		});
		
		$.data(element, "grouptemplate", $(element).children("grouptemplate").html());
		$.data(element, "groupids", {});
		var elem = $(element);
		elem.children("grouptemplate").remove();
		
		var eleme = $(element);
		var node = eleme[0];
		
		var newId = eleme.attr("id")+"__itm__"+Object.keys($.data(element, "groupids")).length;
		eleme.append("<groupitems id=\'"+newId+"\'>"+$.data(element, "grouptemplate")+"</groupitems>");
		
		var subItems = [];
		eleme.find("#"+newId+" [isinput]").each(function () {
			var input = $(this);
			var sId = newId+"_subitm"+subItems.length;
			input.attr("id", sId);
			
			
			Websform.build.callInput({events: false, globalName: input.attr("groupgn")}, "init")(input[0], input.attr("id"));
			
			
			
			subItems.push(sId);
		});
		
		$.data(element, "groupids")[newId] = {items: subItems};
		
		
		window["compareElem"] = element;
		';
	}
	
	function get() {
		$inputs = [];
		
		foreach ($this->inputs as $input) {
			$d = InputController::construct_input($input['i']);
			$inputs[$input['n']] = '<groupinfo groupn="'.$input['n'].'" globalname="'.$input['i']->globalName.'">'.$d['html'].'</groupinfo>';
		}
		
		$rtn = '<inputgroup isinput id="'.$this->id.'"><grouptemplate style="display: none;">';
		
		if ($this->structure === false) {
			$rtn .= (new Structure(Structure::lister($inputs)))->get($inputs);
		}else{
			$rtn .= $this->structure->get($inputs);
		}
		
		$rtn .= '</grouptemplate>';
		

		
		
		
		
		$rtn .= '</inputgroup>';
		return $rtn;
	}
}

/**
* \ingroup BuiltInInputs
* 
* The Input_List is a simple input that can hold a template of inputs and let the user add more to it.
* 
* 
*/
class Input_List extends Input {
	
	public $listStructure = false;
	public $structure = false;
	
	public $max_items = 9999;
	public $min_items = 0;
	
	public $globalName = "List";
	
	function __construct() {
		$this->inputs = [];
	}
	
	/**
	* Add an input to the list template.
	*/
	function addInput($name, $input) {
		array_push($this->inputs, ['n' => $name, 'i' => $input]);
	}
	
	function send() {
		return '
		var data = [];
		var listItems = $.data(element, "listids");
		for (var l in listItems) {
			if (listItems[l] === false) continue;
			var currentItem = {};
			for (var i = 0; i < listItems[l].items.length; i++) {
				var input = $("#"+listItems[l].items[i]);				
				
				if(!isset(input.attr("listgn"))) continue;
				
				if (error !== true) {
					currentItem[input.attr("listn")] = Websform.build.callInput({events: false, globalName: input.attr("listgn")}, "send")(input[0], input.attr("id"));
				}
			}
			data.push(currentItem);
		}
		
		if (data.length == 0) data = 0; //Empty arrays are not posting. The server input code turns this into an empty array.
		
		return data;
		';
	}
	
	function validate_client() {
		return '
		var hasError = true;
		var listItems = $.data(element, "listids");
		var lil = Object.keys(listItems).length;
		if (lil > parseInt($(element).attr("data-max-items")))
			return "Too many items. Maximum amount is "+parseInt($(element).attr("data-max-items"));
		if (lil < parseInt($(element).attr("data-min-items")))
			return "Too few items. Minimum amount is "+parseInt($(element).attr("data-min-items"));
		
		for (var l in listItems) {
			if (listItems[l] === false) continue;
			for (var i = 0; i < listItems[l].items.length; i++) {
				var input = $("#"+listItems[l].items[i]);
				if(!isset(input.attr("listgn"))) continue;
				var error = Websform.build.callInput({events: false, globalName: input.attr("listgn")}, "validate")(input[0], input.attr("id"));
				
				if (error !== true) {
					
					var _error = Websform.build.callInput({events: false, globalName: input.attr("listgn")}, "error")(input[0], input.attr("id"), error);
					if ("inputError" in Websform.currentDynamicEvents)
						Websform.currentDynamicEvents["inputError"]({
							$error: _error,
							$form: Websform.currentForm,
							$element: input
						});
					hasError = false;
				}
			}
		}
		return hasError;
		';
	}
	
	function validate_server($data) {
		if (gettype($data) == "string")
			return false;
		
		return true;
		
	}
	
	function getInputAt($name) {
		foreach ($this->inputs as $i) {
			if ($i['n'] == $name) return $i['i'];
		}
		return false;
	}
	
	function receive($data) {
		$rtn = [];
		if ($data === "0") $data = []; //Client sends 0 if the array is empty.
		
		if (count($data) > $this->max_items)
			return "Too many items. The maximum amount is ".$this->max_items;		
		if (count($data) > $this->max_items)
			return "Too few items. The minimum amount is ".$this->min_items;
		
		foreach ($data as $item) {
			$cItem = [];
			$sIndex = 0;
			if (count($item) != count($this->inputs)) return "Unbalanced input count. Server:".count($this->inputs).", Client:".count($item).".";
			foreach ($item as $iName => $iValue) { 
				$cInput = $this->getInputAt($iName);
				if ($cInput === false) return 'Unable to find input template at '.$iName;
				$cItem[$iName] = $cInput->receive($iValue);
				$error = $cInput->validate_server($cItem[$iName]);
				if ($error !== true) {
					return "Input error at ".$sIndex.':'.$iName.' "'.json_encode($error).'"';
				}
				$sIndex++;
			}
			array_push($rtn, $cItem);
		}
		
		return $rtn;
	}
	
	function load() {
		return '

		for (var i = 0; i < data.length; i++) {
			$(element).trigger("addtolist", [data[i]]);
		}
		
		';
	}
	
	function init() {
		return '
		
		$(element).children("listtemplate").find("listinfo *[isinput]").each(function () {
			var that = $(this);
			var info = that.closest("listinfo");
			that.attr("listgn", info.attr("globalname"));
			that.attr("listn", info.attr("listn"));
			info.after(info.html());
			
			info.remove();
		});
		
		$.data(element, "listtemplate", $(element).children("listtemplate").html());
		$.data(element, "listarea", $(element).children("listarea"));
		$.data(element, "listids", {});
		var elem = $(element);
		elem.children("listtemplate").remove();
		
		
		if (!isset(window["__list_globalinit"])) {
			
		$(document).on("addtolist", "inputlist", function (e, data) {
			e.stopImmediatePropagation();
			var eleme = $(this);
			var node = eleme[0];
			
			var listarea = $.data(node, "listarea");
			var newId = eleme.attr("id")+"__itm__"+Object.keys($.data(node, "listids")).length;
			listarea.append("<listitem id=\'"+newId+"\'>"+$.data(node, "listtemplate")+"</listitem>");
			
			var subItems = [];
			listarea.find("#"+newId+" [isinput]").each(function () {
				var input = $(this);
			
					
				
				if (input.closest("listarea")[0] !== listarea[0]) return true;
				var sId = newId+"_subitm"+subItems.length;
				input.attr("id", sId);
				if (input.hasAttr("list-get-new-id")) {
					
					var fors = $("*[for="+input.attr("list-get-new-id")+"]");
					var idInp = $("#"+input.attr("list-get-new-id"));
					idInp.uniqueId();
					fors.attr("for", idInp.attr("id"));
				}
				
				
				Websform.build.callInput({events: false, globalName: input.attr("listgn")}, "init")(input[0], input.attr("id"));
				
				if (isset(data))
				if (input.attr("listn") in data) {
					Websform.build.callInput({events: false, globalName: input.attr("listgn")}, "load")(input[0], data[input.attr("listn")]);
				}
				
				subItems.push(sId);
				
			});
			
			$.data(node, "listids")[newId] = {items: subItems};
			CallEventHook("themeReload", [listarea]);
		});
		
		
			window["__list_globalinit"] = true;
			$(document).on("click", "[listadd]", function () {
				var that = $(this);
				var list = that.closest("inputlist");
				list.trigger("addtolist");
				
			});
			
			$(document).on("click", "[listremove]", function () {
				var that = $(this);
				var listitem = that.closest("listitem");
				
				$.data(element, "listids")[listitem.attr("id")] = false;
				listitem.remove();
			});
		}
		
		';
	}
	
	function get() {
		$inputs = [];
		
		foreach ($this->inputs as $input) {
			$d = InputController::construct_input($input['i']);
			$inputs[$input['n']] = '<listinfo listn="'.$input['n'].'" globalname="'.$input['i']->globalName.'">'.$d['html'].'</listinfo>';
		}
		
		$rtn = '<inputlist isinput id="'.$this->id.'" data-max-items="'.$this->max_items.'" data-min-items="'.$this->min_items.'"><listtemplate style="display: none;">';
		if ($this->listStructure === false) {
			$rtn .= (new Structure(Structure::lister($inputs)))->get($inputs);
		}else{
			$rtn .= $this->listStructure->get($inputs);
		}
		
		$rtn .= '</listtemplate>';
		
		$struct = [
			'list' => '<listarea></listarea>',
			'add' => '<button listadd>Add</button>'
		];
		
		if ($this->structure === false) {
			$rtn .= (new Structure(Structure::lister($struct)))->get($struct);
		}else{
			$rtn .= $this->structure->get($struct);
		}
		
		
		$rtn .= '</inputlist>';
		return $rtn;
	}
}


/**
* \ingroup Input
*
* The Structure object is an object used to "structure" html and dynamic values.
* Structures are mainly used in Control_Structures and The Input system.
*
* Example:
* \code
* 	$htmlStruct = new Structure("<div>%name%</div><div>%description%</div>"); //The %name% and %description% are replaced by values.
* 	
* 	echo $htmlStruct->get(["name" => "John Smith", "description" => "A popular person"]); //This calls the Structure get method which accepts an associative array, and returns a string with the `%key%` replaced with `value`.
* \endcode
*
*/
class Structure {
	public $html = '';
	public $callback = false;
	
	/**
	* This will create a new Structure object containing the $html string.
	*
	* \param string/function $html The html string to structure. Or pass a function in and when the structure is used the function will be called with the array of params.
	*/
	function __construct($html) {
		if (is_callable($html)) {
			$this->callback = $html;
		}else{
			$this->html = $html;
		}
	}
	
	/**
	* This will return a string that contains the list of keys in $data.
	*
	* \param string $data The associative array to use.
	*/
	static public function lister($data) {
		$rtn = '';
		foreach ($data as $key => $value)
			$rtn .= '%'.$key.'%';
		return $rtn;
	}
	
	/**
	* Appends the $stuff to the end of the structure html string.
	*/
	public function inject($stuff) {
		$this->html .= $stuff;
	}
	
	/**
	* This will replace all the %key%'s in the Structure html with the associated $data value.
	*
	* \param string $data The associative array to use.
	*/
	function get($data) {
		if ($this->callback === false) {
			$rtn = $this->html;
			
			foreach ($data as $key => $value) {
				$rtn = str_replace('%'.$key.'%', $value, $rtn);
			}
			
			return $rtn;
		}else{
			return call_user_func($this->callback, $data);
		}
	}
}




/**
* \ingroup Input
*
* A Message is a list of actions to be called on the client. Message's are sent by Input Form's and are recived via ajax then processed on the client.
*
* \code
* 	$msg = new Message();
* 	$msg->add("form", Message::Success("That was a success.")); //This will show a success message after the form.
* 	$msg->add("name", Message::Error("No good.", 5)); //This will put an error message after the "name" input and the error will be removed after 5 seconds
* 	$msg->add("form", Message::Action("Alert", ["msg" => "Hello World!"])); //This will add the custom action `Alert` to the message list with the data associated with it.
* \endcode
*/
class Message {
	
	/**
	* The messages that are added.
	*/
	public $messages = [];
	
	/**
	* This will add a action to the $name element.
	*
	* \param string $name This can be a input name or "form".
	* \param array $action Use Message::Action() to structure an array for actions.
	*/
	function add($name, $action) {
		if (!isset($this->messages[$name])) $this->messages[$name] = [];
		array_push($this->messages[$name], $action);
	}
	
	function get() {
		return json_encode($this->messages);
	}
	
	/**
	* This is a built in Message/Action that displays a success box after the element.
	*
	* \param string $msg This is the message that is displayed.
	* \param integer $duration This is how long the message will be displayed for in seconds.
	*/
	static public function Success($msg, $duration = 10) {
		return [
			'__type' => 'Success',
			'msg' => $msg,
			'dur' => $duration
		];
	}

	/**
	* This is a built in Message/Action that takes the client to a location.
	*
	* \param string $location This is the location to send the client to.
	*/
	static public function Go($location) {
		return [
			'__type' => 'Forward',
			'url' => $location
		];
	}
	
	/**
	* Same as Message::Success() but with an error.
	*/
	static public function Error($msg, $duration = 10) {
		return [
			'__type' => 'Error',
			'msg' => $msg,
			'dur' => $duration
		];
	}
	
	/**
	* For creating quick errors.
	*/
	static public function QuickError($errorText, $duration = 30) {
		$m = new Message();
		$e = Theme::container("", "QuickError");
		$e->insert($errorText);
		Theme::tell($e, 4, "QuickError");
		$m->add("form", Message::Error($e->get(), $duration));
		return $m;
	}
		
	/**
	* For creating quick success.
	*/
	static public function QuickSuccess($successText, $duration = 30) {
		$m = new Message();
		$e = Theme::container("", "QuickSuccess");
		$e->insert($successText);
		Theme::tell($e, 1, "QuickSuccess");
		$m->add("form", Message::Success($e->get(), $duration));
		return $m;
	}
	
	/**
	* This will return a formated action for use with Message->Add().
	*
	* \param string $actionName The name of the action to use.
	* \param string $data The action data to use.
	*/
	static public function Action($actionName, $data) {
		$data['__type'] = $actionName;
		return $data;
	}
}


/**
* \ingroup Input
*
* The form class is used to create responsive and fast user input forms.
* 
* Events:
* 	- "success"($data): Called on the form submit when it is validated. Return a Message object that will be sent to the client.
* 	- "error"($data, $msg): Called on the form submit when the input is not valid. Return a Message object that will be sent to the client.
* 
* Client events:
* 	- inputError:
* 		- $form: The form element.
* 		- $element: The input element.
* 		- $error: The error element.
* 	- receive: When a `Message` is received from the server.
* 		- $form: The jQuery form element.
* 		- message: The message object received from the server.
* 		- data: The serialized input posted to the server.
* 	- submit: When the form is submited. Before input validation
* 	 	- $form: The jQuery form element.
* 	- post: Before the data is posted to the server.
* 	 	- $form: The jQuery form element.
* 	 	- data: The form's serialized input. If the data is false then the input was invalid.
*/
class Form extends Hookable {
	static public $InputCount = 0;
	
	/**
	* 
	* You should override this with a Structure object if you wish to customize the layout of the form html.
	*/
	public $structure = false;
	public $loadData = false;
	
	public $submitOnStart = false;
	
	public $inputs = [];
	
	public $clientEvents = [];
	
	/**
	* This will construct the Form object and set the name.
	* 
	* @param string $name The form name. Try to make this unique.
	* 
	*/
	function __construct($name) {
		$this->name = 'global__'.'forms__'.$name;
		$this->inputs = [];
		
		$this->client("submit", "
			$(event.\$form).find('.input_error').remove();
		");
		
		$this->client("post", "
			if (event.data === false) return;
			$(event.\$form).append('<div class=\"loading\">".Theme::loader("Form.wait")->get()."</div>');
			$(event.\$form).find('input[type=submit]').addClass('disabled').attr('disabled', 'disabled');
		");
		
		$this->client("receive", "
			$(event.\$form).children('.loading').remove();
			$(event.\$form).find('input[type=submit]').removeClass('disabled').removeAttr('disabled');
			$(event.\$form).children('.error, .success').hide(function () {
				$(this).remove();
			}, 5000);
		");
				
		$this->client("inputError", "$(event.\$error).fadeOut(100);$(event.\$error).addClass('input_error');$(event.\$error).fadeIn(100);");
		
		$this->on("error", function ($data, $msg) {
			$m = new Message();
			$e = Theme::container("", "Form.error");
			$e->insert("Error");
			Theme::tell($e, 4, "Form.error");
			$m->add("form", Message::Error($e->get()));
			return $m;
		});

		$this->on("success", function ($data) {
			$m = new Message();
			$e = Theme::container("", "Form.success");
			$e->insert("Success");
			Theme::tell($e, 1, "Form.success");
			$m->add("form", Message::Success($e->get()));
			return $m;
		});
	}
	
	/**
	* Adds an input instance to the form structure.
	* 
	* @param string $name The input reference name. Used in structure like so %the name%
	* @param Input $input An instance of a `Input` based class.
	* 
	*/
	function addInput($name, $input) {
		array_push($this->inputs, ['n' => $name, 'i' => $input]);
		
		Form::$InputCount++;
	}
	
	function onSend() {
		
	}
	
	function client($event, $javascript) {
		$this->clientEvents[$event] = $javascript;
	}
	
	
	/**
	* This is a wrapper for InputController::get_single_input_value().
	*/
	function getSingleValue($inputName) {
		return InputController::get_single_input_value($this, $inputName);
	}
	
	/**
	* Loads a key/value array into the form
	*/
	function load($data) {
		$set = [];
		foreach ($data as $k => $v) {
			$set[$k] = $this->getInput($k)->into($v);
		}
		$this->loadData = $set;
	}
	
	/**
	* Checks if the form has been submited.
	* 
	* @return false if no data was sent or a key/value array with input names as the key and input values as the value.
	*/
	function check(LateCall $sendMessages = null) {
		return InputController::get_post($this, $sendMessages);
	}
	
	/**
	* Checks form but does not send any messages. Returns errors as well.
	* Note: use \code if (count($errors > 0)) {echo "Had an error";} \endcode to check for errors.
	* 
	* @return array of [inputData[inputName, inputValue], errors[]] or false if this form was not submited.
	*/
	function rawCheck() {
		return InputController::get_post_and_message($this);
	}
	
	/**
	* This sends the error or success message(s) based on the $errors param
	* 
	* @param array $data The data to send to the error/success event.
	* @param array $errors The error array to check against.
	* 
	* @return bool If a success message was sent this is true, or false if error.
	*/
	function sendMessages($data, $errors) {
		if (count($errors) != 0) {
			InputController::send_error_messages($this, $data, $errors);
			return false;
		}
		
		InputController::send_success_messages($this, $data);
		return true;
	}
	
	/**
	* Used to get a form input based on its name.
	* 
	* @param string $name The name to search for.
	* 
	* @return Input The input found, or false if not found.
	*/
	function &getInput($name) {
		foreach ($this->inputs as $inp) {
			if ($name == $inp['n'])
				return $inp['i'];
		}
		return false;
	}
	
	/**
	* This will return a html string for displaying the form on a webpage.
	*/
	function get() {
		if (!$this->structure) {
			$s = '';
			foreach($this->inputs as $i) {
				$s .= '%'.$i['n'].'%';
				
			}
			$this->structure = new Structure($s);
		}
		
		return InputController::stringify($this);
	}
	
}




class Action_Remove extends Action {
	public $name = "Remove";
	
	function javascript() {
		return '$(element).slideUp(function() {$(this).remove();});';
	}
}

class Action_Success extends Action {
	public $name = "Success";
	
	function javascript() {
		return '
			var msg = $("<div class=\'success\'>"+data["msg"]+"</div>");
			msg.insertAfter(element);
			setTimeout(function () {
				msg.fadeOut(function () {
					msg.remove();
				});
			}, data["dur"]*1000);
		';
	}
}

class Action_Error extends Action {
	public $name = "Error";
	
	function javascript() {
		return '
			var msg = $("<div class=\'error\'>"+data["msg"]+"</div>");
			msg.insertAfter(element);
			setTimeout(function () {
				msg.fadeOut(function () {
					msg.remove();
				});
			}, data["dur"]*100);
		';
	}
}

class Action_Forward extends Action {
	public $name = "Forward";
	
	function javascript() {
		return 'window.location.href = data["url"];';
	}
}

onEvent("ready", function () {
	Register_Action(new Action_Success());
	Register_Action(new Action_Forward());
	Register_Action(new Action_Error());
	Register_Action(new Action_Remove());
});



?>