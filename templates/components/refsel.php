<label for="<?= $element->name ?>Id"><?= $element->caption ?></label>
<? $refselList = array(); ?>
<? if (preg_match_all("/#REFSEL\\|([^#]+)#/", $element->find, $matches)) { ?>
    <? foreach ($matches[1] as $refselRef) { ?>
        <? $refselList[] = $refselRef; ?>
    <? } ?>
<? } ?>
<? if ((!isset($element->change) || $element->change) || $_GET['action'] == "add") { ?>
    <select idNumber="<?= (isset($data->{$element->name})) ? $data->{$element->name} : "" ?>" 
            category="<?= $_GET['category'] ?>" 
            name="<?= $element->name ?>" 
            component="<?= $component->name ?>" 
            rootElement="<?= $component->name ?>" 
            element="<?= $element->name ?>" 
            class="refsel form-control" 
            id="<?= $element->name ?>Id" 
            <? $i = 0 ?>
            <? foreach ($refselList as $refselRef) { ?>
                refseldepends<?= $i ?>="<?= $refselRef ?>"
                <? $i++ ?>
            <? } ?>
            <? if (isset($element->onchange)) { ?>
                <? $i = 0 ?>
                <? foreach ($element->onchange as $onChangeItem) { ?>
                    onchange<?= $i ?>="<?= $onChangeItem ?>"
                    <? $i++ ?>
                <? } ?>
            <? } ?>
            parentId="<?= isset($_GET['parent_id']) && $_GET['parent_id'] ? $_GET['parent_id'] : $_GET['id'] ?>"
            placeholder="" aria-describedby="<?= $element->name ?>Help">
                <? if (isset($data->{$element->name}) && $data->{$element->name}) { ?>
            <option value="<?= $data->{$element->name} ?>"><?= $valueText ?></option>
        <? } ?>
    </select>
    <? if (isset($data->{$element->name}) && $data->{$element->name}) { ?>
        <small id="<?= $element->name ?>Help" class="form-text text-muted"><a target="_blank" href="<?= str_replace("#VALUE#", $data->{$element->name}, $element->url) ?>">Открыть</a></small>
    <? } ?>
<? } else { ?>
    <div>
        <input type="hidden" name="<?= $element->name ?>" value="<?= $data->{$element->name} ?>" />
        <? if ((!isset($element->nolink) || !$element->nolink) && $data->{$element->name}) { ?>
            <a target="_blank" class="form-control" href="<?= str_replace("#VALUE#", $data->{$element->name}, $element->url) ?>"><?= $valueText ?></a>
        <? } else { ?>
            <span class="form-control">
                <?= $valueText ?>
            </span>
        <? } ?>
    </div>
<? } ?>


