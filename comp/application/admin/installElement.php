<?php

function installElement($TElement, $element, $parentElement = "") {
    if (!isset($element->table)) {
        return;
    }
    $idName = $TElement::$idName;
    $parentIdName = $TElement->getParentIdName();
    $sql_cols = "$idName int(11) auto_increment";
    if ($parentElement) {
        $sql_cols .= ", $parentIdName int(11) NOT NULL";
    }
    if (isset($element->sort) && $element->sort) {
        $sql_cols .= ", {$element->sort} int(11) NOT NULL";
    }
    foreach ($element->elements as $curElement) {
        if ($curElement->type == "fieldset") {
            continue;
        }
        eval("\$isTable = {$curElement->type}::isTable();");
        if ($isTable) {
            installElement($TElement, $curElement, $element);
            continue;
        }
        eval("\$dataType = {$curElement->type}::getDataType(\$curElement);");
        if (!$dataType) {
            continue;
        }
        eval("\$isNull = {$curElement->type}::isNull(\$curElement);");
        if ($isNull) {
            $isNull = "NULL";
        } else {
            $isNull = "NOT NULL";
        }
        if ($sql_cols) {
            $sql_cols .= ", ";
        }
        $sql_cols .= "`" . $curElement->name . "` $dataType $isNull";
    }
    $tablePrefix = $TElement::$tablePrefix;
    $sql = "CREATE TABLE IF NOT EXISTS `$tablePrefix" . $element->table . "` (
		$sql_cols
		, PRIMARY KEY  (`$idName`)
		);
	";
    $TElement->updateBySql($sql);
    eval("\$singleLine = {$element->type}::hasSingleLine();");
    if ($singleLine) {
        // Проверяем, есть ли уже данные в таблице
        $tableName = $tablePrefix . $element->table;
        $countSql = "SELECT COUNT(*) as cnt FROM `$tableName`";
        try {
            $countResult = mysql::$dbConnect->query($countSql);
            if ($countResult) {
                $countRow = $countResult->fetch(PDO::FETCH_ASSOC);
                if ($countRow && $countRow['cnt'] > 0) {
                    // В таблице уже есть данные, не вставляем дефолтную строку
                    return;
                }
            }
        } catch (Exception $e) {
            // Если не удалось проверить, продолжаем (таблица может быть пустой)
        }
        
        if ($parentElement) {
            $TElement->createByFields(array($idName => 1, $parentIdName => "1"));
        } else {
            $TElement->createByFields(array($idName => 1));
        }
    }
}
