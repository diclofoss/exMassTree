<label for="<?= $element->name ?>Id"><?= $element->caption ?></label>
<div class="row">
    <div class="col-md-12">
        <div class="custom-file mb-3">
            <input type="file" name="file_upload_<?= $element->name ?>" class="custom-file-input" id="<?= $element->name ?>Id">
            <label class="custom-file-label" for="customFile">Выберите файл...</label>
        </div>
    </div>
    <? if (isset($data->{$element->name}) && $data->{$element->name}) { ?>
        <div class="col-md-12 file-actions-container">
            <a href="<?= $data->{$element->name} ?>" class="btn btn-info btn-sm" target="_blank" style="margin-right: 10px;">Скачать</a>
            <button type="button" class="btn btn-danger btn-sm fileDeleteBtn" data-element="<?= $element->name ?>">Удалить</button>
        </div>
    <? } ?>
</div>
<? if (isset($data->{$element->name}) && $data->{$element->name}) { ?>
    <input type="hidden" name="<?= $element->name ?>" value="<?= htmlentities($data->{$element->name}, ENT_QUOTES, "utf-8") ?>">
<? } else { ?>
    <input type="hidden" name="<?= $element->name ?>" value="">
<? } ?>