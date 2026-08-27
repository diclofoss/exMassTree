<?php

$resultSet = array();


$TElement = $this->compDriver("all");
$tbldata = $TElement->getListBySql("SHOW TABLES");

$idName = $TElement::$idName;
$dbName = $this->config->database->dbname;
$parentIdName = $TElement->getParentIdName();
$tablePrefix = $TElement::$tablePrefix;
$result = array();
$i = 0;
foreach ($tbldata as $tblname) {
    $tblname->Tables_in = $tblname->{"Tables_in_$dbName"};
//    if ($tblname->Tables_in != "t_gallery") {
//        continue;
//    }
    if ($tblname->Tables_in == "sqlmapfile") {
        continue;
    }
    if (preg_match("/{$tablePrefix}a_/", $tblname->Tables_in)) {
        continue;
    }
    $curresult = new stdClass();
    $curresult->table = $tblname->Tables_in;
    $curresult->fields = array();
    $curresult->status = true;
    $curresult->tableFound = false;
    $tableData = $TElement->getListBySql("DESCRIBE `{$tblname->Tables_in}`");
    foreach ($tableData as $field) {
        if ($field->Field == $idName) {
            continue;
        }
        if ($field->Field == "sort") {
            continue;
        }
        if ($field->Field == $parentIdName) {
            continue;
        }
        $found = false;
        foreach ($this->config->categories as $curCategory) {
            foreach ($curCategory->components as $component) {
                foreach ($component->elements as $element) {
                    if (validateElement($tblname->Tables_in, $field->Field, $this, $element, null)) {
                        $found = true;
//                        var_dump($found);
                        break;
                    }
                }
                if ($found) {
                    break;
                }
            }
            if ($found) {
                break;
            }
        }
//        var_dump("= " . $found . " " . $tblname->Tables_in . " " . $field->Field);
        if (!$found) {
            $curresult->status = false;
        } else {
            $curresult->tableFound = true;
        }
        $cfield = new stdClass();
        $cfield->field = $field->Field;
        $cfield->preDrop = "";
        if (!$found) {
            $constraintData = $TElement->getBySql("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_SCHEMA = '{$this->config->database->dbname}' AND TABLE_NAME = '{$curresult->table}' AND column_name = '{$cfield->field}'");
            if ($constraintData) {
                $cfield->preDrop = "ALTER TABLE {$curresult->table} DROP FOREIGN KEY {$constraintData->CONSTRAINT_NAME};";
            }
        }
        $cfield->status = $found;
        $curresult->fields[] = $cfield;
    }
    $result[] = $curresult;

    $i++;
    if ($i > 10) {
//        break;
    }
//    var_dump($tblname->Tables_in);
//    die();
}
//var_dump($tbldata);
//die();

//SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_SCHEMA = '{$application->config->database->dbname}' AND TABLE_NAME = '" . $TElement->tblname . "' AND column_name = '$parentIdName' AND REFERENCED_TABLE_NAME = '$fkTable'
//die();

function validateElement($tblname, $field, $application, $element) {
    $prefix = $application->config->database->prefix;
    if (isset($element->elements)) {
        foreach ($element->elements as $curElement) {
            if ($curElement->type == "fieldset") {
                continue;
            }
            eval("\$isTable = {$curElement->type}::isTable();");
            if ($isTable) {
                if (validateElement($tblname, $field, $application, $curElement)) {
                    return true;
                }
                continue;
            }
            if ($tblname != $prefix . @$element->table) {
                continue;
            }
//            var_dump($tblname . " " . $curElement->name . " " . $field . " " . $curElement->name);
            if ($curElement->name == $field) {
                return true;
            }
        }
    }
    return false;
}
