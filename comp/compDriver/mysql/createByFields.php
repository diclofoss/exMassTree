<?php

$varSql = "";
$valSql = "";
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

$sql = "INSERT INTO " . $this->tblname . " ($varSql) VALUES ($valSql)";

try {
    $dbQuery = mysql::$dbConnect->prepare($sql);
} catch (PDOException $ex) {
    error_log("createByFields prepare error: " . $ex->getMessage() . " | SQL: " . $sql);
    return;
}

try {
    $dbQuery->execute();
} catch (PDOException $ex) {
    $errorMsg = "createByFields execute error: " . $ex->getMessage() . "\n";
    $errorMsg .= "SQL: " . $sql . "\n";
    $errorMsg .= "Table: " . $this->tblname . "\n";
    $errorMsg .= "Fields: " . print_r($fields, true);
    error_log($errorMsg);
    echo "<pre>ERROR in createByFields.php:\n" . htmlspecialchars($errorMsg) . "</pre>";
    throw $ex;
}
if (!$dbQuery) {
    return;
}

$dbQuery = mysql::$dbConnect->query("SELECT LAST_INSERT_ID()");
if (!$dbQuery) {
    return;
}

$row = $dbQuery->fetch();

$id = $row[0];
