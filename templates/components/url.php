<label for="<?= $element->name ?>Id"><?= $element->caption ?></label>
<? if (isset($element->readonly) && $element->readonly) { ?>
    <input name="<?= $element->name ?>" type="hidden" value="<?= (isset($data->{$element->name})) ? $data->{$element->name} : "" ?>" />
    <a class="form-control" href="<?= (isset($data->{$element->name})) ? $data->{$element->name} : "" ?>" target="_blank"><?= (isset($data->{$element->name})) ? $data->{$element->name} : "" ?></a>
<? } else { ?>
    <input <?= (isset($element->readonly) && $element->readonly) ? "disabled=\"\"" : "" ?> name="<?= $element->name ?>" type="text" class="form-control" value="<?= (isset($data->{$element->name})) ? $data->{$element->name} : "" ?>" id="<?= $element->name ?>Id" placeholder="">
    <? if (isset($data->{$element->name}) && $data->{$element->name}) { ?>
        <small id="<?= $element->name ?>Help" class="form-text text-muted"><a target="_blank" href="<?= $data->{$element->name} ?>">Открыть</a></small>
    <? } ?>
<? } ?>
