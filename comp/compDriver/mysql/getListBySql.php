<?php

$compObjArray = array();

try {
    $stmt = mysql::$dbConnect->query($sql);
} catch (PDOException $ex) {
    return;
}

if (!$stmt) {
    return;
}
while ($rows = $stmt->fetch()) {
    $compObj = $this->compDriver($this->objName);
    foreach ($rows as $var => $val) {
        if (is_numeric($var)) {
            continue;
        }
        $compObj->$var = $val;
    }
    $cubCompObjArray = array();
    foreach ($withList as $with) {
        $TcompDriver = new compDriver($with);
        $cubCompObjArray = $TcompDriver->getList($compObj->n_id);
        $paramName = $with . "List";
        $compObj->$paramName = $cubCompObjArray;
    }
    foreach ($withObjects as $field_id => $objectName) {
        $TcompDriver = new compDriver($objectName);
        $fieldName = preg_replace("/_id/", "", $field_id);
        $compObj->$fieldName = $TcompDriver->get($compObj->$field_id);
    }

    $compObjArray[] = $compObj;
}