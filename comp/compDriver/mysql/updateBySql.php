<?php

try {
    $stmt = mysql::$dbConnect->query($sql);
} catch (PDOException $ex) {
    return;
}
if (!$stmt) {
    return false;
}
return true;
