<?php

$this->getEmpty();

$SET = "";
foreach ($_POST as $var => $val) {
    if (is_array($val)) {
        continue;
    }
    if (in_array($var, $fieldsExclude)) {
        continue;
    }
    if (isset($this->$var)) {
        if ($SET) {
            $SET .= ", ";
        }
        if ($val == "NOW()" || $val == "NULL") {
            $SET .= "$var = $val";
        } else {
            $val = str_replace("'", "\'", $val);
            $SET .= "$var = '$val'";
        }
    }
}
if ($fields) {
    foreach ($fields as $var => $val) {
        if (isset($this->$var)) {
            if ($SET) {
                $SET .= ", ";
            }
            if ($val == "NOW()" || $val == "NULL") {
                $SET .= "$var = $val";
            } else {
                $val = str_replace("'", "\'", $val);
                $SET .= "$var = '$val'";
            }
        }
    }
}

$WHERE = "";
if ($withFields) {
    foreach ($withFields as $var => $val) {
        $WHERE .= " AND $var = '$val'";
    }
}

$idName = mysql::$idName;

try {
    $dbQuery = mysql::$dbConnect->prepare("UPDATE " . $this->tblname . " SET $SET WHERE $idName = $id $WHERE");
    $dbQuery->execute();
} catch (PDOException $ex) {
    return;
}
if (!$dbQuery) {
    print (mysql::$dbConnect->error());
    return $code = 1;
}
return $code = 0;
