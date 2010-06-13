<?
// URL Routing for admin app
$routes = array(
	
	// files_list($path)
	'^(/browser)((.*)/)$' => 'admin/files_list',
	'^(/pop_browser)((.*)/)$' => 'admin/files_list_pop',
	
	// forgot()
	'^/forgot/$' => 'admin/forgot',
	// logout()
	'^/logout/$' => 'admin/logout',
	// index()
	'^/$' => 'admin/index',
	// app($app)
	'^/([a-z\-_]+)/$' => 'admin/app',
	// list($app, $model)
	'^/([a-z\-_]+)/([a-z\-_]+)/$' => 'admin/items',
	// add($app, $model)
	'^/([a-z\-_]+)/([a-z\-_]+)/add/$' => 'admin/add',
	// change($app, $model, $id)
	'^/([a-z\-_]+)/([a-z\-_]+)/([0-9]+)/$' => 'admin/change',
	// delete($app, $model, $id)
	'^/([a-z\-_]+)/([a-z\-_]+)/([0-9]+)/delete/$' => 'admin/delete',
	
	// Ordering
	// order($app, $model, $id, $direction)
	'^/([a-z\-_]+)/([a-z\-_]+)/([0-9]+)/order/(up|down)/$' => 'admin/order',
);
?>