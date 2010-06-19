<?
load('Form');

// Load models
Frix::model('cms', 'CmsPage');

class Views {
	
	function video ($path, $id) {
		
		Frix::model('cms', 'CmsVideo');
		
		$video = CmsVideo::meta()->one(array('id' => $id));
		
		if (!$video) {
			throw new Http404Exception;
		}
		
		$cms_page = self::get_page($path);
		
		$context = array(
			'video' => $video,
			'cms_page' => $cms_page,
			'body_attrs' => array('id' => $cms_page->slug, 'class' => 'video'),
		);
		
		$t = new Template('cms/video');
		echo $t->render($context);
		
	}
	
	static function get_page ($path) {
		
		$pages = explode('/', $path);
		
		// Stop if path is empty or starts with one of the grouping pages
		if (!$path || in_array($pages[0], $ignore_root_pages)) {
			throw new Http404Exception;
		}
		
		// Get the last item and remove from the array
		$slug = array_pop($pages);
		
		// Get the page
		$cms_page = CmsPage::meta()->filter(array('visible' => True))->one(array('slug' => $slug));
		
		// Page not found? Exit...
		if (!$cms_page) {
			throw new Http404Exception;
		}
		// Initial page
		$parent_page = $cms_page;
		
		// Try to get each parent page, checking the slug against the path
		// from the last path part until the first
		do {
			
			// Get parent page
			$parent_page = $parent_page->fields['parent']->get_instance();
			// Get the last item and remove from the array
			$slug = array_pop($pages);
			
			// Page found?
			if ($parent_page) {
				// Slug doesn't match?
				if ($parent_page->slug != $slug) {
					// Got one of the ignorable root pages?
					if (in_array($parent_page->slug, $ignore_root_pages)) {
						// Exit the loop
						break;
					}
					else {
						// Throw not found error
						throw new Http404Exception;
					}
				}
			}
			
		} while ($slug && $parent_page);
		
		return $cms_page;
	}
	
	function page ($path, $context = array()) {
		
		$cms_page = self::get_page($path);
		
		$slug = $cms_page->slug;
		$pk_lookup = array('parent' => $cms_page->get_pk());
		
		Frix::model('cms', 'CmsFile');
		Frix::model('cms', 'CmsImage');
		Frix::model('cms', 'CmsVideo');

		$context += array(
			'cms_page' => $cms_page,
			'cms_files' => CmsFile::meta()->all($pk_lookup),
			'cms_images' => CmsImage::meta()->all($pk_lookup),
			'cms_videos' => CmsVideo::meta()->all($pk_lookup),
			'cms_pages' => CmsPage::meta()->filter(array('visible' => True))->all($pk_lookup),
			'body_attrs' => array('id' => $slug),
		);
		
		$t = new Template('cms/custom/' . $slug, 'cms/page');
		echo $t->render($context);
		
	}
	
	function contact () {
		
		if ($_GET['sent']) {
			return $this->page('contact', array('sent' => True));
		}
		
		$f = new Form();
		$f->add_fields(array(
			'name' => new CharField('Name', array('length' => 100)),
			'email' => new CharField('E-mail', array('length' => 100)),
			'message' => new TextField('Message'),
		));
		
		$context = array(
			'contact_form' => $f,
		);
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			
			$f->input($_POST);
			
			if ( !($_POST['name'] && $_POST['email'] && $_POST['message']) ) {
				$context['error'] = 'All fields are required. Please complete the form.';
			}
			elseif (!validate_email($_POST['email'])) {
				$context['error'] = 'Invalid e-mail address.';
			}
			else {
				
				// Get e-mail address from the Settings app
				$to = Frix::app('settings')->get('contact_email');
				
				// Send the e-mail
				$ok = send_mail('Contact', $f->get_message(), $f->get_email(), $to);
				
				// Error sending the message?
				if (!$ok) {
					$context['error'] = 'Couldn\'t send message, please try again later.';
				}
				else {
					redir('./?sent=1');
				}
				
			}
			
		}
		
		return $this->page('contact', $context);
		
	}
}
?>
