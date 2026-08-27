<?php

$view = "users";
if (isset($_GET['view']) && $_GET['view']) {
    $view = $_GET['view'];
}

ob_start();
include("templates/admin/left.php");
$renderLeft = ob_get_clean();
$renderData = "";

if ($view == "users") {
    ob_start();
    if (isset($_GET['action']) && $_GET['action'] == "add") {
        $TGroup = $this->compDriver("a_group");
        $groupList = $TGroup->getList();
        include("templates/admin/usersEdit.php");
    } else if (isset($_GET['action']) && $_GET['action'] == "edit") {
        $TUser = $this->compDriver("a_user");
        $user = $this->auth->getUser($this, $_GET['id']);
        $TUser_group = $this->compDriver("a_user_group");
        $TGroup = $this->compDriver("a_group");
        $groupList = $TGroup->getList();
        $curGroupList = $TUser_group->getList("", array($TGroup->getParentIdName() => $user->{$this->config->database->idName}));
        foreach ($curGroupList as $curGroup) {
            $curGroup->group = $TGroup->get($curGroup->group_id);
        }
        include("templates/admin/usersEdit.php");
    } else {
        $userList = $this->auth->getList($this);
        include("templates/admin/users.php");
    }
    $renderData = ob_get_clean();
}

if ($view == "groups") {
    ob_start();
    if (isset($_GET['action']) && $_GET['action'] == "add") {
        include("templates/admin/groupsEdit.php");
    } else if (isset($_GET['action']) && $_GET['action'] == "edit") {
        $TGroup = $this->compDriver("a_group");
        $group = $TGroup->get($_GET['id']);
        $TGroup_component = $this->compDriver("a_group_component");
        $componentNameList = $TGroup_component->getList($group->{$this->config->database->idName});
        $allComponents = array();
        foreach ($this->config->categories as $category) {
            foreach ($category->components as $component) {
                $allComponents[] = $component;
            }
        }
        $componentList = array();
        foreach ($allComponents as $component) {
            foreach ($componentNameList as $componentName) {
                if ($component->name == $componentName->componentName) {
                    $componentList[] = $component;
                }
            }
        }
        include("templates/admin/groupsEdit.php");
    } else {
        $TGroup = $this->compDriver("a_group");
        $groupList = $TGroup->getList();
        include("templates/admin/groups.php");
    }
    $renderData = ob_get_clean();
}

if ($view == "database") {
    include("admin/database.php");
    ob_start();
    include("templates/admin/database.php");
    $renderData = ob_get_clean();
}

if ($view == "databaseCleanup") {
    include("admin/databaseCleanup.php");
    ob_start();
    include("templates/admin/databaseCleanup.php");
    $renderData = ob_get_clean();
}

if ($view == "history") {
    $THistory = $this->compDriver("a_actionlog");
    $curpage = 1;
    $perPage = 30;
    if (isset($_SESSION['pages']['core']['history'])) {
        $curpage = $_SESSION['pages']['core']['history'];
    }
    $limit = ($curpage - 1) * $perPage;
    $limit = "$limit, $perPage";
    $totalObj = $THistory->getBySql("SELECT count(*) as total FROM {$THistory->tblname}");
    ob_start();
    application::renderPagePanel($totalObj->total, $curpage, $perPage, "#", "core", "history");
    $pagePanel = ob_get_clean();
    $historyList = $THistory->getListBySql("SELECT * FROM {$THistory->tblname} ORDER BY datetime DESC LIMIT $limit");
//    $historyList = $THistory->getList($id = "", $fields = array(), $withList = array(), $fieldsExclude = array(), $withObjects = array(), $orderByList = array("datetime DESC"), $limit = "100");
    ob_start();
    include("templates/admin/history.php");
    $renderData = ob_get_clean();
}


