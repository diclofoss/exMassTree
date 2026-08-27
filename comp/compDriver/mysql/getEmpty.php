<?php

try {
    $dbQuery = mysql::$dbConnect->query("DESCRIBE " . $this->tblname);
} catch (PDOException $ex) {
    return;
}
if (!$dbQuery) {
    return;
}

while ($rows = $dbQuery->fetch()) {
    $paramName = $rows['Field'];
    $this->$paramName = "";
}
