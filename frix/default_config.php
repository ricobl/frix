<?
//////////////
/* DATABASE */
//////////////

// Database table prefix
$config['DB_PREFIX'] = 'frix_';
// Database connection
$config['DB_URL'] = 'mysql://root:123@localhost/frix';


///////////
/* PATHS */
///////////

// Frix FS root (this dir)
$config['FRIX_ROOT'] = dirname(__FILE__);
// FS project root
$config['ROOT'] = dirname($_SERVER['SCRIPT_FILENAME']);
// Static media path: 'ROOT/media'
$config['MEDIA_ROOT'] = join_path($config['ROOT'], 'media');

// Project root URL (same path of the index file getting the request)
$config['WEB_ROOT'] = dirname($_SERVER['SCRIPT_NAME']);
// Static media URL: 'WEB_ROOT/media/'
$config['MEDIA_URL'] = url($config['WEB_ROOT'], 'media');
// Frix media URL: 'WEB_ROOT/frix/media'
// $config['FRIX_MEDIA'] = url($config['WEB_ROOT'], 'frix/media');

// TODO: IIS sucks, no DOCUMENT_ROOT

	// From: http://helicron.net/php/
	/*
	$localpath=getenv("SCRIPT_NAME");
	$absolutepath=realpath($localPath);
	// a fix for Windows slashes
	$absolutepath=str_replace("\\","/",$absolutepath);
	$docroot=substr($absolutepath,0,strpos($absolutepath,$localpath));
	// as an example of use
	include($docroot."/includes/config.php"); 
	*/
	
	// From: http://fyneworks.blogspot.com/2007/08/php-documentroot-in-iis-windows-servers.html
	/*
	// let's make sure the $_SERVER['DOCUMENT_ROOT'] variable is set
	if (!isset($_SERVER['DOCUMENT_ROOT'])) {
		if (isset($_SERVER['SCRIPT_FILENAME'])) {
			$base = $_SERVER['SCRIPT_FILENAME'];
		}
	}
	
	if (!isset($_SERVER['DOCUMENT_ROOT'])) {
		if (isset($_SERVER['PATH_TRANSLATED'])) {
			$base = substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']);
		}
	}
	
	if ($base) {
		$_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', $base, 0, 0 - strlen($_SERVER['PHP_SELF']));
	}
	
	// $_SERVER['DOCUMENT_ROOT'] is now set - you can use it as usual...
*/

// Check http://br2.php.net/pathinfo for more solutions
$config['FRIX_MEDIA'] = url(str_replace(norm_path($_SERVER['DOCUMENT_ROOT']), '', norm_path($config['FRIX_ROOT'])), 'media');

// Adjust WEB_ROOT to work without mod_rewrite (add 'index.php/')
$config['WEB_ROOT'] = url($config['WEB_ROOT'], 'index.php/');


///////////////
/* TEMPLATES */
///////////////

// Template lookup paths
$config['TEMPLATE_PATHS'] = array(
	join_path($config['ROOT'], 'templates'),
	join_path($config['FRIX_ROOT'], 'templates'),
);


//////////
/* APPS */
//////////

// Installed Apps
// TODO: maybe use config paths here, instead of 
$config['APPS'] = array(
	'auth' => join_path($config['FRIX_ROOT'], 'apps/auth'),
	'admin' => join_path($config['FRIX_ROOT'], 'apps/admin'),
);


////////////
/* ROUTES */
////////////

// Get routes from the project root
@include_once( join_path($config['ROOT'], 'routes.php') );


//////////
/* MISC */
//////////

// Project Name
$config['PROJECT_TITLE'] = 'Frix::php';

// Help / support link on admin
$config['ADMIN_HELP_LINK'] = 'http://github.com/ricobl/frixphp';
?>
