<select <?= Template::attrs($field->attrs) ?>>
	<? foreach ($field->choices as $k => $v): ?>
		<option value="<?= htmlentities($k) ?>" <?= ($k == $field->value) ? ' selected="selected"' : '' ?>><?= $v ?></option>
	<? endforeach; ?>
</select>
