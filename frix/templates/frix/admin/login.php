<? extend('frix/admin/splash') ?>

<? block('contents') ?>
	<form  action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" enctype="multipart/form-data">
		
		<h1>CMS</h1>
		
		<?= $form->render_fields(); ?>
		
		<p class="buttons">
			<?= $message ? $message . '<br />' : '' ?>
			<input type="submit" class="button" name="submit" value="OK" />
		</p>
	</form>

	<p><a href="forgot/">Forgot Password?</a></p>
<? end_block() ?>
