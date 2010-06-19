<? extend('frix/admin/base') ?>

<? block('contents') ?>
	<? if ($options->can_add): ?>
		<p><span class="button"><a href="add/">Create <?= $model->verbose_name ?></a></span></p>
	<? endif; ?>

	<? if ($items): ?>
		<div id="data">
			<table>
				<colgroup>
					<col span="<?= count($options->list_display) ?>" />
					<col class="actions" />
				</colgroup>
				<tr>
					<? foreach ($options->head_display as $name): ?>
						<th><span><?= $options->list_display_header($name) ?></span></th>
					<? endforeach; ?>
					<th><span>Edit</span></th>
				</tr>
				<? foreach ($items as $item): ?>
					<tr>
						<? foreach ($options->list_display as $name): ?>
							<td><?= $options->list_display_column($item, $name) ?></td>
						<? endforeach; ?>
						<td>
							<? if ($options->can_change): ?>
								<span class="button"><a href="<?= $item->get_pk() ?>/">change</a></span>
							<? endif; ?>
							<? if ($options->can_delete): ?>
								<span class="button"><a href="<?= $item->get_pk() ?>/delete/">delete</a></span>
							<? endif; ?>
						</td>
					</tr>
				<? endforeach; ?>
			</table>
		</div>
	<? else: ?>
		<div id="message">
			<p>No items found.</p>
		</div>
	<? endif; ?>
<? end_block() ?>
