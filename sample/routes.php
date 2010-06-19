<?
// URL Routing
$config['ROUTES'] = array(
	'^(/admin)(.*)' => 'admin/route',
	
	'^/$' => 'main/index',
	
	'^/contact/$' => 'cms/contact',
	'^/([-\w/]+)/$' => 'cms/page',
);
?>
