<?php
/**
* \ingroup BuiltInInputs
* 
* The `Webcam` input is a nice easy to use validated image uploader that uses the client webcam.
* 
* The value of the image is in a base64 string use `$image = imagecreatefromstring(base64_decode($imageString))` to create a image from it.
* 
* \note this will add 2 bytes to the max file size to account for base64 =, == ending.
* 
* \note The output of this input is an array of base64 strings.
* 
* Options:
*	- Image->max_width: The max width in pixels
*	- Image->max_height: The max height in pixels
*	- Image->min_width: The min width in pixels
*	- Image->min_height: The min height in pixels
*	- Image->max_size: The max size in bytes
*
*/
class Webcam extends Input {
	public $globalName = 'Webcam';
	public $label = "input_webcam";
	
	public $max_width = 4000;
	public $min_width = 10;
	public $max_height = 3000;
	public $min_height = 10;
	
	/**
	* How many images are allowed to be uploaded
	*/
	public $maxAmount = 12;
	
	/**
	* Default is 100mb
	*/
	public $max_size = 1000000000;
	
	///\cond
	static private $didJavascript = false;
	///\endcond
	
	function buildElement() {
		$e = new Element("div");
		$e->addClass("webcam-input");
		$e->attr("data-max-amount", $this->maxAmount);
		
		$frame = new Element("div", "<canvas style='display: none'></canvas><video style='max-width: 100%; max-height: 100%;' autoplay></video>", []);
		$frame->addClass("webcam-frame");
		
		$thumb = new Element("div");
		$thumb->addClass("webcam-thumbnails");
		$thumb->attr("style", "position: relative; top: -4em; left: .1em;");
		
		$add = Theme::button(Theme::icon("camera", "")->get(), "", ["round" => true]);
		$add->attr("style", "float: right; top: .9em; left: -.3em;");

		$add->addClass("webcam-add");
		$thumb->append($add);
		$thumb->append(new Element("div", ["class" => "nails", "style" => "position: relative; top: -2.6em; overflow-x: auto; float: left; white-space: nowrap; opacity: 1; width: 100%;"]));
		
		$e->append($frame);
		$e->append($thumb);
		
		$e->attr("id", $this->id);
		$e->attr("data-max-width", $this->max_width);
		$e->attr("data-min-width", $this->min_width);
		$e->attr("data-min-height", $this->min_height);
		$e->attr("data-max-height", $this->max_height);
		$e->attr("data-max-size", $this->max_size);
		$e->attr("isinput", "");
		
		$this->doVisible($e);
		
		if (!self::$didJavascript) {
			self::$didJavascript = true;
			
			Javascript::On("themeReload", '$(magic).find(".webcam-input:not([data-webcam-done])").each(function () {
				var that = $(this);
				that.data("webcam-input", {images: []});
				$(this).attr("data-webcam-done", "true");
				navigator.getUserMedia  =  navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia;
				
				var video = $(this).find(".webcam-frame > video")[0];
				
				var canvas = $(this).find(".webcam-frame > canvas")[0];
				var ctx = canvas.getContext("2d");
				
				var thumbBar = $(this).children(".webcam-thumbnails");
				var thumbnails = thumbBar.children("div.nails");
				
				thumbnails.on("click", "img", function () {
					that.data("webcam-input").images.splice(parseInt($(this).attr("data-image-id")), 1);
					refresh();
				});
				
				thumbnails.on("mouseenter", "img", function () {
					$(this).animate({top: "-.2em", opacity: "1"}, 300);
				});
				
				thumbnails.on("mouseleave", "img", function () {
					$(this).animate({top: "0em", opacity: ".9"}, 300);
				});
				
				var add = thumbBar.find(".webcam-add");
				add.on("click", function () {take()});
				
				if (navigator.getUserMedia) {
					navigator.getUserMedia({audio: true, video: {
						width: {min: 1080, ideal: 1920},
						height: {min: 720, ideal: 1080}
					  }}, function(stream) {
						var audioTracks = stream.getAudioTracks();
						for (var i = 0, l = audioTracks.length; i < l; i++) {
							audioTracks[i].enabled = false;
						}
						
						video.src = window.URL.createObjectURL(stream);
					}, function () {
						alert("Error while getting video input");
					});
				}else{
					
				}
				
				function take() {
					canvas.width = video.videoWidth;
					canvas.height = video.videoHeight;
					ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
					that.data("webcam-input").images.push(canvas.toDataURL("image/png"));
					refresh();
				}
				
				function refresh() {
					thumbnails.animate({opacity: 0}, 300, function () {
						thumbnails.empty();
						var images = that.data("webcam-input").images;
						for (var i in images) {
							thumbnails.append($("<img data-image-id=\""+i+"\" style=\"cursor: pointer; opacity: .9; position: relative; margin: .2em; max-width: 64px; max-height: 64px;\"/>").attr("src", images[i]));
						}
						thumbnails.animate({opacity: 1}, 300);
					});
				}
			});');
		}
		
		return $e;
	}
	
	function get() {
		return $this->buildElement()->get();
	}
	
	function send() {
		return 'return $(element).data("webcam-input").images;';
	}
	
	function validate_client() {
		return '//var files = window.Websom.Theme.get($(element));
		/*
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
		}*/
		
		return true;';
	}
	
	function validate_server($data) {
		foreach ($data as $i => $img) {
			$ind = $i+1;
			$size = 4*ceil((strlen($img)/3));
			
			if ($size > $this->max_size) {
				return "Image ".$ind." is too large in size(".($size/1024)."kb). The maximum size is ".($this->max_size/1024)."kb.";
			}
			
			$base64 = base64_decode($img);
			if ($base64 === false) {
				return "Image ".$ind." is not formated correctly.";
			}
			$image = imagecreatefromstring($base64);
			
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
		foreach ($data as $i => $img)
			$data[$i] = substr($img, 22);
			
		return $data;
	}
	
	function load() {
		return 'window.Websom.Theme.set($(element), data);';
	}
	
}
?>