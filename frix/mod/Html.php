<?
// Html helpers / shortcuts
class Html {
	
	static function prefix_media ($src) {
		$src = url(Frix::config('MEDIA_URL'), $src);
	}
	
	static function flash ($src, $width, $height, $get = '', $params = array()) {
		
		$src = url(Frix::config('MEDIA_URL'), $src);
		
		$get = ($get ? '?' : '') . $get;
		
		$context = array(
			'src' => $src . $get,
			'src_no_ext' => dirname($src) . '/' . basename($src, '.swf') . $get,
			'width' => $width,
			'height' => $height,
		);
		
		// Load template and render
		$t = new Template('frix/html/flash');
		return $t->render($context);
		
	}
	
}
?>