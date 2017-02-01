<?php
function Input_image_Status(){
	return true;
}

function Input_image_Global_Javascript() {
	return '
		function imageChange(event, elem) {
			var output = $(elem).parent("div").find("img");
			$(output).attr("src", URL.createObjectURL(event.target.files[0]));
		}
	';
}

function Input_image_Sanitize_Client(){
	//write the following in javascript. 
	//The element variable is _e
	//The check variable is _c it can be "Submit" or "Input" (if the value of the element changed or was submited via form button press)
	//If checking takes loading then return a callback\\
	//If there is an error. make sure to return the error string
return "
	if ($(_e).hasAttr('maxsize')) {
	var maxsize = $(_e).attr('maxsize');
	if (_e.files[0].size > maxsize)
		return 'Image size is too large, '+Math.round(_e.files[0].size/1024)+'kb of max '+Math.round(maxsize/1024)+'kb';
	}

	return true;
	/*
	function (_e, imgwidth, imgheight) {
		if ($(_e).hasAttr('maxwidth')) {
		var maxwidth = $(_e).attr('maxwidth');
		if (imgwidth > maxwidth)
			return 'Image width is too large, '+imgwidth+'px of max '+maxwidth+'px.';
		}
		if ($(_e).hasAttr('maxheight')) {
		var maxheight = $(_e).attr('maxheight');
		if (imgheight > maxheight)
			return 'Image height is too small, '+imgheight+'px of min '+maxheight+'px.';
		}
		if ($(_e).hasAttr('minwidth')) {
		var minwidth = $(_e).attr('minwidth');
		if (imgwidth < minwidth)
			return 'Image width is too small, '+imgwidth+'px of min '+minwidth+'px.';
		}
		if ($(_e).hasAttr('minheight')) {
		var minheight = $(_e).attr('minheight');
		if (imgheight < minheight)
			return 'Image height is too small, '+imgheight+'px of min '+minheight+'px.';
		}
		
		
	};
	*/
";
}

function Input_image_Sanitize_Server($options = array('maxsize' => 1024), $value){
	//If there is an error return an error string, or return true
	
	$imginfo = getimagesize($value["tmp_name"]);
	$iw = $imginfo[0];
	$ih = $imginfo[1];
	
	if (isset($options['maxwidth'])) {
		if ($iw > $options['maxwidth'])
			return 'Image width is too large, '.$iw.'px of max '.$options['maxwidth'].'px.';
	}
	if (isset($options['minwidth'])) {
		if ($iw < $options['minwidth'])
			return 'Image width is too small, '.$iw.'px of min '.$options['minwidth'].'px.';
	}
	if (isset($options['maxheight'])) {
		if ($ih > $options['maxheight'])
			return 'Image height is too large, '.$ih.'px of max '.$options['maxheight'].'px.';
	}
	if (isset($options['minheight'])) {
		if ($iw < $options['minheight'])
			return 'Image height is too small, '.$ih.'px of min '.$options['minheight'].'px.';
	}
	
	if ($value["size"] > $options['maxsize'])
		return 'The image is too large '.round($value["size"]/1024) .'kb of maximum '.round($options['maxsize']/1024) .'kb.';
	
	$extension = pathinfo($value['name'], PATHINFO_EXTENSION);
	$validType = 'Invalid image type.';
	foreach ($options['types'] as $type)
		if ($type == $extension)
			$validType = true;
	
	
	return $validType;
}

function Input_image_Html_Get($options, $args, $value = ''){
	//Always make value optional. The value is for, if the input is already set.
	$attr = '';
	$keys = array('maxwidth', 'maxheight', 'minwidth', 'minheight', 'maxsize');
	foreach ($keys as $key)
		if (isset($options[$key]))
			$attr .= $key.'='.'"'.$options[$key].'" ';
	$after = '';
	if (isset($options['preview'])) $after = '<img previewer=""';
	if (isset($options['previewurl'])) $after .= ' src="'.$options['previewurl'].'"';
	if (isset($options['previewclass'])) $after .= ' class="'.$options['previewclass'].'"';
	$after .= '>';
	return '<div><input type="file" '.$args.' accept="image/*" '.$attr.' " onchange="imageChange(event, this)">'.$after.'</div>';
}

function Input_image_Override_Value($name, $options) {
	return $_FILES[$name];
}



/**
* \ingroup BuiltInInputs
* 
* The `Image` input is a nice easy to use validated image uploader.
* 
* The value of the image is in a base64 string use `$image = imagecreatefromstring(base64_decode($imageString))` to create a image from it.
* 
* \note this will add 2 bytes to the max file size to account for base64 =, == ending.
* 
* Options:
*	- Image->max_width: The max width in pixels
*	- Image->max_height: The max height in pixels
*	- Image->min_width: The min width in pixels
*	- Image->min_height: The min height in pixels
*	- Image->max_size: The max size in bytes
*
*/
class Image extends Input {
	public $globalName = 'Image';
	public $label = "input_image";
	
	public $max_width = 4000;
	public $min_width = 10;
	public $max_height = 3000;
	public $min_height = 10;
	
	/**
	* Warning this is not checked on the server
	*/
	public $acceptedExtensions = [".jpg", ".jpeg", ".png", ".gif"];
	
	/**
	* Default is 1mb
	*/
	public $max_size = 1000000;
	
	function buildElement() {
		$e = Theme::input_file($this->label, ["types" => $this->acceptedExtensions]);
		
		$e->attr("id", $this->id);
		$e->attr("data-max-width", $this->max_width);
		$e->attr("data-min-width", $this->min_width);
		$e->attr("data-min-height", $this->min_height);
		$e->attr("data-max-height", $this->max_height);
		$e->attr("data-max-size", $this->max_size);
		$e->attr("isinput", "");
		
		$this->doVisible($e);
		
		return $e;
	}
	
	function get() {
		return $this->buildElement()->get();
	}
	
	function send() {
		return 'var files = window.Websom.Theme.get($(element));
		var newFiles = [];
		for (var i = 0; i < files.length; i++)
			newFiles.push(files[i][0]);
		return newFiles;';
	}
	
	function validate_client() {
		return 'var files = window.Websom.Theme.get($(element));
		
		for (var i in files) {
			var ind = parseInt(i+1);
			var img = files[i][0];
			var size = 4*Math.ceil(img.length/3);
			var maxSize = parseInt($(element).attr("data-max-size"));
			if (size > maxSize) {
				return "Image "+ind+" is too large in size("+Math.round(size/1024)+"kb). The maximum size is "+Math.round(maxSize/1024)+"kb.";
			}
			
			var image = files[i][1];
			
			var w = image.width;
			var maxWidth = parseInt($(element).attr("data-max-width"));
			var minWidth = parseInt($(element).attr("data-min-width"));
			if (w > maxWidth) {
				return "Too large, image "+ind+" must be under "+maxWidth+"pixels in width.";
			}
			if (w < minWidth) {
				return "Too small, image "+ind+" must be over "+minWidth+"pixels in width.";
			}
			var h = image.height;
			var maxHeight = parseInt($(element).attr("data-max-height"));
			var minHeight= parseInt($(element).attr("data-min-height"));
			if (h > maxHeight) {
				return "Too large, image "+ind+" must be under "+maxHeight+"pixels in height.";
			}
			if (h < minHeight) {
				return "Too small, image "+ind+" must be over "+minHeight+"pixels in height.";
			}
		}
		
		return true;';
	}
	
	function validate_server($data) {
		foreach ($data as $i => $img) {
			$ind = $i+1;
			$size = 4*ceil((strlen($img)/3));
			
			if ($size > $this->max_size) {
				return "Image ".$ind." is too large in size(".($size/1024)."kb). The maximum size is ".($this->max_size/1024)."kb.";
			}
			
			$image = imagecreatefromstring(base64_decode($img));
			
			if ($image === false)
				return "Image ".$ind." is not formated correctly.";
			
			$w = imagesx($image);
			
			if ($w > $this->max_width) {
				return "Too large, image ".$ind." must be under ".$this->max_width."pixels in width.";
			}
			if ($w < $this->min_width) {
				return "Too small, image ".$ind." must be over ".$this->min_width."pixels in width.";
			}
			$h = imagesy($image);
			
			if ($h > $this->max_height) {
				return "Too large, image ".$ind." must be under ".$this->max_height."pixels in height.";
			}
			if ($h < $this->min_height) {
				return "Too small, image ".$ind." must be over ".$this->min_height."pixels in height.";
			}
			imagedestroy($image);
		}
		return true;
	}
	
	function error() {
		return "return $('<div>'+error+'</div>').insertAfter(element);";
	}
	
	function receive($data) {
		return $data;
	}
	
	function load() {
		return 'window.Websom.Theme.set($(element), data);';
	}
	
}

?>