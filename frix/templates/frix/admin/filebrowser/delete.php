<? extend('frix/admin/base') ?>

<? block('contents') ?>
	<div id="message">
		<p>Are you sure you want to delete <strong>"<?= $file_name ?>"</strong>?</p>
	</div>

	<form action="./?<?= $_SERVER['QUERY_STRING'] ?>" method="post">
		<p class="buttons">
		<span class="button"><input type="submit" name="delete" value="Yes" /></span>
		<span class="button"><a href="./">No</a></span>
		</p>
	</form>
<? end_block() ?>
