<input <?= Template::attrs($field->attrs) ?> class="<?= $field->type ?>_field" /><br />
<? if ($field->value): ?><span>File: </span><a href="<?= $field->get_url() ?>"><?= $field->get_url() ?></a><? endif; ?>
