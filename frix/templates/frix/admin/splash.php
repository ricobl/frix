<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?= Frix::config('PROJECT_TITLE') ?> - Admin</title>
<link href="<?= Frix::config('FRIX_MEDIA') ?>admin/style/base.css" rel="stylesheet" type="text/css" />
</head>

<body id="splash">
	<div id="container">
		
		<div id="header">
			<h1><a href="<?= url($root) ?>"><?= Frix::config('PROJECT_TITLE') ?></a></h1>
		</div>

		<? if ($msg): ?>
			<div id="message"<? if ($msg_type): ?> class="<?= $msg_type ?>"<? endif; ?>>
				<a href="./"><?= $msg ?></a>
			</div>
		<? endif; ?>

		<div id="main">
			<div id="splash_box">
				<? block('contents') ?><? end_block() ?>
			</div>
		</div>
		
	</div>
</body>
</html>
