<?
class CmsApp extends App {
	
	function get_root_pages () {
		$this->load_model('CmsPage');
		return CmsPage::meta()->filter(array('visible' => true))->all(array('parent' => null));
	}

	function get_child_pages ($slug) {
		
		$this->load_model('CmsPage');
		
		$page = CmsPage::meta()->one(array('slug' => $slug));
		
		if (!$page) {
			return array();
		}
		
		return CmsPage::meta()->filter(array('visible' => true))->all(array('parent' => $page->id));
		
	}
	
}
?>
