<?php

if ($_GET['view'] == "database") {
    include("admin/databaseSubmit.php");
    return $code;
}

if ($_GET['view'] == "groups") {
    include("admin/groupsSubmit.php");
    return $code;
}

if ($_GET['view'] == "users") {
    include("admin/usersSubmit.php");
    return $code;
}