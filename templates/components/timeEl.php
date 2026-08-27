<label for="<?= $element->name ?>Id"><?= $element->caption ?></label>
<div class="form-group">
    <? if (dateEl::isNull($element)) { ?>
        <? if (isset($data->{$element->name})) { ?>
            <? $h = date("H", strtotime($data->{$element->name})) ?>
            <? $m = date("i", strtotime($data->{$element->name})) ?>            
        <? } else { ?>
            <? $h = date("H") ?>
            <? $m = date("i") ?>
        <? } ?>
    <? } else { ?>
        <? if (isset($data->{$element->name})) { ?>
            <? $h = date("H", strtotime($data->{$element->name})) ?>
            <? $m = date("i", strtotime($data->{$element->name})) ?>            
        <? } else { ?>
            <? $h = date("H") ?>
            <? $m = date("i") ?>
        <? } ?>
    <? } ?>
    <select name="<?= $element->name ?>_h">
        <? for ($i = 0; $i < 24; $i++) { ?>
            <option <?= ($i == $h) ? "selected" : "" ?> value="<?= $i ?>"><?= $i ?></option>
        <? } ?>
    </select>
    :
    <select name="<?= $element->name ?>_m">
        <? for ($i = 0; $i < 60; $i++) { ?>
            <option <?= ($i == $m) ? "selected" : "" ?> value="<?= $i ?>"><?= $i ?></option>
        <? } ?>
    </select>
    <input name="<?= $element->name ?>" type="hidden" value="custom_value" />
</div>
<? if (isset($element->readonly) && $element->readonly) { ?>
    <input name="<?= $element->name ?>_date" type="hidden" value="<?= $data->{$element->name} ?>" />
<? } ?>
