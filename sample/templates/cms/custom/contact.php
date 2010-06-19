<? extend('cms/base') ?>

<? block('contents') ?>
	<h2><?= $cms_page->title ?></h2>

	<div id="text_box">
		<p><?= preg_replace('/(\r|\r\n?)/', '</p><p>', $cms_page->content) ?></p>
	</div>
		
	<? if ($error): ?>
		<div id="message">
			<p><?= $error ?></p>
		</div>
	<? endif; ?>
	<?= $contact_form->render() ?>
<? end_block() ?>
