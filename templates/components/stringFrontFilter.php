<div class="form-group mb-2">
    <label for="<?= $element->name ?>FrontFilter" class="sr-only"></label>
    <input type="text" class="form-control" name="<?= $element->name ?>FrontFilter" value="<?= (isset($_SESSION['filter'][$_GET['component']][$parentElement->name][$element->name])) ? $_SESSION['filter'][$_GET['component']][$parentElement->name][$element->name] : "" ?>" id="<?= $element->name ?>FrontFilter" placeholder="<?= $element->caption ?>" />
</div>
