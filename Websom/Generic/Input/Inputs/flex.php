<?php

/**
* \ingroup BuiltInInputs
* 
* The Input_Flex is a dynamic input.
*/
class Input_Flex extends Input {
	
	///\cond
	public $inputs;
	
	public $globalName = "Flex";
	
	static public $index = 0;
	
	///\endcond
	
	public $noInputFoundError = "Invalid input type";
	
	function __construct() {
		$this->inputs = [];
		self::$index++;
	}
	
	/**
	* Add a dynamic route.
	*/
	function addRoute($refName, $check, $input, $key, $default = false) {
		array_push($this->inputs, ["n" => $refName,  "c" => $check, "i" => $input, "k" => $key, "d" => $default]);
	}
	
	function send() {
		return 'var parent = $(element).children("inputroutes").children("inputroute.flex-input-on");
var input = parent.children("[isinput]");
if (input.length == 0)
	return false;
var send = Websform.build.callInput({events: false, globalName: parent.attr("globalname")}, "send")(input[0], input.attr("id"));
return {type: parent.attr("name"), value: send}';
	}
	
	function validate_client() {
		return 'var hasError = true;
var parent = $(element).children("inputroutes").children("inputroute.flex-input-on");
if (parent.length == 0) {
	return $(element).attr("data-no-select-error");
}

var input = parent.children("[isinput]");

var error = Websform.build.callInput({events: false, globalName: parent.attr("globalname")}, "validate")(input[0], input.attr("id"));

if (error !== true) {
	var _error = Websform.build.callInput({events: false, globalName: parent.attr("globalname")}, "error")(input[0], input.attr("id"), error);
	if ("inputError" in Websform.currentDynamicEvents)
		Websform.currentDynamicEvents["inputError"]({
			$error: _error,
			$form: Websform.currentForm,
			$element: input
		});
	hasError = false;
}

return hasError;';
	}
	
	function validate_server($data) {
		if ($data === false)
			return "Error";
		
		if (gettype($data) == "string")
			return $data;
		
		return true;
	}
	
	function receive($data) {
		if (!is_array($data))
			return false;
		
		if (!isset($data["type"]))
			return false;
		
		if (!isset($data["value"]))
			return false;
		
		foreach ($this->inputs as $inp) {
			if ($data["type"] == $inp["n"]) {
				$rData = $inp["i"]->receive($data["value"]);
				$err = $inp["i"]->validate_server($rData);
				
				if ($err !== true)
					return $err;
				return ["type" => $data["type"], "value" => $rData];
			}
		}
		return false;
	}
	
	function load() {
		return '$(element).children("inputroutes").children("inputroute").removeClass("flex-input-on").hide(); var input = $(element).children("inputroutes").children("inputroute[name="+data.type+"]"); Websform.build.callInput({events: false, globalName: input.attr("globalname")}, "load")(input.children("[isinput]")[0], data.value[input.attr("data-key")]); input.show(); input.addClass("flex-input-on")';
	}
	
	function init() {
return 'var that = this; var reCheck = function () {
	$(element).closest("websform").find(".input_error").remove();
	var inputData = Websform.getInputData($(element).closest("websform"), true);
	var flexData = flexInput[$(element).attr("data-flex-id")];
	
	var scopeData = that.getScopeData();
	
	$(element).children("inputroutes").children("inputroute").each(function () {
		$(this).children("[isinput]").attr("id", $(element).attr("id")+"_flex_itm_"+$(this).attr("name"));
		Websform.build.callInput({events: false, globalName: $(this).attr("globalname")}, "init")($(this).children("[isinput]")[0], $(this).children("[isinput]").attr("id"));
		try {
			if (flexData[$(this).attr("name")].check(inputData, scopeData)) {
				$(this).show();
				$(this).addClass("flex-input-on");
			}else{
				$(this).hide();
				$(this).removeClass("flex-input-on");
			}
		} catch(e) {
			
		}
	});
}
try {
	reCheck();
}catch(e) {
	
}
$(element).closest("websform").on("change", function () {
	reCheck();
});';
	}
	
	function get() {
		$rtn = "";
		$scripts = [];
		$didDefault = false;
		foreach ($this->inputs as $i => $inp) {
			$d = InputController::construct_input($inp['i']);
			
			if ($inp["d"])
				$didDefault = true;
			
			$default = "style='display: none;'";
			
			if ($inp["d"] OR ($i == count($this->inputs)-1 AND !$didDefault))
				$default = "class='flex-input-on'";
			
			$rtn .= "<inputroute data-key='".$inp["k"]."' name='".$inp["n"]."' ".$default." globalname='".$inp['i']->globalName."'>".$d['html']."</inputroute>";
			$scripts[] = $inp["n"].": {check: function (input, scopeInput) {".$inp["c"]."}}";
		}
		
		$script = "if (typeof flexInput == 'undefined') flexInput = {}; flexInput['".self::$index."'] = {".implode(",", $scripts)."}";
		return "<flexinput isinput id='".$this->id."' data-flex-id='".self::$index."'><script>".$script.";</script><inputroutes>".$rtn."</inputroutes></flexinput>";
	}
}

?>