<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?= Frix::config('PROJECT_TITLE') ?> - Admin</title>
<link href="<?= url(Frix::config('FRIX_MEDIA'), 'admin/style/base.css') ?>" rel="stylesheet" type="text/css" />
<? block('scripts') ?><? end_block() ?>
</head>

<body<? block('body_attr') ?><? end_block() ?>>
<div id="container">
	
	<? if (!$popup): ?>
		<? block('header') ?>
			<div id="header">
				<h1><a href="<?= url($root) ?>"><?= Frix::config('PROJECT_TITLE') ?></a></h1>
				
				<ul id="toolbar">
					<li class="bt_logout"><a href="<?= url($root, 'logout') ?>">Logout</a></li>
				</ul>
				
				<div id="menu">
					<ul>
						<? foreach ($apps as $item): ?>
							<? if ($item->models): ?>
								<li<?= ($item->name == $app->name) ? ' class="active"' : '' ?>><a href="<?= $item->admin_url ?>"><?= $item->verbose_name ?></a></li>
							<? endif; ?>
						<? endforeach; ?>
						<li<?= $filebrowser ? ' class="active"' : '' ?>><a href="<?= url($root, 'browser') ?>">Filebrowser</a></li>
					</ul>
				</div>
			</div>
		<? end_block() ?>
	<? endif; ?>
	
	<div id="main">
		
		<? if ($msg): ?>
			<div id="message"<? if ($msg_type): ?> class="<?= $msg_type ?>"<? endif; ?>>
				<a href="./"><?= $msg ?></a>
			</div>
		<? endif; ?>
		
		<div id="info_bar">
			<h2><?= $breadcrumbs ? implode(' | ', $breadcrumbs) : 'Start Page' ?></h2>
			<? block('license') ?><? end_block() ?>
		</div>
		
		<? block('contents') ?><? end_block() ?>
	</div>
</div>

</body>
</html>
