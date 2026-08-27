<label for="<?= $element->name ?>Id"><?= $element->caption ?></label>
<input <?= (isset($element->readonly) && $element->readonly) ? "disabled=\"\"" : "" ?> name="<?= $element->name ?>" type="text" class="form-control" value="<?= (isset($data->{$element->name})) ? $data->{$element->name} : (isset($element->default) ? $element->default : "") ?>" id="<?= $element->name ?>Id" placeholder="" />
<? if (isset($element->readonly) && $element->readonly) { ?>
    <input name="<?= $element->name ?>" type="hidden" value="<?= (isset($data->{$element->name})) ? $data->{$element->name} : "" ?>" />
<? } ?>
