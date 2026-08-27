<script>
    if (component === undefined) {
        var component = "<?= $_GET['component'] ?>";
    }
    if (selectData === undefined) {
        var selectData = new Array();
    }
    if (selectedData === undefined) {
        var selectedData = new Array();
    }
    selectData['<?= $curElement->name ?>'] = new Array();
    selectedData['<?= $curElement->name ?>'] = new Array();
<? $i = 0; ?>
<? foreach ($curElement->items as $item) { ?>
    <? if (isset($_SESSION['filter'][$_GET['component']][$element->name][$curElement->name]) && in_array($i, $_SESSION['filter'][$_GET['component']][$element->name][$curElement->name])) { ?>
            selectedData['<?= $curElement->name ?>'][<?= $i ?>] = "selected";
    <? } else { ?>
            selectedData['<?= $curElement->name ?>'][<?= $i ?>] = "";
    <? } ?>
        selectData['<?= $curElement->name ?>'][<?= $i ?>] = "<?= $item ?>";
    <? $i++; ?>
<? } ?>
</script>