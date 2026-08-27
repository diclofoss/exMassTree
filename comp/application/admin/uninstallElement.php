<?php

function uninstallElement($TElement, $element) {
    $idName = $TElement::$idName;
    $parentIdName = $TElement->getParentIdName();
    foreach ($element->elements as $curElement) {
        if ($curElement->type == "fieldset") {
            continue;
        }
        eval("\$isTable = {$curElement->type}::isTable();");
        if ($isTable) {
            uninstallElement($TElement, $curElement);
            continue;
        }
    }
    $tablePrefix = $TElement::$tablePrefix;
    $sql = "DROP TABLE IF EXISTS `$tablePrefix" . $element->table . "`";
    $TElement->updateBySql($sql);
}
