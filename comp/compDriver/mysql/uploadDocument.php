<?php

$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
$fileFolder = "/" . $this->objName . "/" . date("Y-m-d") . "/";
if (!is_dir($DOCUMENT_ROOT . $fileFolder)) {
    @mkdir($DOCUMENT_ROOT . $fileFolder);
}

$utils = new utils();
if (!preg_match("/(.+)\.([^\.]+)$/", $file['name'], $matches)) {
    $fileExtention = "";
    $fileName = $this->translitIt($file['name']);
} else {
    $fileExtention = $matches[2];
    $fileName = $this->translitIt($matches[1]);
}
$salt = microtime(true);
$path = $fileFolder . $fileName . "_" . $salt . "." . $fileExtention;
move_uploaded_file($file['tmp_name'], $DOCUMENT_ROOT . $path);
