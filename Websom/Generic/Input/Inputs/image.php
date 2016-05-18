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
?>