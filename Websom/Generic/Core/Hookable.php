<?php

class Hookable {
	private $_hookable_hooks = [];
	
	public function on($event, callable $callback) {
		$this->_hookable_hooks[$event] = $callback;
	}
	
	public function event($event, $args) {
		if (!isset($this->_hookable_hooks[$event])) return false;
		return call_user_func_array($this->_hookable_hooks[$event], $args);
	}
}

?>