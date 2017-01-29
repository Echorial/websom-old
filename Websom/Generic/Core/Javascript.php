<?php

/**
* \defgroup Javascript Javascript
* 
* This is the small javascript part of websom.
* 
* Websom javascript events:
* Use onEvent(string eventName, function callback) to listen for events.
* Use CallEventHook(string eventName, array params) to invoke events.
* 
* Official global events:
*  - themeReload(element parent): Called when a part(parent) of the page needs to have the theme loaded.
*/

/**
* \ingroup Javascript
* 
* The Javascript class contains tools for creating javascript globals and quick dirty javascript event hooks.
* 
* To create a javascript global:
* \code
* Javascript::Set("MyGlobal", '"Some string"'); //Sets window["MyGlobal"] = "Some string"
* Javascript::Set("MyGlobalNumber", '2017'); //Sets window["MyGlobalNumber"] = 2017
* Javascript::Set("MyGlobalBool", 'false'); //Sets window["MyGlobalBool"] = false
* Javascript::Set("MyGlobalObject", '{ foo: function () {alert("go");} }'); //Sets window["MyGlobalObject"] = { foo: function () {alert("go");} }
* \endcode
* 
* To run javascript when a websom javascript event is fired:
* \code
* Javascript::On("themeReload", "alert('Theme was reloaded!'); console.log('Hello world');"); //Will run the code when the websom javascript event "themeReload" is invoked
* \endcode
*/
class Javascript {
	static private $globalPageValues = [];
	static private $globalPageEventHooks = [];
	
	/**
	* This will set the $key to the $value globally in the client's window.
	*/
	static public function Set($key, $value) {
		Javascript::$globalPageValues[$key] = $value;
	}
	
	/**
	* This will run the $script code when the websom javascript event $event is invoked.
	* 
	* \note The script is wrapped within the onEvent function, this means some params can be passed into the callback. The first param is `magic`.
	*/
	static public function On($event, $script) {
		array_push(Javascript::$globalPageEventHooks[$event], $script);
	}
	
	/**
	* This is called by Websom after end to get any javascript that needs to be added to the page.
	*/
	static public function get() {
		$script = '<script>';
		
		foreach (Javascript::$globalPageValues as $key => $value)
			$script .= 'window["'.$key.'"] = '.$value.';';
			
		foreach (Javascript::$globalPageEventHooks as $event => $scripts) {
			$script .= 'onEvent("'.$event.'", function (magic) {';
			foreach ($scripts as $s) {
				$script .= '
				'.$s.'
				
				';
			}
			$script .= '});';
		}
		return $script.'</script>';
	}
}

/**
* \ingroup Javascript
* 
* This is a helper for the client.
* 
* \note Make sure to add the ws_not_safe class to any parent element of user content or unsafe html that could contain classes or attributes.
* 
* \warning The clickElement and submitForm use classes and attributes to let websom know how to handle clicks on these elements. If any user content can contain classes or elements make sure to wrap that in an element with the ws_not_safe class.
*/
class ClientTools {
	/**
	* This will make the $element when clicked click the $elementToClick selector.
	*/
	static public function clickElement(&$element, $elementToClick) {
		$element->addClass("ws_click_element");
		$element->attr("data-ws-element", $elementToClick);
	}
	
	/**
	* This will make the $element when clicked submit the $formName.
	*/
	static public function submitForm(&$element, $formName) {
		$element->addClass("ws_submit_form");
		$element->attr("data-ws-form", $formName);
	}
}

?>