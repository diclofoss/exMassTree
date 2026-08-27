<?php

function fixElement($application, $TElement, $element) {
    if (!isset($element->table)) {
        return;
    }
    $tableData = $TElement->getListBySql("DESCRIBE `{$TElement->tblname}`");
//    var_dump("DESCRIBE `{$TElement->tblname}`");
    if (!$tableData) {
        return;
    }
//    var_dump($tableData);
    if (isset($element->elements)) {
        foreach ($element->elements as $curElement) {
            if ($curElement->type == "fieldset") {
                continue;
            }
            eval("\$isTable = {$curElement->type}::isTable();");
            if ($isTable) {
                $TCurElement = $application->compDriver($curElement->table);
                fixElement($application, $TCurElement, $curElement);
                continue;
            }
            if (!$tableData) {
                continue;
            }
            $found = false;
            eval("\$type = {$curElement->type}::getDataType(\$curElement);");
            if (!$type) {
                continue;
            }
            eval("\$isNull = {$curElement->type}::isNull(\$curElement);");
            foreach ($tableData as $val) {
//                if ($element->name == "firmorg_upload") {
//                    var_dump($element->name . "  " . $curElement->name . " " . $val->Field);
//                }
                if ($curElement->name != $val->Field) {
                    continue;
                }
                $found = true;
                if ($isNull != ($val->Null == "YES")) {
//                    if ($isNull) {
//                        $curResult->descr = "Expected: Null = YES; Found: Null = NO";
//                    } else {
//                        $curResult->descr = "Expected: Null = NO; Found: Null = YES";
//                    }
                }
                if ($type != $val->Type) {
//                    $curResult->status = false;
//                    $curResult->descr = "Expected: $type; Found: " . $val->Type;
                }
            }
            if (!$found) {
//                var_dump($element->name . "  " . $curElement->name);
                if ($isNull) {
                    $isNull = "NULL";
                } else {
                    $isNull = "NOT NULL";
                }
                $tablePrefix = $TElement::$tablePrefix;
                $sql = "ALTER TABLE `" . $tablePrefix . $element->name . "` ADD `" . $curElement->name . "` " . $type . " " . $isNull . ";";
                if (!$TElement->updateBySql($sql)) {
//                    var_dump($sql);
//                    die();
                }
            }
        }
    }
}
