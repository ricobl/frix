<?
class CmsMenuItem extends Model {
	
	function setup ($meta) {
		$meta->verbose_name = 'Menu Item';
	}
	
	function __toString () {
		return sprintf('%s', $this->title);
	}
	
	function create_fields () {
		$this->add_fields(array(
			'menu' => new ForeignKey('Parent Menu', array('model_name' => 'CmsMenu', 'null' => True)),
			'title' => new CharField('Title', array('length' => 40)),
			'link' => new CharField('Link', array('length' => 150)),
			'visible' => new BooleanField('Visible', array('default' => True, 'editable' => False)),
			'pos' => new IntegerField('Position'),
		));
	}
	
	function get_absolute_url () {
		return url(Frix::config('WEB_ROOT'), $this->link);
	}
	
	static public function meta() {
		return ModelMeta::meta(get_class());
	}
}
?>
