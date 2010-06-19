<fieldset class="inline">
	<h3><?= $form->model->verbose_name_plural ?></h3>
	<table>
		<?
		// TODO: use COL tags to style the table
		?>
		<tr>
			<? foreach ($form->fields as $k => $item): ?>
				<? if ($item->editable): ?>
					<th><?= $item->verbose_name ?></th>
				<? endif; ?>
			<? endforeach; ?>
		</tr>
		<?= $form->extra ?>
	</table>
</fieldset>
