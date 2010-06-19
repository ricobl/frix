<form <?= Template::attrs($form->attrs) ?> enctype="multipart/form-data">
	<?= $form->render_fields() ?>
	<?= $form->extra ?>
	<p class="buttons"><input type="submit" name="submit" value=" Save " /></p>
</form>
