<?php

if (is_numeric($id)) {
    $WHERE = mysql::$idName . " = $id";
} else {
    $WHERE = "sysname = '$id'";
}

try {
    $dbQuery = mysql::$dbConnect->query("SELECT * FROM " . $this->tblname . " WHERE $WHERE");
} catch (PDOException $ex) {
    $compObj = "";
    return;
}
if (!$dbQuery) {
    $compObj = "";
    return;
}
$compObj = $dbQuery->fetchObject();
foreach ($withList as $with) {
    $TcompDriver = $this->compDriver($with);
    $compObjArray = $TcompDriver->getList($compObj->{mysql::$idName});
    $paramName = $with . "List";
    $compObj->$paramName = $compObjArray;
}

foreach ($withObjects as $field_id => $objectName) {
    $TcompDriver = $this->compDriver($objectName);
    $fieldName = preg_replace("/_id/", "", $field_id);
    $compObj->$fieldName = $TcompDriver->get($compObj->$field_id);
}