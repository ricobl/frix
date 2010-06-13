<? extend('frix/admin/base') ?>

<? block('contents') ?>
	<p>WARNING: This will remove all items related to this object.</p>

	<form action="./" method="post">
		<p class="buttons">
		<span class="button"><input type="submit" name="delete" value="Yes" /></span>
		<span class="button"><a href="../../">No</a></span>
		</p>
	</form>
<? end_block() ?>