<label for="<?= $element->name ?>DateId"><?= $element->caption ?></label>
<div class="form-group date">
    <div class="input-group">
        <? if (datetimeEl::isNull($element)) { ?>
            <? if (isset($data->{$element->name})) { ?>
                <? $date = date("Y-m-d H:i:s", strtotime($data->{$element->name})) ?>
                <? $time = date("H:i", strtotime($data->{$element->name})) ?>
                <input <?= (isset($element->readonly) && $element->readonly) ? "disabled=\"\"" : "" ?> type="text" name="<?= $element->name ?>" class="form-control daterangepickerEl" value="<?= $date ?>" id="<?= $element->name ?>DateId" />
            <? } else { ?>
                <input <?= (isset($element->readonly) && $element->readonly) ? "disabled=\"\"" : "" ?> type="text" name="<?= $element->name ?>" class="form-control daterangepickerEl" value="" id="<?= $element->name ?>DateId" />
            <? } ?>
        <? } else { ?>
            <? if (isset($data->{$element->name})) { ?>
                <? $date = date("Y-m-d H:i:s", strtotime($data->{$element->name})) ?>
                <? $time = date("H:i", strtotime($data->{$element->name})) ?>
                <input <?= (isset($element->readonly) && $element->readonly) ? "disabled=\"\"" : "" ?> type="text" name="<?= $element->name ?>" class="form-control daterangepickerEl" value="<?= $date ?>" id="<?= $element->name ?>DateId" />
            <? } else { ?>
                <input <?= (isset($element->readonly) && $element->readonly) ? "disabled=\"\"" : "" ?> type="text" name="<?= $element->name ?>" class="form-control daterangepickerEl" value="" id="<?= $element->name ?>DateId" />
            <? } ?>
        <? } ?>
        <span class="input-group-append">
            <button class="btn btn-outline-secondary" type="button">
                <span data-feather="calendar"></span>
            </button>
        </span>
    </div>
</div>
<? if (isset($element->readonly) && $element->readonly) { ?>
<? } ?>