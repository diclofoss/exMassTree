<?php

$this->getEmpty();

// Если parent ID установлен в POST или GET, устанавливаем его в объект
if (mysql::$parentIdName) {
    $parentIdName = mysql::$parentIdName;
    $parentIdValue = null;
    if (isset($_POST[$parentIdName]) && $_POST[$parentIdName]) {
        $parentIdValue = $_POST[$parentIdName];
    } elseif (isset($_GET['parent_id']) && $_GET['parent_id']) {
        $parentIdValue = $_GET['parent_id'];
        $_POST[$parentIdName] = $parentIdValue;
    } elseif (isset($_GET['id']) && $_GET['id']) {
        $parentIdValue = $_GET['id'];
        $_POST[$parentIdName] = $parentIdValue;
    }
    if ($parentIdValue && isset($this->$parentIdName)) {
        $this->$parentIdName = $parentIdValue;
    }
}

$varSql = "";
$valSql = "";
$fieldNeedle = array();
if ($fields) {
    foreach ($fields as $var => $val) {
        $fieldNeedle[] = $var;
    }
}
foreach ($_POST as $var => $val) {
    if (is_array($val)) {
        continue;
    }
    if (isset($this->$var) && !in_array($var, $fieldNeedle)) {
        // Используем значение из объекта, если оно установлено, иначе из POST
        $valueToUse = (!empty($this->$var)) ? $this->$var : $val;
        if ($varSql) {
            $varSql .= ", ";
        }
        $varSql .= $var;
        if ($valSql) {
            $valSql .= ", ";
        }
        if ($valueToUse == "NOW()" || $valueToUse == "NULL") {
            $valSql .= "$valueToUse";
        } else {
            $valSql .= "'$valueToUse'";
        }
    }
}
if ($fields) {
    foreach ($fields as $var => $val) {
        if ($varSql) {
            $varSql .= ", ";
        }
        $varSql .= $var;
        if ($valSql) {
            $valSql .= ", ";
        }
        if ($val == "NOW()" || $val == "NULL") {
            $valSql .= "$val";
        } else {
            $valSql .= "'$val'";
        }
    }
}

$id = null;
try {
    $dbQuery = mysql::$dbConnect->prepare("INSERT INTO " . $this->tblname . " ($varSql) VALUES ($valSql)");
    $dbQuery->execute();
} catch (PDOException $ex) {
    print("INSERT INTO " . $this->tblname . " ($varSql) VALUES ($valSql)");
    print $ex->getMessage();
    return $id;
}
if (!$dbQuery) {
    print("INSERT INTO " . $this->tblname . " ($varSql) VALUES ($valSql)");
    $errorInfo = mysql::$dbConnect->errorInfo();
    print $errorInfo[2]; // Сообщение об ошибке
    return $id;
}

$dbQuery = mysql::$dbConnect->query("SELECT LAST_INSERT_ID()");
if (!$dbQuery) {
    return $id;
}

$row = $dbQuery->fetch();

$id = $row[0];
