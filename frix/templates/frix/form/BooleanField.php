<input name="<?= $field->attrs['name'] ?>" type="hidden" value="0" />
<input <?= Template::attrs($field->attrs) ?> value="1" class="<?= $field->type ?>_field" />