<?
class Fs {
	
	/*
	// TODO: replace 'util.php' FS functions with these...
	
	// Normalize a path
	static function norm_path ($path, $sep = DIRECTORY_SEPARATOR) {
		return str_replace(array('/', '\\'), $sep, $path);
	}

	// Join path parts
	static function join_path ($parts, $sep = DIRECTORY_SEPARATOR) {
		return implode($sep, $parts);
	}
	*/
	
	// Read a directory
	static function dir ($path, $mask = '.+', $replace = '\0') {
		
		$dirs = array();
		$files = array();
		
		// Check if path is a directory
		if (is_dir($path)) {
			
			// Open a directory and read its contents
			if ($handle = opendir($path)) {
				
				// Loop through directory contents
				while (($file = readdir($handle)) !== false) {
					
					// If the file matches the RegEx
					if (preg_match('/' . $mask . '/', $file)) {
						
						// Apply the RegEx replacement
						$replacement = preg_replace('/' . $mask . '/', $replace, $file);
						
						// Check the item type (dir or file) and save
						if (is_dir($path . $file)) {
							$dirs[] = $replacement;
						}
						else {
							$files[] = $replacement;
						}
						
					}
					
				}
				
				// Close directory handle
				closedir($handle);
				
				// Sort arrays (natural sort)
				natsort($dirs);
				natsort($files);
				
			}
			
		}
		
		return array_merge($dirs, $files);
		
	}
	
	static function format_size ($size, $round = 0) {
		//Size must be bytes!
		$sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		
		$total = count($sizes);
		
		for ($i = 0; ($size > 1024) && ($i < $total); ++$i) {
			$size /= 1024;
		}
		
		return round($size, $round) . ' ' . $sizes[$i];
	}
	
	static function file_size ($file) {
		
		if (!is_file($file)) {
			return false;
		}
		
		return filesize($file);
	}
	
	static function extension ($file) {
		$info = pathinfo($file);
		return $info['extension'];
	}
	
	static function basename ($file) {
		$info = pathinfo($file);
		return $info['basename'];
	}
	
	// Read a directory
	static function is_empty_dir ($path) {
		
		$empty = true;
		
		// Check if path is a directory
		if (is_dir($path)) {
			
			// Open a directory and read its contents
			if ($handle = opendir($path)) {
				
				// Loop through directory contents
				while (($file = readdir($handle)) !== false) {
					
					// If the file isn't the parent or current dir
					if (($file != '.') && ($file != '..')) {
						$empty = false;
						break;
					}
					
				}
				
				// Close directory handle
				closedir($handle);
				
			}
			
		}
		
		return $empty;
		
	}
}
?>