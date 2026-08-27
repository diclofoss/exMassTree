<?php
// Суффикс id модалки должен совпадать с button.js: при наличии data на кнопке ищется elementName + "Modal_" + rowId
$cardRowId = (isset($_GET['id']) && $_GET['id'] !== '') ? (string) $_GET['id'] : '';
$modalIdSuffix = $cardRowId !== '' ? '_' . htmlspecialchars($cardRowId, ENT_QUOTES, 'UTF-8') : '';
?>
<label for="<?= $element->name ?>Id"><?= $element->caption ?></label>
<div class="form-group">
    <button type="button" element="<?= $element->name ?>" category="<?= $_GET['category'] ?>" component="<?= $component->name ?>"<?php if ($cardRowId !== '') { ?> data="<?= htmlspecialchars($cardRowId, ENT_QUOTES, 'UTF-8') ?>"<?php } ?> class="<? if (!isset($element->input) || !$element->input) { ?>buttonAction<? } else { ?>modalOpenButtonAction<? } ?> btn btn-dark"><?= $element->btnCaption ?></button>
</div>
<? if (isset($element->input) && $element->input) { ?>
    <div class="modal fade" id="<?= $element->name ?>Modal<?= $modalIdSuffix ?>" tabindex="-1" role="dialog" aria-labelledby="<?= $element->name ?>ModalLabel<?= $modalIdSuffix ?>" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="<?= $element->name ?>ModalLabel<?= $modalIdSuffix ?>"><?= $element->caption ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <? foreach ($element->input as $name => $caption) { ?>
                        <div class="form-group">
                            <label for="<?= $name ?>Id<?= $modalIdSuffix ?>"><?= $caption ?></label>
                            <? if (isset($element->fileInputList) && in_array($name, $element->fileInputList)) { ?>
                                <div class="custom-file mb-3">
                                    <input type="file" name="file_upload_<?= $name ?>_<?= $element->name ?>" class="custom-file-input" id="<?= $name ?>Id<?= $modalIdSuffix ?>">
                                    <label class="custom-file-label" for="customFile">Выберите файл...</label>
                                </div>
                            <? } else { ?>
                                <input type="<?= (isset($element->datepickers) && in_array($name, $element->datepickers)) ? "date" : "text" ?>" class="form-control" id="<?= $name ?>Id<?= $modalIdSuffix ?>" name="<?= $name ?>_<?= $element->name ?>" />
                            <? } ?>
                        </div>
                    <? } ?>
                </div>
                <div class="modal-footer">
                    <button <? if (isset($element->url) && $element->url) { ?>urlMode="true"<? } ?> element="<?= $element->name ?>" category="<?= $_GET['category'] ?>" component="<?= $component->name ?>"<?php if ($cardRowId !== '') { ?> data="<?= htmlspecialchars($cardRowId, ENT_QUOTES, 'UTF-8') ?>"<?php } ?> type="button" class="modalButtonAction btn btn-primary" data-dismiss="modal">Отправить</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                </div>
            </div>
        </div>
    </div>
<? } ?>
