<? extend('base') ?>

<? block('contents') ?>
	<h2><?= $cms_page->title ?></h2>

	<div id="text_box">
		<p><?= preg_replace('/(\r|\r\n?)/', '</p><p>', $cms_page->content) ?></p>
	</div>
<? end_block() ?>
		
<? block('sidebar') ?>
	<? if ($cms_pages): ?>
		<div class="box">
			<h3>Child Pages</h3>
			<ul>
				<? foreach ($cms_pages as $item): ?>
					<li><a href="<?= $item->slug ?>/"><?= $item->title ?></a></li>
				<? endforeach; ?>
			</ul>
		</div>
	<? endif; ?>
	
	<? if ($cms_videos): ?>
		<div class="box">
			<h3>Videos</h3>
			<ul>
				<? foreach ($cms_videos as $item): ?>
					<li>
						<a href="<?= $item->fields['file']->get_url() ?>" target="_blank"><?= $item->name ?></a>
					</li>
				<? endforeach; ?>
			</ul>
		</div>
	<? endif; ?>
	
	<? if ($cms_images): ?>
		<div class="box">
			<h3>Images</h3>
			<ul>
				<? foreach ($cms_images as $item): ?>
					<li>
						<a href="<?= $item->fields['file']->get_url() ?>" target="_blank"><?= $item->name ?></a>
					</li>
				<? endforeach; ?>
			</ul>
		</div>
	<? endif; ?>
	
	<? if ($cms_files): ?>
		<div class="box">
			<h3>Files</h3>
			<ul>
				<? foreach ($cms_files as $item): ?>
					<li><a href="<?= $item->fields['file']->get_url() ?>" target="_blank"><?= $item->name ?></a></li>
				<? endforeach; ?>
			</ul>
		</div>
	<? endif; ?>
<? end_block() ?>
