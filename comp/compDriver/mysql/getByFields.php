<?php

$WHERE = '';

if ($fields) {
    foreach ($fields as $var => $val) {
        if ($WHERE) {
            $WHERE .= " AND ";
        }
        if (is_array($val)) {
            $WHERE .= " ( ";
            for ($i = 2; $i < count($val); $i++) {
                if ($WHERE && $i != 2) {
                    $WHERE .= " " . $val[1] . " ";
                }
                $WHERE .= " $var " . $val[0] . " '" . $val[$i] . "'";
            }
            $WHERE .= " ) ";
        } else {
            $WHERE .= " $var = '$val'";
        }
    }
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
    $compObjArray = $TcompDriver->getList($compObj->id);
    $paramName = $with . "List";
    $compObj->$paramName = $compObjArray;
}