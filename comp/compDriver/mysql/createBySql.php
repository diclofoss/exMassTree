<?php

try {
    $stmt = mysql::$dbConnect->query($sql);
} catch (PDOException $ex) {
    return;
}
if (!$stmt) {
    return 0;
}

$dbQuery = mysql::$dbConnect->query("SELECT LAST_INSERT_ID()");
if (!$dbQuery) {
    return;
}

$row = $dbQuery->fetch();

$id = $row[0];

return $id;
