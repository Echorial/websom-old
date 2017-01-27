<?php

/**
* \ingroup BuiltInInputs
* 
* The Dictionary input is based on the Theme::input_dictionary().
* 
* Note: If using a database to search in use the Dictionary->database option.
* 
* Options:
* 	- string Dictionary->database Set this to a url to post(Dictionary->getKey) a search string to. The page should return a json string containg a list of key value pairs. 
* 	- int Dictionary->maxKeys This is the maximum number of keys that are allowed to be inputed by the user.
*  - int Dictionary->minKeys This is the minimum number keys that are allowed.
*  - int Dictionary->placeholder The placeholder before keys are inputed.
*  - int Dictionary->extraPlaceholder The placeholder after keys are inputed.
*  
*/
class Dictionary extends Input {
	public $globalName = "Dictionary";
	
	public $source = false;
	public $subSource = "";
	
	public $database = false;
	public $getKey = "search";
	
	public $maxKeys = 99999;
	public $minKeys = 0;
	
	/**
	* If keys that are not in the source should be allowed.
	* Warning: This is not checked on the server side validation. Make sure to do validation yourself.
	*/
	public $allowUserKeys = false;
	
	/**
	* The input label
	*/
	public $label = "Dictionary_input";
	
	public $placeholder = "";
	public $extraPlaceholder = "";
	
	/**
	* @param string $source The javascript object in relation to window. See Theme::input_dictionary() for more info.
	* @param string $subSource If set, this will search in the window[$source][$subSource] object for keys.
	*/
	public function __construct($source, $subSource = "") {
		$this->source = $source;
		$this->subSource = $subSource;
	}
	
	public function get() {
		$params = [
			"source" => $this->source,
			"placeholder" => $this->placeholder,
			"extraPlaceholder" => $this->extraPlaceholder,
			"database" => $this->database,
			"getKey" => $this->getKey
		];
		
		if ($this->subSource != "")
			$params["subSource"] = $this->subSource;
		
		$e = Theme::input_dictionary($params, $this->label);
		
		$e->attr("data-source", $this->source);
		$e->attr("data-sub-source", $this->subSource);
		
		if ($this->database !== false)
			$this->allowUserKeys = true;
		
		$e->attr("data-custom-keys", $this->allowUserKeys ? "1" : "0");
		$e->attr("data-max-keys", $this->maxKeys);
		$e->attr("data-min-keys", $this->minKeys);
		
		$e->attr("isinput", "");
		$e->attr("id", $this->id);
		
		$this->doVisible($e);
		
		return $e->get();
	}
	
	public function validate_client() {
		return '
		var elem = $(element);
		var val = window.Websom.Theme.get($(element));
		if (val.length < parseInt(elem.attr("data-min-keys")))
			return "Needs at least "+elem.attr("data-min-keys")+".";
		
		if (val.length > parseInt(elem.attr("data-max-keys")))
			return "Maximum of "+elem.attr("data-max-keys")+" allowed.";
		
		if (elem.attr("data-custom-keys") == "0") {
			var vals = window[elem.attr("data-source")];
			
			if (elem.attr("data-sub-source") != "")
				vals = vals[elem.attr("data-sub-source")];
			
			for (var i = 0; i < val.length; i++) {
				if (!(val[i] in vals))
					return val[i]+" "+elem.attr("data-extra-placeholder")+" is not allowed.";
			}
		}
		
		return true;
		';
	}
	
	public function validate_server($data) {
		if (!is_array($data))
			return "Invalid input.";
		
		if (count($data) > $this->maxKeys)
			return "Maximum of ".$this->maxKeys.".";
		
		if (count($data) < $this->minKeys)
			return "Minimum of ".$this->minKeys.".";
		
		return true;
	}
	
	public function error() {
		return "return $('<div>'+error+'</div>').insertAfter(element);";
	}
	
	public function receive($data) {
		return $data;
	}
	
	public function load() {
		return 'window.Websom.Theme.set($(element), data);';
	}
	
	public function send() {
		return 'return window.Websom.Theme.get($(element));';
	}
	
	
}

?>