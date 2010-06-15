<?
// Get models
Frix::model('cms', 'CmsPage');
Frix::model('cms', 'CmsFile');
Frix::model('cms', 'CmsImage');
Frix::model('cms', 'CmsVideo');

Frix::model('cms', 'CmsMenu');
Frix::model('cms', 'CmsMenuItem');

class CmsPageOptions extends AdminOptions {
	
	public $inlines = array('CmsFile', 'CmsImage', 'CmsVideo');
	// public $list_display = array('id', 'parent', 'title', 'slug', 'visible');
	public $list_display = array('title', 'slug', 'visible');
	
	public $custom_position = 'pos';
	
}

class CmsMenuOptions extends AdminOptions {
	
	public $inlines = array('CmsMenuItem');
	
}

// Register
Frix::app('admin')->register('CmsPage', 'CmsPageOptions');
Frix::app('admin')->register('CmsMenu', 'CmsMenuOptions');
?>
