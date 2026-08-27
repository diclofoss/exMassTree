<label for="<?= $element->name ?>Id"><?= $element->caption ?></label>
<? if (isset($element->style) && $element->style == "summernote") { ?>
    <textarea <?= (isset($element->readonly) && $element->readonly) ? "disabled=\"\"" : "" ?> name="<?= $element->name ?>" class="form-control summernote" id="<?= $element->name ?>Id" rows="10"><?= (isset($data->{$element->name})) ? htmlentities($data->{$element->name}, ENT_QUOTES, "utf-8") : "" ?></textarea>
<? } else { ?>
    <textarea <?= (isset($element->readonly) && $element->readonly) ? "disabled=\"\"" : "" ?> name="<?= $element->name ?>" class="form-control" id="<?= $element->name ?>Id" rows="10"><?= (isset($data->{$element->name})) ? htmlentities($data->{$element->name}, ENT_QUOTES, "utf-8") : "" ?></textarea>
<? } ?>
<? if (isset($element->readonly) && $element->readonly) { ?>
    <input name="<?= $element->name ?>" type="hidden" value="<?= (isset($data->{$element->name})) ? htmlentities($data->{$element->name}, ENT_QUOTES, "utf-8") : "" ?>" />
<? } ?>
