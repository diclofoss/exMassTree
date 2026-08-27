<label for="<?= $element->name ?>Id"><?= $element->caption ?></label>
<input type="hidden" name="picture_width_<?= $element->name ?>" value="<?= $element->width ?>" />
<input type="hidden" name="picture_height_<?= $element->name ?>" value="<?= $element->height ?>" />
<input type="hidden" name="picture_mask_<?= $element->name ?>" value="<?= $element->mask ?>" />
<? if (!$element->dependPicture) { ?>
    <div class="row">
        <? if (!isset($element->readonly) || !$element->readonly) { ?>
            <div class="col-md-6">
                <div class="custom-file mb-3">
                    <input type="file" name="picture_upload_<?= $element->name ?>" class="custom-file-input" id="<?= $element->name ?>Id">
                    <label class="custom-file-label" for="customFile">Выберите файл...</label>
                </div>
            </div>
        <? } ?>
        <? if (!$element->dependPicture && isset($data->{$element->name}) && $data->{$element->name}) { ?>
            <div class="col-md-6">
                <input type="button" class="btn btn-danger pictureDeleteBtn" data="<?= $element->name ?>" name="picture_deleteBtn_<?= $element->name ?>" value="удалить" />
            </div>
        <? } ?>
    </div>
<? } ?>
<? if ($element->width && $element->height && isset($data->{$element->name}) && $data->{$element->name} && $element->dependPicture) { ?>
    <div class="mb-3">
        <input data-target="#picture_resizeModal_<?= $element->name ?>" data-toggle="modal" type="button" class="btn btn-warning pictureChangeSize" dependPicture="<?= $element->dependPicture ?>" data="<?= $element->name ?>" name="picture_changeSize_<?= $element->name ?>" value="изменить размер" />
        <input type="hidden" name="picture_doResize_<?= $element->name ?>" value="" />
        <input type="hidden" name="picture_x1_<?= $element->name ?>" value="" />
        <input type="hidden" name="picture_y1_<?= $element->name ?>" value="" />
        <input type="hidden" name="picture_x2_<?= $element->name ?>" value="" />
        <input type="hidden" name="picture_y2_<?= $element->name ?>" value="" />
    </div>
<? } ?>    
<? if (isset($data->{$element->name}) && $data->{$element->name}) { ?>
    <input type="hidden" name="picture_delete_<?= $element->name ?>" value="" />
    <input type="hidden" name="<?= $element->name ?>" value="<?= htmlentities($data->{$element->name}, ENT_QUOTES, "utf-8") ?>">
    <div>
        <img id="picture_<?= $element->name; ?>" class="picture_<?= $element->name; ?>" src="<?= $data->{$element->name} ?>?<?= time() ?>" alt="<?= $element->caption ?>" />
    </div>
<? } else { ?>
    <? #if ($element->width && $element->height) { ?>
        <div>
            <img id="picture__dummy_<?= $element->name; ?>" src="https://dummyimage.com/<?= $element->width ?>x<?= $element->height ?>" alt="" />
        </div>    
        <input type="hidden" name="newPicture" value="true" />
        <input type="hidden" name="<?= $element->name ?>" value="<?= "/" . $component->name . "/" . date("Y-m-d") . "/" . time() . rand(0, 1000) ?>">
    <?#}?>
<? } ?>
<script>
<? if ($element->dependedPictures) { ?>
        if (dependedPictures == undefined) {
            var dependedPictures = new Array();
        }
        dependedPictures["<?= $element->name ?>"] = new Array();
    <? foreach ($element->dependedPictures as $depenedPicture) { ?>
            dependedPictures["<?= $element->name ?>"].push("<?= $depenedPicture ?>");
    <? } ?>
<? } ?>
</script>

<? if ($element->width && $element->height && isset($data->{$element->name}) && $element->dependPicture) { ?>
    <div data="<?= $element->name ?>" data-width="<?= $element->width ?>" data-height="<?= $element->height ?>" class="modal fade pictureChangeSizeModal" id="picture_resizeModal_<?= $element->name ?>" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Изменение размера</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-center">
                        <div class="img-container">
                            <img id="resizePicture_<?= $element->name ?>" src="<?= $data->{$element->dependPicture} ?>?<?= time() ?>" alt="Picture">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal">Применить</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                </div>                
            </div>
        </div>
    </div>
<? } ?>