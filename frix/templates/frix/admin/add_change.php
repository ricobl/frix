<? $this->extend('frix/admin/base') ?>

<? block('scripts') ?>
<script type="text/javascript" src="<?= url(Frix::config('FRIX_MEDIA'), 'gz.php?file=admin/js/j.js') ?>"></script>
<script type="text/javascript" src="<?= url(Frix::config('FRIX_MEDIA'), 'gz.php?file=admin/js/add_change.js') ?>"></script>
<? end_block() ?>

<? $this->block('contents') ?>
	<form <?= Template::attrs($form->attrs) ?> enctype="multipart/form-data">
		
		<?= $form->render_fields() ?>
			
		<? foreach ($inlines as $inline): ?>
			<fieldset class="inline">
				<h3><?= $inline->meta->verbose_name_plural ?></h3>
				<table>
					<?
					// TODO: use COL tags to style the table
					?>
					<tr>
						<? foreach ($inline->get_fields() as $item): ?>
							<? if ($item->editable): ?>
								<th><?= $item->verbose_name ?></th>
							<? endif; ?>
						<? endforeach; ?>
					</tr>
					<? foreach ($inline->get_forms() as $inline_form): ?>
						<tr><?= $inline_form->render_fields() ?></tr>
					<? endforeach; ?>
				</table>
			</fieldset>
		<? endforeach; ?>
			
		<p class="buttons">
			<span class="button"><input type="submit" name="submit" value="Save" /></span>
			<span class="button"><a href="../">Cancel</a></span>
		</p>
		
	</form>
<? $this->end_block() ?>
