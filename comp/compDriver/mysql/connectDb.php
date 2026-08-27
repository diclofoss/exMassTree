<?php

if (!$config) {
    die("Ошибка подключения базы MySQL");
}

mysql::$dbConnect = new PDO("mysql:host={$config->host};dbname={$config->dbname};charset={$config->charset}", $config->user, $config->password);
mysql::$tablePrefix = $config->prefix;
mysql::$idName = $config->idName;
if (isset($config->parentIdName)) {
    mysql::$parentIdName = $config->parentIdName;
} else {
    mysql::$parentIdName = "parent_id";
}
if (!isset($config->idName)) {
    $config->idName = "id";
}

