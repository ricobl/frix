<? extend('base') ?>

<? block('contents') ?>
	<h2>Error <?= $error_code ?></h2>
	
	<div id="text_box">
		<p><?= $error_msg ?></p>
		<p>Please contact the administrator.</p>
	</div>
<? end_block() ?>
