<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?= Frix::config('PROJECT_TITLE') ?></title>
<link rel="stylesheet" href="<?= url(Frix::config('MEDIA_URL'), 'style/base.css') ?>" type="text/css">
<? block('head') ?><? end_block() ?>
</head>

<body <?= Template::attrs($body_attrs) ?>>

<div id="container">
	
	<div id="header">
		<h1><a href="<?= url(Frix::config('WEB_ROOT')) ?>"><?= Frix::config('PROJECT_TITLE') ?></a></h1>
		<div id="menu">
			<ul>
				<li><a href="<?= url(Frix::config('WEB_ROOT')) ?>">Home</a></li>
				<? foreach (Frix::app('cms')->get_root_pages() as $item): ?>
					<li><a href="<?= $item->get_absolute_url() ?>"><?= $item->title ?></a></li>
				<? endforeach; ?>
			</ul>
		</div>
	</div>
	
	<div id="main">
		<div id="contents">
			<? block('contents') ?><? end_block() ?>
		</div>

		<div id="sidebar">
			<? block('sidebar') ?>
				<div class="box">
					<h3>Pages</h3>
					<ul>
						<? foreach (Frix::app('cms')->get_root_pages() as $item): ?>
							<li><a href="<?= $item->slug ?>/"><?= $item->title ?></a></li>
						<? endforeach; ?>
					</ul>
				</div>
			<? end_block() ?>
		</div>
	</div>
</div>

</body>
</html>
