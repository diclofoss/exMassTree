<?php

if (!$sql) {
    return array();
}
try {
    $stmt = mysql::$dbConnect->query($sql);
} catch (PDOException $ex) {
    return $row = null;
}
if (!$stmt) {
    return $row = null;
}
$row = $stmt->fetchObject();
return $row;
