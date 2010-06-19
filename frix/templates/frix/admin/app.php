<? extend('frix/admin/base') ?>

<? block('contents') ?>
	<? if ($models_options): ?>
		<div id="data">
			<table>
				<colgroup>
					<col class="empty" />
					<col span="2" />
					<col class="empty" />
				</colgroup>
				<tr>
					<th><span>&nbsp;</span></th>
					<th><span>Model</span></th>
					<th><span>Edit</span></th>
					<th>&nbsp;</th>
				</tr>
				<? foreach ($models_options as $item): ?>
					<tr>
						<td>&nbsp;</td>
						<td><a href="<?= url($root, $item->meta->admin_url) ?>"><?= $item->meta->verbose_name_plural ?></a></td>
						<td>
							<span class="button"><a href="<?= url($root, $item->meta->admin_url) ?>">Change</a></span>
							<? if ($item->can_add): ?>
								<span class="button"><a href="<?= url($root, $item->meta->admin_url, 'add') ?>">Add</a></span>
							<? endif; ?>
						</td>
						<td>&nbsp;</td>
					</tr>
				<? endforeach; ?>
			</table>
		</div>
	<? else: ?>
		<p>No models for this app!</p>
	<? endif; ?>
<? end_block() ?>
