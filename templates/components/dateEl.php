<label for="<?= $element->name ?>Id"><?= $element->caption ?></label>
<div class="form-group">
    <? if (dateEl::isNull($element)) { ?>
        <? if (isset($data->{$element->name})) { ?>
            <? $date = date("Y-m-d", strtotime($data->{$element->name})) ?>
            <input class="form-control" <?= (isset($element->readonly) && $element->readonly) ? "disabled=\"\"" : "" ?> name="<?= $element->name ?>_date" type="date" value="<?= $date ?>" id="<?= $element->name ?>DateId" />
        <? } else { ?>
            <input class="form-control" <?= (isset($element->readonly) && $element->readonly) ? "disabled=\"\"" : "" ?> name="<?= $element->name ?>_date" type="date" value="" id="<?= $element->name ?>DateId" />
        <? } ?>
    <? } else { ?>
        <? if (isset($data->{$element->name})) { ?>
            <? $date = date("Y-m-d", strtotime($data->{$element->name})) ?>
        <? } else { ?>
            <? $date = date("Y-m-d") ?>
        <? } ?>
        <input class="form-control" <?= (isset($element->readonly) && $element->readonly) ? "disabled=\"\"" : "" ?> name="<?= $element->name ?>_date" type="date" value="<?= $date ?>" id="<?= $element->name ?>DateId" />
    <? } ?>    
    <input name="<?= $element->name ?>" type="hidden" value="custom_value" />
</div>
<? if (isset($element->readonly) && $element->readonly) { ?>
    <input name="<?= $element->name ?>_date" type="hidden" value="<?= $data->{$element->name} ?>" />
<? } ?>
