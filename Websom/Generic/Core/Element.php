<?php
/**
* \defgroup Element Element
*
* This class is used to build html structures.
*/



/**
* \ingroup Element
*/
class Element {
	public $identity = [];
	
	public $extent = [];
	
	/**
	* To construct an element you need to provide an element type, and an optional element attribute list.
	*/
	public function __construct($p1, $p2 = false, $p3 = false) {
		if ($p3 !== false) {
			$this->identity = self::structure($p1, $p3);
			$this->identity['contents'] = [$p2];
		}else{
			$this->identity = self::structure($p1, $p2);
		}
		return $this;
	}
	
	/**
	* Use this to insert an `element` instance into the this `element` contents.
	*/
	public function append($element) {
		array_push($this->identity['contents'], $element);
	}
	
	/**
	* This will append a reference to $this onto the $element.
	*/
	public function appendTo($element) {
		$element->appendReference($this);
	}
	
	/**
	* Appends the $element reference.
	*/
	public function appendReference(&$element) {
		array_push($this->identity['contents'], $element);
	}

	/**
	* Use this to get the type of element `this` is.
	*/
	public function type() {
		return $this->identity['type'];
	}
	
	/**
	* Use this to insert a string into the element.
	*/
	public function insert($str) {
		array_push($this->identity['contents'], $str);
	}
	
	/**
	* Use this to set an attribute on the element to a `value`.
	*/
	public function attr($name, $value = null) {
		if ($value === null) {
			if (!isset($this->identity['attr'][$name])) return null;
			return $this->identity['attr'][$name];
		}
		else
			$this->identity['attr'][$name] = $value;
	}
	
	public function hasAttr($name) {
		return isset($this->identity['attr'][$name]);
	}
	
	/**
	* Deletes the `name` attribute
	*/
	public function removeAttr($name) {
		if (isset($this->identity['attr'][$name]))
			unset($this->identity['attr'][$name]);
	}
	
	public function extend($name, callable $call) {
		$this->extent[$name] = $call;
	}
	
	public function call($name, $args = []) {
		if (!is_array($args)) $args = [$args];
		if (isset($this->extent[$name]))
			return call_user_func_array($this->extent[$name]->bindTo($this), $args);
		return false;
	}
	
	public function children(callable $callback) {
		foreach ($this->identity['contents'] as $e) {
			if (call_user_func($callback->bindTo($e)) === true) {
				return true;
			}
		}
	}
	
	public function html() {
		return self::stringify($this, false);
	}
	
	//Quickly made. :(
	public function &child($selector) {
		if (strpos($selector, " ") !== false) {
			$sels = explode(" ", $selector);
			$prevChild = $this;
			$depth = 1;
			
			foreach($sels as $s) {
				$prevChild = $prevChild->child($s);
				if ($depth == count($sels) OR $prevChild === false) {
					return $prevChild;
				}
				$depth++;
			}
		}
		
		if ($selector[0] == "#") {
			foreach ($this->identity['contents'] as $e) {
				if (gettype($e) === "string") continue;
				if (($e->attr('id')) !== null)
					if ($e->attr('id') == substr($selector, 1))
						return $e;
			}
		}else if($selector[0] == "."){
			$array = [];
			foreach ($this->identity['contents'] as $e) {
				if (gettype($e) === "string") continue;
				if (($e->attr('class')) !== null)
					if (strpos($e->attr('class'), substr($selector, 1)) !== false)
						array_push($array, $e);
			}
			if (count($array) > 0)
				return $array;
		}else{
			foreach ($this->identity['contents'] as $e) {
				if (gettype($e) === "string") continue;
				if ($e->identity['type'] == $selector)
					return $e;
			}
		}
		return false;
	}
	
	public function addClass($class) {
		$classes = $this->attr('class');
		
		if (!isset($classes)) {
			$this->attr('class', $class);
		}else{
			$this->attr('class', $classes.' '.$class);
		}
	}
	
	public function removeClass($class) {
		$classes = $this->attr('class');
		if (isset($classes)) {
			$this->attr('class', str_replace($class, '', $classes));
		}
	}
	
	/**
	* Get the html string.
	*/
	public function get() {
		return self::stringify($this);
	}
	
	
	
	static function uniqueId() {
		$id = md5(uniqid(self::$ids, true));
		self::$ids++;
		return $id;
	}
	
	//Private
	
	static private $ids = 9999;
	
	static private function structure($p1, $p2) {
		$rtn = [
			'type' => false,
			'attr' => [],
			'contents' => []
		];
		
		$rtn['type'] = $p1;
		if ($p2 !== false) {
			$rtn['attr'] = $p2;
		}
		
		return $rtn;
	}
	
	static private function stringify($elem, $includeThis = true) {
		$str = '<'.$elem->identity['type'].' '.self::attributer($elem->identity['attr']).'>';
		
		if (!$includeThis) $str = '';
		
		foreach ($elem->identity['contents'] as $subElem) {
			$ty = gettype($subElem);
			if ($ty == 'string' OR $ty == 'integer') {
				$str .= $subElem;
				continue;
			}
			
			if (get_class($subElem) === 'Element') {
				$str .= self::stringify($subElem);
			}
		}
		
		if (!$includeThis)
			return $str;
		
		return $str.'</'.$elem->identity['type'].'>';
	}

	static private function attributer($attrs) {
		$str = [];
		foreach ($attrs as $n => $v) {
			array_push($str, $n.'="'.str_replace('"', "&quot;", $v).'"');
		}
		
		return implode(' ', $str);
	}
	
}



?>