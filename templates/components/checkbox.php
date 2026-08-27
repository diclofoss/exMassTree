<label for="<?= $element->name ?>Id"><?= $element->caption ?></label>
<? if (!isset($element->readonly) || !$element->readonly) { ?>
    <div class="form-group form-check">
        <label><input type="checkbox" class="form-check-input" <?= (isset($data->{$element->name}) && $data->{$element->name} == 1) ? "checked" : "" ?> value="1" name="<?= $element->name ?>" id="<?= $element->name ?>Id"> <?= (isset($element->items)) ? $element->items[1] : "Да" ?></label>
    </div>
<? } else { ?>
    <? if (isset($data->{$element->name})) { ?>
        <input name="<?= $element->name ?>" type="hidden" value="1" />
        <span class="form-control">
            <?= (isset($element->items)) ? $element->items[1] : "Да" ?>
        </span>
    <? } else { ?>
        <span class="form-control">
            <?= (isset($element->items)) ? $element->items[0] : "Нет" ?>
        </span>
    <? } ?>
<? } ?>
