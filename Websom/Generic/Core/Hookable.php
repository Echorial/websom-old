<?php
/**
* \ingroup Event
*
* If an object or class is "hookable" then code can listen for events that this class or object invokes. Events can be hooked for both single object instances or globally on all instances.
* This is the class that should be extended if you wish to implement events and hooks.
*
* Example:
* \code
* class fish extends Hookable {
* 	public function catch() {
* 		$this->event("caught", ["Bass"]); //Invoke the "caught" event and pass "Bass" as a param.
* 	}
* }
* 
* $fish = new Fish();
* $fish->on("caught", function ($name) { //Listen for "caught" on just this instance.
* 	echo "A ".$name." was caught.";
* });
* 
* fish::onGlobal("caught", function ($name) { //Listen for "caught" on all instances.
* 	echo "A ".$name." was caught.";
* }, true);
* 
* 
* \endcode
*
*/
class Hookable {
	private $_hookable_hooks = [];
	
	static private $_hookable_global_hooks = [];
	
	/**
	* Use this to hook into a single object's event.
	* 
	* @param string $event The event name to listen for.
	* @param callable $callback A function to call when the event is invoked. This function should return true if it wishes the event to be canceled or false if not.
	* 
	* @return void
	*/
	public function on($event, callable $callback) {
		if (!isset($this->_hookable_hooks[$event])) $this->_hookable_hooks[$event] = [];
		array_push($this->_hookable_hooks[$event], $callback);
	}
	
	/**
	* Use this to invoke an event on this instance and global listeners.
	* 
	* @param string $event The event name.
	* @param array $args A list of params to pass into the listening function(s).
	* @param bool $multiple If this is false the event will be fired on the latest event listener and return the value from that hook.
	* 
	* @return 1. True if the event should be canceled or false if not. 2. Mixed value for single hook.
	*/
	public function event($event, $args, $multiple = true) {
		$canceled = false;
		if (!$multiple) {
			if (isset($this->_hookable_hooks[$event])) {
				return call_user_func_array($this->_hookable_hooks[$event][count($this->_hookable_hooks[$event])-1], $args);
			}else{
				return null;
			}
		}
		
		if (isset(self::$_hookable_global_hooks[$event])) {
			foreach (self::$_hookable_global_hooks[$event] as $hook) {
				if ($hook[1]) {
					$val = call_user_func_array($hook[0], $args);
					if ($val === true)
						$canceled = true;
				}
			}
		}
		
		if (isset($this->_hookable_hooks[$event])) {
			foreach ($this->_hookable_hooks[$event] as $hook) {	
				$val = call_user_func_array($hook, $args);
				if ($val === true)
					$canceled = true;
			}
		}
		
		return $canceled;
	}
	
	//Static hooks
	
	/**
	* This will listen for an event on all instances.
	* 
	* @param string $event The event name to listen for.
	* @param callable $callback A function that will be called when any instance or static class gets its event invoked. This should also return true if the event should be canceled or false if not.
	* @param bool $allObjectInstances If this should listen on object instances.
	* 
	* @return void
	*/
	static public function onGlobal($event, callable $callback, $allObjectInstances = false) {
		if (!isset(self::$_hookable_global_hooks[$event])) self::$_hookable_global_hooks[$event] = [];
		array_push(self::$_hookable_global_hooks[$event], [$callback, $allObjectInstances]);
	}
	
	/**
	* This will call all static hooks for an event(not instance hooks).
	* 
	* @param string $event The event name to invoke.
	* @param array $args A list of params to pass into the listening function(s).
	* 
	* @return 1. True if the event should be canceled or false if not. 2. Mixed value for single hook.
	*/
	static public function globalEvent($event, $args) {
		$canceled = false;
		if (isset(self::$_hookable_global_hooks[$event])) {
			foreach (self::$_hookable_global_hooks[$event] as $hook) {
				
				if (!$hook[1]) {
					$val = call_user_func_array($hook[0], $args);
					if ($val === true) {
						$canceled = true;
					}elseif ($val !== false) {
						return $val;
					}
				}
			}
		}
		
		return $canceled;
	}
}


/**
* \ingroup Event
* 
* This class is used to invoke a hidden function when calling a function. Bad explanation :(
* 
* Example:
* \code
* 
* function callMeBack($lateCall = null) {
*  	//DoStuff
* 		LateCall::inject($lateCall, (function () { die("Dead"); }); //If no lateCall object is passed this will be invoked immediately.
* 		return "SomeValue";
* }
* 
* $lc = new LateCall(); //Create a new lateCall object for the call bellow.
* 
* $someReturn = callMeBack($lc); //This passes our lateCall reference into the function.
* Wait(5);
* $lc->invoke(); //This will call the die function after 5 seconds rather than right away if we did not pass anything.
* 
* callMeBack(); //This invokes the die function right away.
* 
* \endcode
*/
class LateCall {
	/**
	* @param bool/array $shouldCallRightAway If left at false this will wait until the invoke method is called, if set to array this will call the inserted function right away and pass the array into it.
	*/
	public function __construct($shouldCallRightAway = false) {
		$this->waiting = $shouldCallRightAway;
	}
	
	public $lateCall = false;
	
	public $waiting = false;
	
	/**
	* Use this to insert into a lateCall or just call the function if lateCall is null
	* 
	* @param LateCall/null $lateCall The LateCall or null to check on.
	* @param callable $call The callback to invoke.
	* @param array $params The array of params to pass into the $call if the $lateCall is null
	*/
	static public function inject($lateCall, callable $call, $params = []) {
		if ($lateCall === null) {
			call_user_func_array($call, $params);
		}else{
			$lateCall->insert($call);
		}
	}
	
	/**
	* Insert a callable into the lateCall object.
	*/
	public function insert(callable $call) {
		$this->lateCall = $call;
		if ($this->waiting !== false)
			$this->callIt($this->waiting);
	}
	
	/// \cond
	private function callIt($params) {
		if ($this->lateCall === false) {
			$this->waiting = true;
			return;
		}
		call_user_func_array($this->lateCall, $params);
	}
	/// \endcond
	
	/**
	* Invoke the lateCall.
	* 
	* @param array $params The params to pass into the callable.
	*/
	public function invoke($params = []) {
		$this->callIt($params);
	}
}






?>