<?php
/**
* \ingroup TemplateClasses
*
* \brief This is the template class for all 'Views'.
*
* Views are very useful for displaying information from a MySql database.
*
* Read the method descriptions for information on how View works.
*
* To create custom views you would simply extend the View class and override the needed methods.
*/
class View {
	public $Is_View = true;
	public $owner = 'none';
	public $name = 'Untitled_View';
	
	/**
	* Injections
	* This is used to inject into subs.
	*/
	public $inject;
	
	/**
	* @param Injections $customInjections If you wish to override the injections for this single view instance.
	*/
	public function __construct(Injections $customInjections = null) {
		if ($customInjections === null) {
			$this->inject = new Injections();
		}else{
			$this->inject = $customInjections;
		}
		$this->init();
	}
	
	/**
	* This is called after the constructor.
	*/
	public function init() {
		
	}
	
	/**
	* This method is called requesting an html structured string about how the data container will be displayed.
	*
	* The $rows are where the rows of data will be displayed.
	* The $columns are where the sorting controls will be displayed.
	*/
	public function full($rows, $columns) {
		return '';
	}
	
	/**
	* This method is called requesting the html structure for each row.<br>
	* Example return value: <br>
	* \code
	* return "<div> Name: ".$row['name'].", Description: ".$row['desc']." </div>"
	* \endcode
	* The above example will return a div containg the `name` and `desc` data from the MySql database
	*/
	public function sub($row, $injections) {
		return '';
	}
	
	/**
	* This will return a sub with any injected strings.
	* 
	* @param array $row The data to pass into the injections and sub() method.
	* 
	* @return A string containg the built sub.
	*/
	public function buildSub($row) {
		if (!($this->inject instanceof Injections)) {
			$this->inject = new Injections();
		}
		$this->inject->row = $row;
		return $this->sub($row, $this->inject);
	}
}

/* Test

class cView extends View {
	function sub($row, $injections) {
		$e = new Element("div");
		
		$e->append($row["name"]);
		
		$e->prepend($injections->get("top", false, "<br>"));
		$e->append($injections->get("bottom", false, "<br>"));
		
		return $injections->align($e)->get();
	}
}

$vv = new cView();
$vv->inject->inject("left", function ($row) {
	return $row["name"]." <- Hello";
});

$vv->inject->inject("right", function ($row) {
	return $row["name"]." <- Right Left";
});
$vv->inject->inject("top", function ($row) {
	return $row["name"]." <- Top";
});
$vv->inject->inject("bottom", function ($row) {
	return $row["name"]." <- Bot";
});

echo $vv->buildSub([
	"name" => "Nme"
]);
*/

/**
* \ingroup TemplateClasses
* 
* This class is used by View to allow injection into the sub method.
*/
class Injections {
	
	private $injected = [];
	
	/**
	* The row to use if one was not provided in the Injections::get($customRow) argument.
	*/
	public $row = [];
	
	/**
	* This will inject a function into the location that you want.
	* 
	* @param string $where The location to inject this. List of required injection spots:
	* 		- "top": Inside of the main element but above everything.
	* 		- "bottom": Inside of the main element but bellow everything.
	* 		- "left": If set this should split the element content into a grid and put this to the left. Use Injections::align() for a quick easy way to do this. Options: (int)"size"(1-12) The size of the grid column.
	* 		- "right": If set this should split the element content into a grid and put this to the right. Use Injections::align() for a quick easy way to do this. Options: (int)"size"(1-12) The size of the grid column.
	* @param callable $injector The function to call when this injection is available. The function should return a string and accept(function argument) an array of row data.
	* @param array $options A key/value pair array containing the options to pass into the sub.
	*/
	function inject($where, callable $injector, $options = []) {
		if (!isset($this->injected)) {
			$this->injected[$where] = [];
		}
		$this->injected[$where][] = [$injector, $options];
	}
	
	/**
	* This will call the $inject callable when the $location is injected into.
	* 
	* @param string $location The location of the injection to find. See Injections::inject to see the standard locations.
	* @param array $customRow Set this to an array if you wish to override the row that is passed into the injections.
	* @param string $implode If set to a string this will return a single string that is connected with the $implode string.
	* 
	* @return Array of [string(content), options[key/value]] to add to the location.
	*/
	function get($location, $customRow = false, $implode  = false) {
		$r = $this->row;
		if ($customRow !== false)
			$r = $customRow;
		
		foreach ($this->injected as $loc => $inj) {
			if ($loc == $location) {
				$rtn = [];
				foreach ($inj as $i) {
					if ($implode === false) {
						$rtn[] = [call_user_func($i[0], $r), $i[1]];
					}else{
						$rtn[] = call_user_func($i[0], $r);
					}
				}
				if ($implode !== false) {
					return implode($implode, $rtn);
				}else{
					return $rtn;
				}
			}
		}
		if ($implode !== false) {
			return "";
		}else{
			return [];
		}
	}
	
	/**
	* This automatically check and add grids for left and right locations.
	* 
	* @param string/Element $mainContent This is the main content that will be placed in the middle.
	* 
	* @return An element instance containg the $mainContent and left/right grids if any are needed. Note if a grid is not needed the method will return the $mainContent.
	*/
	function align($mainContent, $mainSize = 6, $customRow = false, $leftAppend = [], $rightAppend = []) {
		$left = $this->get("left", $customRow)+$leftAppend;
		$right = $this->get("right", $customRow)+$rightAppend;
		
		$hasGrid = false;
		$grid = [[$mainContent, $mainSize]];
		
		foreach ($left as $le) {

			if (!isset($le[1]["size"]))
				$le[1]["size"] = 1;
			
			array_unshift($grid, [$le[0], $le[1]["size"]]);
		}
		
		foreach ($right as $re) {
			if (!isset($re[1]["size"]))
				$re[1]["size"] = 1;
			
				$grid[] = [$re[0], $re[1]["size"]];
		}
		
		$rtn = $mainContent;
		if (count($grid) > 1) {
			$rtn = Theme::grid([
					$grid
			], "?");
		}
		return $rtn;
	}
	
}

?>