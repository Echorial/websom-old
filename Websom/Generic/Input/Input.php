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
			$formStart .= '<websformloader>'.json_encode($form->loadData).'</websformloader>';
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
	
	
	
	static function get_post ($form) {
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
			
			$m = $form->event("error", [$data]);
			if(get_class($m) !== "Message") throw new Exception("onFailure must return a Message object.");
			Cancel('{"serverMsg": 0, "msg": '.json_encode($errors).', "actions": '.$m->get().'}');
			return false;
		}
		
		$m = $form->event("success", [$data]);
		if(get_class($m) !== "Message") throw new Exception("onSuccess must return a Message object.");
		Cancel('{"serverMsg": 1, "actions": '.$m->get().'}');
		return $data;
		
	}
	
}

onEvent('end', function () {
	
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
			array_push($set, $name.': function (element, name, error) {
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


class Input_List extends Input {
	
	public $listStructure = false;
	public $structure = false;
	
	public $globalName = "List";
	
	function __construct() {
		$this->inputs = [];
	}
	
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
		if ($data === 0) $data = []; //Client sends 0 if the array is empty.
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
				$sIndext++;
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
		
		$rtn = '<inputlist isinput id="'.$this->id.'"><listtemplate style="display: none;">';
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
	
	/**
	* This will create a new Structure object containing the $html string.
	*
	* \param string $html The html string to structure.
	*/
	function __construct($html) {
		$this->html = $html;
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
	* This will replace all the %key%'s in the Structure html with the associated $data value.
	*
	* \param string $data The associative array to use.
	*/
	function get($data) {
		$rtn = $this->html;
		
		foreach ($data as $key => $value)
			$rtn = str_replace('%'.$key.'%', $value, $rtn);
		return $rtn;
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
	
	public $structure = false;
	public $loadData = false;
	
	public $submitOnStart = false;
	
	public $clientEvents = [];
	
	function __construct($name) {
		$this->name = 'global__'.'forms__'.$name;
		$this->inputs = [];
		
		$this->on("success", function ($data) {
			$m = new Message();
			$m->add("form", Message::Success("Success"));
			return $m;
		});
		
		$this->on("error", function ($data) {
			$m = new Message();
			$m->add("form", Message::Error("Error"));
			return $m;
		});
	}
	
	function addInput($name, $input) {
		array_push($this->inputs, ['n' => $name, 'i' => $input]);
		
		Form::$InputCount++;
	}
	
	function onSend() {
		
	}
	
	function client($event, $javascript) {
		$this->clientEvents[$event] = $javascript;
	}
	
	function load($data) {
		$this->loadData = $data;
	}
	
	function check() {
		return InputController::get_post($this);
	}
	
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

onEvent("ready", function () {
	Register_Action(new Action_Success());
	Register_Action(new Action_Error());
});

/**
*
*/
function GetSomeForm() {
	$form = new Form('testForm');
	
	
	$txt = new Text();
	$txt->blank = true;
	
	$txt->max = 10;
	$txt->min = 2;
	
	$form->addInput("Register", $txt);

	
	
	
	$nameList = new Input_Group();
		$nameList->addInput("firstName", $txt);
		$nameList->addInput("lastName", $txt);
		$nameList->structure = new Structure(
			'<div class="list"><div>%firstName%</div><div>%lastName%</div></div>'
		);
	
	$nameLister = new Input_List();
		$nameLister->addInput("list", $nameList);

		$nameLister->listStructure = new Structure(
			'<div class="list"><div>%list%</div><button listremove>Remove</button></div>'
		);
	
	$form->addInput("nameLister", $nameLister);
	
	
	$form->structure = new Structure(
		'<div>Your name: %Register%</div>%nameLister%<div>%Submit%</div>'.(Theme::input_submit("Send", "formSubmit")->get())
	);
	
	$form->on("error", function ($data) {
		$m = new Message();
		$m->add("form", Message::Error("Please fix errors"));
		return $m;
	});
	
	$form->on("success", function ($data) {
		Storage::Set("tempar", $data);
		$m = new Message();
		
		$m->add("form", Message::Success("Got input ".json_encode($data)."."));
		
		return $m;
	});
	
	$form->load(
		json_decode('{"Register":"dssd","nameLister":[{"list":{"firstName":"gdsh","lastName":"sdhsd"}},{"list":{"firstName":"ads","lastName":"hsdh"}}]}')
	);
	
	$form->check();
	
	return $form->get();
}

?>