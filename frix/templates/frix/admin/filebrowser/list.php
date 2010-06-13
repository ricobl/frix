<? extend('frix/admin/base') ?>

<? if ($popup): ?>
	<? block('body_attr') ?> id="filebrowser" class="popup"<? end_block() ?>
	<? block('scripts') ?>
	<script type="text/javascript" src="<?= url(Frix::config('FRIX_MEDIA'), 'gz.php?file=admin/js/j.js') ?>"></script>
	<script type="text/javascript" src="<?= url(Frix::config('FRIX_MEDIA'), 'admin/js/tiny_mce/tiny_mce_popup.js') ?>"></script>
	<script type="text/javascript" src="<?= url(Frix::config('FRIX_MEDIA'), 'admin/js/tiny_mce/tiny_mce_popup_setup.js') ?>"></script>
	<? end_block() ?>
<? else: ?>
	<? block('body_attr') ?> id="filebrowser"<? end_block() ?>
<? endif; ?>

<? block('contents') ?>
	
	<div id="quick_forms">
		<form action="./" method="post">
			<p>
				<label>
					<input type="text" name="dir_name" />
				</label>
				<span class="button"><input type="submit" name="new_dir" value="Create Folder" /></span>
			</p>
		</form>
		<form action="./" method="post" enctype="multipart/form-data">
			<p>
				<label>
					<input type="file" name="file" />
				</label>
				<span class="button"><input type="submit" name="new_file" value="Upload File" /></span>
			</p>
		</form>
	</div>
	
	<? if ($files): ?>
		<div id="data">
			<table>
				<colgroup>
					<col class="empty" />
					<? if ($popup): ?><col width="50" /><? endif; ?>
					<col />
					<col width="100" />
					<col class="actions" />
					<col class="empty" />
				</colgroup>
				<tr>
					<th><span>&nbsp;</span></th>
					<? if ($popup): ?><th><span>&nbsp;</span></th><? endif; ?>
					<th><span>Filename</span></th>
					<th><span>Size</span></th>
					<th><span>Actions</span></th>
					<th>&nbsp;</th>
				</tr>
				<? foreach ($files as $item): ?>
					<tr class="<?= $item['class'] ?>">
						<td>&nbsp;</td>
						<? if ($popup): ?>
							<td class="select">
								<? if (!$item['is_dir']): ?>
									<a href="<?= $item['link'] ?>" title="Use this file!">Select</a>
								<? else: ?>
									&nbsp;
								<? endif; ?>
							</td>
						<? endif; ?>
						<td class="name"><a href="<?= $item['link'] ?>" target="<?= $item['target'] ?>"><?= $item['name'] ?></a></td>
						<td><?= $item['size'] ?></td>
						<td>
							<? if (!$item['is_dir'] || $item['is_empty']): ?>
								<span class="button"><a href="?del=<?= $item['name'] ?>">delete</a></span>
							<? else: ?>
								<a href="<?= $item['link'] ?>">Not empty!</a>
							<? endif; ?>
						</td>
						<td>&nbsp;</td>
					</tr>
				<? endforeach; ?>
			</table>
		</div>
	<? else: ?>
		<p>No items found.</p>
	<? endif; ?>
<? end_block() ?>