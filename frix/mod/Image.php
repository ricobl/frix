<?
class ImageException extends Exception {};

///////////////////
/* Image Engines */
///////////////////

class ImageGif extends Image {
	
	var $type = 'gif';
	
	function load_file ($path) {
		return imagecreatefromgif($path);
	}
	function send_stream () {
		return imagegif($this->image);
	}
	function save_file ($path) {
		return imagegif($this->image, $path);
	}
	
}
class ImageJpeg extends Image {
	
	var $type = 'jpeg';
	
	function load_file ($path) {
		return imagecreatefromjpeg($path);
	}
	function send_stream () {
		return imagejpeg($this->image, null, $this->quality);
	}
	function save_file ($path) {
		return imagejpeg($this->image, $path, $this->quality);
	}
	
}
class ImagePng extends Image {
	
	var $type = 'png';
	
	function load_file ($path) {
		return imagecreatefrompng($path);
	}
	function send_stream () {
		return imagepng($this->image);
	}
	function save_file ($path) {
		return imagepng($this->image, $path);
	}
	
}

////////////////
/* Base Image */
////////////////

abstract class Image {
	
	public $path;
	public $type; // GIF, JPEG, PNG, BMP?
	
	// Image resource
	public $image;
	
	static $engines = array(
		'gif' => 'ImageGif',
		'jpg' => 'ImageJpeg',
		'jpeg' => 'ImageJpeg',
		'png' => 'ImagePng',
	);
	
	// Dimensions
	private $width, $height;
	public $orig_width, $orig_height;
	public $proportional = true;
	
	// Image quality, applies only to JPEG files
	// Adding here for compatibility with other image engines
	var $quality = 75;
	
	//////////////
	/* Internal */
	//////////////
	
	// public function __construct () {
	// }
	
	// MAGIC FUNCTIONS: member overload
	public function __get ($name) {
		$method = 'get_' . $name;
		if (method_exists($this, $method)) {
			return $this->$method();
		}
		return $this->$name;
	}
	public function __set ($name, $value) {
		$method = 'set_' . $name;
		if (method_exists($this, $method)) {
			return $this->$method($value);
		}
		return $this->$name;
	}
	
	////////////
	/* Static */
	////////////
	
	// Opens an image file
	// Returns an image engine instance
	static function open ($path) {
		
		if (!file_exists($path)) {
			throw new ImageException(sprintf('Image file "%s" not found', $path));
		}
		
		// TODO: auto-detect image type using getgetimagesize
		
		$path_info = pathinfo($path);
		
		// Get the appropriate engine
		$engine = self::$engines[ strtolower($path_info['extension']) ];
		
		if ( !$engine || (!class_exists($engine)) ) {
			throw new ImageException(sprintf('Unsupported image type for file "%s"', $path));
		}
		
		// Create an image instance
		$image = new $engine;
		
		// Tries to load the image file
		$image->load($path);
		
		return $image;
		
	}
	
	private function set_width ($width) {
		if ($this->proportional) {
			$scale = $width / $this->width;
			$this->height = round($this->height * $scale);
		}
		return $this->width = $width;
	}
	private function set_height ($height) {
		if ($this->proportional) {
			$scale = $height / $this->height;
			$this->width = round($this->width * $scale);
		}
		return $this->height = $height;
	}
	private function get_width () {
		return $this->width;
	}
	private function get_height () {
		return $this->height;
	}
	
	private function update () {
		
		// Checks if dimensions changed
		if (!$this->changed()) {
			return;
		}
		
		list($nw, $nh, $w, $h) = array($this->width, $this->height, $this->orig_width, $this->orig_height);
		
		// TODO: support transparent GIFs and PNGs
			// check: mediumexposure.com/techblog/smart-image-resizing-while-preserving-transparency-php-and-gd-library
		$new_img = imagecreatetruecolor($nw, $nh);
		imagecopyresampled($new_img, $this->image, 0, 0, 0, 0, $nw, $nh, $w, $h);
		
		$this->image = $new_img;
		
		// Update original width and height
		$this->orig_width = $this->width;
		$this->orig_height = $this->height;
		
		return true;
		
	}
	
	////////////
	/* Public */
	////////////
	
	public function load ($path) {
		
		// Save file path
		$this->path = $path;
		
		// Tries to load the image file
		$this->image = $this->load_file($this->path);
		
		// Checks if the image was loaded
		if (!$this->image) {
			throw new ImageException(sprintf('Invalid "%s" image file "%s".', $type, $path));
		}
		
		// Load dimensions
		$this->width = imagesx($this->image);
		$this->height = imagesy($this->image);
		$this->orig_width = $this->width;
		$this->orig_height = $this->height;
		
		return $this->image;
		
	}
	
	// Checks if dimensions changed from the original file
	public function changed () {
		return ( ($this->width != $this->orig_width) || ($this->height != $this->orig_height) );
	}
	
	// Crops the image canvas
	public function crop ($top, $right, $bottom, $left) {
		// TODO: use only lazy operations
			// Maybe keeping a canvas array, to know where to crop
		return;
	}
	
	public function fit ($width = null, $height = null) {
		// TODO: use only lazy operations
			// Image modifications will happen only on save or stream
		
		if ($width) {
			$this->set_width($width);
		}
		if ($height) {
			$this->set_height($height);
		}
		
	}
	
	public function save () {
		// Update lazy operations
		$this->update();
		// Save file to original path
		return $this->save_file($this->path);
	}
	
	public function save_as ($path) {
		// Update lazy operations
		$this->update();
		
		// TODO: detect file extension changes and save with different engines
		
		// Save file
		return $this->save_file($path);
	}
	
	public function stream () {
		// Update lazy operations
		$this->update();
		// Send mime-type header
		header('Content-type: image/' . $this->type);
		// Send the image stream
		return $this->send_stream(null);
	}
	
	//////////////
	/* Abstract */
	//////////////
	
	// Load an image file
	abstract function load_file ($path);
	// Output an image to the browser
	abstract function send_stream ();
	// Write an image file to disk
	abstract function save_file ($path);
	
}
?>