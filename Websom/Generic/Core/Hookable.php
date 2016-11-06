<?php

/**
* \defgroup Hookable Hookable
*
* If an object or class is "hookable" then code can listen for events that this class or object invokes. Events can be hooked for both single object instances or globally on all instances.
*/

/**
* \ingroup Hookable
*
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
	* @param callable $callback A function to call when the event is invoked. This function should return true if it wishes the event to be cancled or false if not.
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
	* 
	* @return 1. True if the event should be cancled or false if not. 2. Mixed value for single hook.
	*/
	public function event($event, $args) {
		$cancled = false;
		
		
		if (isset(self::_hookable_global_hooks[$event])) {
			foreach (self::_hookable_global_hooks[$event] as $hook) {
				if ($hook[1]) {
					$val = call_user_func_array($hook[0], $args);
					if ($val === true) {
						$cancled = true;
					}elseif ($val !== false) {
						return $val;
					}
				}
			}
		}
		
		if (isset($this->_hookable_hooks[$event])) {
			foreach ($this->_hookable_hooks[$event] as $hook) {			
				$val = call_user_func_array($hook, $args);
				if ($val === true) {
					$cancled = true;
				}elseif ($val !== false) {
					return $val;
				}
			}
		}
		
		return $cancled;
	}
	
	//Static hooks
	
	/**
	* This will listen for an event on all instances.
	* 
	* @param string $event The event name to listen for.
	* @param callable $callback A function that will be called when any instance or static class gets its event invoked. This should also return true if the event should be cancled or false if not.
	* @param bool $allObjectInstances If this should listen on object instances.
	* 
	* @return void
	*/
	static public function onGlobal($event, callable $callback, $allObjectInstances = false) {
		if (!isset(self::_hookable_global_hooks[$event])) self::_hookable_global_hooks[$event] = [];
		array_push(self::_hookable_global_hooks[$event], [$callback, $allObjectInstances]);
	}
	
	/**
	* This will call all static hooks for an event(not instance hooks).
	* 
	* @param string $event The event name to invoke.
	* @param array $args A list of params to pass into the listening function(s).
	* 
	* @return 1. True if the event should be cancled or false if not. 2. Mixed value for single hook.
	*/
	static public function globalEvent($event, $args) {
		$cancled = false;
		
		if (isset(self::_hookable_global_hooks[$event])) {
			foreach (self::_hookable_global_hooks[$event] as $hook) {
				if (!$hook[1]) {
					$val = call_user_func_array($hook[0], $args);
					if ($val === true) {
						$cancled = true;
					}elseif ($val !== false) {
						return $val;
					}
				}
			}
		}
		
		return $cancled;
	}
	
}

?>