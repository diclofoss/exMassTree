<?php
// Шаблон для отображения кнопки в списке
// $rowId - ID текущей строки из списка
?>
<button type="button" element="<?= $element->name ?>" category="<?= $_GET['category'] ?>" component="<?= $component->name ?>" data="<?= $rowId ?>" class="<? if (!isset($element->input) || !$element->input) { ?>buttonAction<? } else { ?>modalOpenButtonAction<? } ?> btn btn-dark btn-sm"><?= $element->btnCaption ?></button>
<? if (isset($element->input) && $element->input) { ?>
    <div class="modal fade" id="<?= $element->name ?>Modal_<?= $rowId ?>" tabindex="-1" role="dialog" aria-labelledby="<?= $element->name ?>ModalLabel_<?= $rowId ?>" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="<?= $element->name ?>ModalLabel_<?= $rowId ?>"><?= $element->caption ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <? foreach ($element->input as $name => $caption) { ?>
                        <div class="form-group">
                            <label for="<?= $name ?>Id_<?= $rowId ?>"><?= $caption ?></label>
                            <? if (isset($element->fileInputList) && in_array($name, $element->fileInputList)) { ?>
                                <div class="custom-file mb-3">
                                    <input type="file" name="file_upload_<?= $name ?>_<?= $element->name ?>" class="custom-file-input" id="<?= $name ?>Id_<?= $rowId ?>">
                                    <label class="custom-file-label" for="customFile">Выберите файл...</label>
                                </div>
                            <? } else { ?>
                                <input type="<?= (isset($element->datepickers) && in_array($name, $element->datepickers)) ? "date" : "text" ?>" class="form-control" id="<?= $name ?>Id_<?= $rowId ?>" name="<?= $name ?>_<?= $element->name ?>" />
                            <? } ?>
                        </div>
                    <? } ?>
                </div>
                <div class="modal-footer">
                    <button <? if (isset($element->url) && $element->url) { ?>urlMode="true"<? } ?> element="<?= $element->name ?>" category="<?= $_GET['category'] ?>" component="<?= $component->name ?>" data="<?= $rowId ?>" type="button" class="modalButtonAction btn btn-primary" data-dismiss="modal">Отправить</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                </div>
            </div>
        </div>
    </div>
<? } ?>
