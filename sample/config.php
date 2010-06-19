<?
// All errors except notices
error_reporting(E_ALL ^ E_NOTICE);

// Installed Apps
$config['APPS'] = array(
	'admin' => join_path($config['FRIX_ROOT'], 'apps/admin'),
	'main' => 'apps/main',
	'cms' => join_path($config['FRIX_ROOT'], 'apps/cms'),
	'auth' => join_path($config['FRIX_ROOT'], 'apps/auth'),
	'settings' => join_path($config['FRIX_ROOT'], 'apps/settings'),
);

// Database connection
$config['DB_URL'] = 'mysql://localhost/sample';

// Project Name
$config['PROJECT_TITLE'] = 'Sample Project';
$config['EMAIL_SUBJECT_PREFIX'] = 'Sample Project ';
?>
