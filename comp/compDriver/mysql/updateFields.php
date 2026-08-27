<?php

$idName = mysql::$idName;

$SET = '';
foreach ($fields as $field => $value) {
    if ($SET) {
        $SET .= ", ";
    }
    if ($value == "NOW()" || $value == "NULL") {
        $SET .= "$field = $value";
    } else {
        $SET .= "$field = '$value'";
    }
}
$WHERE = "";
if (is_array($id)) {
    foreach ($id as $field => $value) {
        if ($WHERE) {
            $WHERE .= " AND ";
        }
        $WHERE = "$field = '$value'";
    }
} else {
    $WHERE = "$idName = $id";
}

try {
    $dbQuery = mysql::$dbConnect->prepare("UPDATE " . $this->tblname . " SET $SET WHERE $WHERE");
    $dbQuery->execute();
} catch (PDOException $ex) {
    return;
}
if (!$dbQuery) {
    print (mysql::$dbConnect->error());
    return $code = 1;
}
return $code = 0;
