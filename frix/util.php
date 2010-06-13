<?
define('CHAR_LF', "\n");
define('CHAR_CR', "\r");
define('CHAR_CRLF', "\r\n");
define('CHAR_TAB', "\t");

class ImportException extends Exception {};

// Load a PHP file
function import () {
	
	// Fix path separators
	$path = join_path(func_get_args());
	
	// Try to get the file
	if (@include_once($path)) {
		return true;
	}
	
	// Throw an exception if not found
	throw new ImportException(sprintf('Couldn\'t import file "%s"', $path));
	
}

// Load an internal FRIX module
function load ($package) {
	// Get the package path based on FRIX_ROOT constant
	return import(join_path(Frix::config('FRIX_ROOT'), 'mod', $package . '.php'));
}

// Normalize a path
function norm_path ($path, $sep = DIRECTORY_SEPARATOR) {
	// Replace (one or more)
		// slash: \/
		// backslash: \\\
	// With $sep
    return preg_replace('#[\/\\\]+#', $sep, $path);
}

// Join path parts
function join_path ($parts, $sep = DIRECTORY_SEPARATOR) {
	// If $parts isn't an array
	if (!is_array($parts)) {
		// Use all args as the parts to join
		$parts = func_get_args();
		// Restore the default separator
		$sep = DIRECTORY_SEPARATOR;
	}
	// Join path parts and normalize
	return norm_path(implode($sep, $parts), $sep);
}

// Assign a default value for a variable if its value is not defined
function def (&$var, $def) {
	if (!$var) {
		$var = $def;
	}
	return $var;
}

// Get a list of parent classes using an optional sprintf format
function get_parent_classes ($obj, $format = '%s') {
	
	$class = get_class($obj);
	$classes = array();
	
	while ($class) {
		$classes[] = sprintf($format, $class);
		$class = get_parent_class($class);
	}
	
	return $classes;
	
}

// Limit a string length
function truncate ($str, $len = 80) {
	
	// Check if the string is already smaller than the length
	if (strlen($str) <= $len) {
		return $str;
	}
	
	// Replace the last non-word followed by a word
	return preg_replace('/\W\w*$/', '...', substr($str, 0, $len));
	
}

// Normalizes a string
function slugify ($str) {
	
	// TODO: deal with some special cases (read html entities table)
	
	$str = preg_replace(array('/[^\w\s]/', '/\\s+/') , array(' ', '-'), $str);
	
	$str = strtolower($str);
	
	return $str;
	
}

// Convert a string to camelcase
// Takes an optional separator
function camel ($str, $sep = '_') {
	// 1: replaces underscores with spaces
	// 2: make first char of words uppercase
	// 3: removes spaces
	return str_replace(' ', '', ucwords(str_replace($sep, ' ', $str)));
}

// Convert a string from camelcase
// Takes an optional separator
function uncamel ($str, $sep = '_') {
	// Add $sep before uppercase chars and remove first $sep
	$str = strtolower(preg_replace(array('/[A-Z]/', '/^' . $sep . '/'), array($sep . '\0', ''), $str));
	return $str;
}

// Join url pieces together
function url () {
	
	$args = func_get_args();
	
	// Join the parts
	$url = implode('/', $args);
	
	// Check for a file-extension in the last part
	// to find out if is a directory or a file
	if (!preg_match('#\..+$#', $args[count($args) - 1])) {
		$url .= '/';
	}
	
	// Replace double (or more) consecutive slashes
	$url = preg_replace('#/+#', '/', $url);
	
	return norm_path($url, '/');
}

// Redirect to another URL
function redir ($url) {
	header('Location: ' . $url);
	exit;
}

function debug () {
	
	while (ob_end_clean());
	
	if (ini_get('html_errors')) {
		echo '<pre>';
	}
	
	var_dump(func_get_args());
	exit;
}

function toggle (&$tog, $value) {
	$tog = !$tog;
	return $tog ? $value : '';
}

// Checks for a valid e-mail address
function validate_email ($email) {
	return preg_match('|^[a-zA-Z0-9._-]+@([a-zA-Z0-9._-]+)\.([a-zA-Z0-9_-]){2,3}$|i', $email);
}

function send_mail ($subject, $msg, $from, $to) {
	
	if ( !(validate_email($from) || validate_email($to)) ) {
		// throw new Exception('Invalid e-mail address.');
		return false;
	}
	
	load('Email');
	$email = new Email;
	
	return $email->send(Frix::config('EMAIL_SUBJECT_PREFIX') . $subject, $msg, $from, $to);
	
}

// Random pronunciable password generator
function make_pass ($len = 8) { 
	$vowels = array("a", "e", "i", "o", "u"); 
	$cons = array("b", "c", "d", "f", "g", "j", "k", "l", "m", "n", "p", "r", "s", "t", "v", "w", "x", "y", "z", "ch", "sh", "lh", "fr", "tr", "cr", "pr", "pl", "cl");
	 
	$num_vowels = count($vowels); 
	$num_cons = count($cons); 

	while (strlen($pwd) < $len) {
		$pwd .= $cons[rand(0, $num_cons - 1)] . $vowels[rand(0, $num_vowels - 1)];
	} 
	 
	return substr($pwd, 0, $len); 
}
?>