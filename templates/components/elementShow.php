<? if (isset($data->{$element->name}) && $data->{$element->name}) { ?>
    <label for="<?= $element->name ?>Id"><?= $element->caption ?></label>
    <div class="form-control"><?= (isset($data->{$element->name})) ? $data->{$element->name} : "" ?></div>    
<?
}?>