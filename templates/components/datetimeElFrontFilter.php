<div class="input-group mb-2">
    <? if (isset($_SESSION['filter'][$_GET['component']][$parentElement->name][$element->name])) { ?>
        <? $date = $_SESSION['filter'][$_GET['component']][$parentElement->name][$element->name] ?>
    <? } else { ?>
        <? $date = date("Y-m-d", mktime(date("H"), date("i"), date("s"), date("m"), date("d") - 2, date("Y"))) . " - " . date("Y-m-d", mktime(date("H"), date("i"), date("s"), date("m"), date("d") - 1, date("Y"))) ?>
    <? } ?>
    <span class="input-group-prepend">
        <button class="btn btn-outline-secondary" type="button">
            <?= $element->caption ?>
        </button>
    </span>
    <input type="text" name="<?= $element->name ?>FrontFilter" class="form-control daterangepickerFrontFilterEl" value="<?= $date ?>" id="<?= $element->name ?>FrontFilter" />
</div>
