<? extend('frix/admin/splash') ?>

<? block('contents') ?>
	<form  action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" enctype="multipart/form-data">
		
		<h1>CMS</h1>
		
		<?= $form->render_fields(); ?>
		
		<p><?= $message ?></p>
		
		<p class="buttons">
			<input type="submit" class="button" name="submit" value="OK" />
		</p>
		
	</form>

	<p><a href="../">Login</a></p>
<? end_block() ?>
