<?php

$prefix = $this->config->database->prefix;
$resultSet = array();

foreach ($this->config->categories as $curCategory) {
    foreach ($curCategory->components as $component) {
        $component->validationResult = new stdClass();
        $component->validationResult->status = true;
        $component->validationResult->elements = array();
        foreach ($component->elements as $element) {
            if ($element->type == "fieldset") {
                continue;
            }
            eval("\$isTable = {$element->type}::isTable();");
            if (!$isTable) {
                continue;
            }
            $result = validateElement($this, $element, null);
            if (!$result->status) {
                $component->validationResult->status = false;
            }
            $component->validationResult->elements[] = $result;
        }
    }
}

function validateElement($application, $element, $parent) {
    $result = new stdClass();
    $result->elements = array();
    $result->elementName = $element->name;
    $result->status = true;
    $result->missedTable = false;
    if (!isset($element->table)) {
        return $result;
    }
    $TElement = $application->compDriver($element->table);
    $idName = $TElement::$idName;
    $parentIdName = $TElement->getParentIdName();
    $tablePrefix = $TElement::$tablePrefix;
    $tableData = $TElement->getListBySql("DESCRIBE `{$TElement->tblname}`");
    if (!$tableData) {
        $result->status = false;
        $result->missedTable = true;
    }
    if (isset($element->elements)) {
        if ($parent) {
            eval("\$isTable = {$element->type}::isTable();");
            if ($isTable && (!isset($element->notParent) || !$element->notParent)) {
                $curResult = new stdClass();
                $result->status = true;
                $curResult->status = true;
                $curResult->missedField = false;
                $curResult->missedFk = false;
                $curTableData = $TElement->getListBySql("DESCRIBE `{$tablePrefix}{$element->name}`");
                $found = false;
                foreach ($curTableData as $val) {
                    if ($val->Field == $parentIdName) {
                        $found = true;
                    }
                }
                if (!$found) {
                    $result->status = false;
                    $curResult->status = false;
                    $curResult->elementName = $parentIdName;
                    $curResult->missedField = true;
                    $curResult->parentElementName = $element->name;
                    $curResult->suggesstion = "ALTER TABLE `" . $tablePrefix . $element->name . "` ADD `" . $parentIdName . "` INT(11) NOT NULL;";
                }
                $fkTable = $tablePrefix.$parent->name;
                $fkData = $TElement->getListBySql("SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE 
REFERENCED_TABLE_SCHEMA = '{$application->config->database->dbname}' AND TABLE_NAME = '" . $TElement->tblname . "' AND column_name = '$parentIdName' AND REFERENCED_TABLE_NAME = '$fkTable'");
                if (!$fkData) {
                    $result->status = false;
                    $curResult->status = false;
                    $curResult->elementName = $parentIdName;
                    $curResult->parentElementName = $element->name;
                    $curResult->missedFk = true;
                    $keyName = "{$fkTable}_{$tablePrefix}{$element->name}_{$parentIdName}";
                    $keyName = md5($keyName).time();                    
                    $curResult->fkSuggesstion = "ALTER TABLE `" . $tablePrefix . $element->name . "` ADD KEY `KK_$keyName` ({$parentIdName}); ";
                    $curResult->fkSuggesstion .= "ALTER TABLE `" . $tablePrefix . $element->name . "` ADD CONSTRAINT `FK_$keyName` FOREIGN KEY ({$parentIdName}) REFERENCES `$fkTable` (`$idName`);";
                }
                $result->elements[] = $curResult;
            }
        }
        foreach ($element->elements as $curElement) {
            if ($curElement->type == "fieldset") {
                continue;
            }
            eval("\$isTable = {$curElement->type}::isTable();");
            if ($isTable) {
                $curResult = validateElement($application, $curElement, $element);
                if (!$curResult->status) {
                    $result->status = false;
                }
                $result->elements[] = $curResult;
                continue;
            }
            if (!$tableData) {
                continue;
            }
            $found = false;
            $curResult = new stdClass();
            $curResult->status = true;
            $curResult->elementName = $curElement->name;
            $curResult->missedField = false;
            $curResult->missedFk = false;
            $curResult->parentElementName = $element->name;
            eval("\$type = {$curElement->type}::getDataType(\$curElement);");
            if (!$type) {
                continue;
            }
            eval("\$isNull = {$curElement->type}::isNull(\$curElement);");
            foreach ($tableData as $val) {
                if ($curElement->name != $val->Field) {
                    continue;
                }
                $found = true;
                if ($isNull != ($val->Null == "YES")) {
                    $result->status = false;
                    $curResult->status = false;
                    if ($isNull) {
                        $curResult->descr = "Expected: Null = YES; Found: Null = NO";
                    } else {
                        $curResult->descr = "Expected: Null = NO; Found: Null = YES";
                    }
                }
                if ($type != $val->Type) {
                    $result->status = false;
                    $curResult->status = false;
                    $curResult->descr = "Expected: $type; Found: " . $val->Type;
                }
            }
            if (!$found) {
                $result->status = false;
                $curResult->status = false;
                $curResult->missedField = true;
                if ($isNull) {
                    $isNull = "NULL";
                } else {
                    $isNull = "NOT NULL";
                }
                $curResult->suggesstion = "ALTER TABLE `" . $tablePrefix . $element->name . "` ADD `" . $curResult->elementName . "` " . $type . " " . $isNull . ";";
            }
            if ($curElement->type == "refsel") {
                $fkTable = findFKElement($TElement, $curElement);
                // Полиморфный refsel (t_#ROW|…# / #REFSEL|…#) — одной FK-таблицы нет, не требовать.
                $selectSql = isset($curElement->select) ? (string) $curElement->select : '';
                $dynamicFk = ($fkTable === '' || strpos($fkTable, '#') !== false
                    || strpos($selectSql, '#ROW|') !== false
                    || strpos($selectSql, '#REFSEL|') !== false);
                if (!$dynamicFk) {
                    $fkData = $TElement->getListBySql("SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE 
REFERENCED_TABLE_SCHEMA = '{$application->config->database->dbname}' AND TABLE_NAME = '" . $TElement->tblname . "' AND column_name = '{$curElement->name}' AND REFERENCED_TABLE_NAME = '$fkTable'");
                    if (!$fkData) {
                        $result->status = false;
                        $curResult->status = false;
                        $curResult->missedFk = true;
                        $curResult->fkSuggesstion = "FK for table $fkTable / {$curElement->name} not found";
                        $keyName = "{$fkTable}_{$tablePrefix}{$element->name}_{$curResult->elementName}";
                        $keyName = md5($keyName).time();
                        $curResult->fkSuggesstion = "ALTER TABLE `" . $tablePrefix . $element->name . "` ADD KEY `KK_$keyName` ({$curResult->elementName}); ";
                        $curResult->fkSuggesstion .= "ALTER TABLE `" . $tablePrefix . $element->name . "` ADD CONSTRAINT `FK_$keyName` FOREIGN KEY ({$curResult->elementName}) REFERENCES `$fkTable` (`$idName`);";
                    }
                }
            }
            $result->elements[] = $curResult;
        }
    }
    return $result;
}

function findFKElement($TElement, $curElement) {
    if (!preg_match("/FROM ([^WHEERE]+)/", $curElement->select, $matches)) {
        return "";
    }
    $fromList = array();
    $fromRawList = preg_split("/,/", trim($matches[1]));
    foreach ($fromRawList as $fromRaw) {
        $fromRaw = trim($fromRaw);
        @list($table, $alias) = preg_split("/ /", $fromRaw);
        if (!$alias) {
            $alias = "0";
        }
        $fromList[$alias] = $table;
    }
    if (count($fromList) == 1) {
        foreach ($fromList as $fl) {
            return $fl;
        }
        return "";
    }
    if (!preg_match("/(\w+).\w+ ?= ?'#VALUE#'/", $curElement->select, $matches)) {
        return "";
    }
    if (!isset($fromList[$matches[1]])) {
        var_dump($curElement->select);
        var_dump($fromList);
        var_dump($matches[1]);
        die();
    }
    return $fromList[$matches[1]];
}
