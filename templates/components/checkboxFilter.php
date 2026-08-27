<?php
if (!isset($element->items)) {
    $element->items = array("Нет", "Да");
}

$componentName = isset($_GET['component']) ? $_GET['component'] : $component->name;
$selectedIndices = array();
$filterValues = null;

if (isset($parentElement->widgetId) && $parentElement->widgetId) {
    $widgetState = WidgetUtils::getWidgetState($parentElement->widgetId);
    if (isset($widgetState['filters'][$element->name])) {
        $filterValues = $widgetState['filters'][$element->name];
    }
} elseif (isset($_SESSION['filter'][$componentName][$parentElement->name][$element->name])) {
    $filterValues = $_SESSION['filter'][$componentName][$parentElement->name][$element->name];
}

if ($filterValues !== null) {
    foreach ((array) $filterValues as $filterValue) {
        if ($filterValue !== '' && $filterValue !== null) {
            $selectedIndices[] = (int) $filterValue;
        }
    }
}
?>
<div class="filter-data-source" style="display:none" data-filter-type="checkbox"
     data-element="<?= htmlspecialchars($element->name, ENT_QUOTES, 'UTF-8') ?>"
     data-component="<?= htmlspecialchars($componentName, ENT_QUOTES, 'UTF-8') ?>"
     data-parent-element="<?= htmlspecialchars($parentElement->name, ENT_QUOTES, 'UTF-8') ?>"
     data-options="<?= htmlspecialchars(json_encode(array_values($element->items), JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"
     data-selected="<?= htmlspecialchars(json_encode($selectedIndices), ENT_QUOTES, 'UTF-8') ?>"></div>
<script>
    if (component === undefined) {
        var component = "<?= $componentName ?>";
    }
    if (selectData === undefined) {
        var selectData = new Array();
    }
    if (selectedData === undefined) {
        var selectedData = new Array();
    }
    selectData['<?= $element->name ?>'] = new Array();
    selectedData['<?= $element->name ?>'] = new Array();
<?php $i = 0; ?>
<?php foreach ($element->items as $item) { ?>
    <?php if (in_array($i, $selectedIndices, true)) { ?>
            selectedData['<?= $element->name ?>'][<?= $i ?>] = "selected";
    <?php } else { ?>
            selectedData['<?= $element->name ?>'][<?= $i ?>] = "";
    <?php } ?>
        selectData['<?= $element->name ?>'][<?= $i ?>] = "<?= $item ?>";
    <?php $i++; ?>
<?php } ?>
</script>
