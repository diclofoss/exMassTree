<label for="<?= $element->name ?>Id"><?= $element->caption ?></label>
<select <?= (isset($element->readonly) && $element->readonly) ? "disabled=\"\"" : "" ?> name="<?= $element->name ?>" id="<?= $element->name ?>Id" class="form-control">
    <? if (isset($element->specialKeys) && $element->specialKeys) { ?>
        <? foreach ($element->items as $val => $item): ?>
            <? if (isset($data->{$element->name}) && $data->{$element->name} == $val) { ?>
                <option selected="" value="<?= $val ?>"><?= $item ?></option>
            <? } else { ?>
                <option value="<?= $val ?>"><?= $item ?></option>
            <? } ?>
        <? endforeach; ?>
    <? } else { ?>
        <? $i = 0; ?>
        <? foreach ($element->items as $item): ?>
            <? if (isset($data->{$element->name}) && $data->{$element->name} == $i) { ?>
                <option selected="" value="<?= $i ?>"><?= $item ?></option>
            <? } else { ?>
                <option value="<?= $i ?>"><?= $item ?></option>
            <? } ?>
            <? $i++; ?>
        <? endforeach; ?>
    <? } ?>
</select>
