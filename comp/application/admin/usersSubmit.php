<?php

if ($_GET['action'] == "add") {
    $code = $this->auth->addUser($this);
} else if ($_GET['action'] == "edit") {
    if (isset($_POST['addGroup'])) {
        $code = $this->auth->addGroup($this);
        return;
    }
    $code = $this->auth->updateUser($this);
} else if ($_GET['action'] == "delete") {
    if (isset($_GET['group_id'])) {
        $code = $this->auth->deleteGroup($this);
        return;
    }
    $code = $this->auth->deleteUser($this);
}