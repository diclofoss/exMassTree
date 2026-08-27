<?php

$dir = 'comp/auth/';
$files = scandir($dir);
foreach ($files as $file) {
    if ($file == ".") {
        continue;
    }
    if ($file == "..") {
        continue;
    }
    $dir = implode(DIRECTORY_SEPARATOR, array(dirname(__FILE__), 'components', $file));
    if (is_dir($dir)) {
        continue;
    }
    include_once 'auth/' . $file;
}

interface auth {

    function login($config);

    function getList($config);

    function getUser($config, $id);

    function logout();

    function getName();

    function auth($config);

    function isAuthed();

    function getLogin();

    function allowChange();

    function isAdmin();

    function addUser($config);

    function updateUser($config);

    function deleteUser($config);

    function addGroup($config);

    function deleteGroup($config);

    function updatePersonal($config);
}
