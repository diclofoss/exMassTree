<?php

$action = $_GET['action'];
if ($action == "edit") {
    $TGroup = $this->compDriver("a_group");
    if (!isset($_POST['isAdmin'])) {
        $_POST['isAdmin'] = 0;
    }

    if (isset($_POST['newComponent'])) {
        if ($_POST['component_id']) {
            $TGroup_component = $this->compDriver("a_group_component");
            $TGroup_component->createByFields(array($TGroup_component->getParentIdName() => $_GET['id'], "componentName" => $_POST['component_id']));
        }
        $this->redirect = $this->dirName . "/?admin&view=groups&action=edit&id={$_GET['id']}";
        return $code = 200;
    }
    $found = true;
    if ($_POST['isAdmin'] == 0) {
        $groupList = $TGroup->getList("", array("isAdmin" => 1));
        $found = false;
        foreach ($groupList as $group) {
            if ($group->id != $_GET['id']) {
                $found = true;
            }
        }
    }
    if (!$found) {
        $_POST['isAdmin'] = 1;
    }
    $TGroup->updateByPost($_GET['id']);
    $this->redirect = $this->dirName . "/?admin&view=groups";
    return $code = 200;
}

if ($action == "delete" && isset($_GET['component_id']) && $_GET['component_id']) {
    $TGroup_component = $this->compDriver("a_group_component");
    $TGroup_component->delete("", array($TGroup_component->getParentIdName() => $_GET['id'], "componentName" => $_GET['component_id']));
    $this->redirect = $this->dirName . "/?admin&view=groups&action=edit&id={$_GET['id']}";
    return $code = 200;
} else if ($action == "delete") {
    $TGroup_component = $this->compDriver("a_group_component");
    $TGroup = $this->compDriver("a_group");
    $group = $TGroup->get($_GET['id']);
    $found = true;
    if ($group->isAdmin == 1) {
        $groupList = $TGroup->getList("", array("isAdmin" => 1));
        $found = false;
        foreach ($groupList as $group) {
            if ($group->id != $_GET['id']) {
                $found = true;
            }
        }
    }
    if (!$found) {
        $this->redirect = $this->dirName . "/?admin&view=groups";
        return $code = 200;
    }

    $TGroup_component->delete("", array($TGroup_component->getParentIdName() => $_GET['id']));
    $TGroup->delete($_GET['id']);
    $this->redirect = $this->dirName . "/?admin&view=groups";
    return $code = 200;
}
if ($action == "add") {
    $TGroup = $this->compDriver("a_group");
    if (!isset($_POST['isAdmin'])) {
        $_POST['isAdmin'] = 0;
    }
    $TGroup->createByPost();
    $this->redirect = $this->dirName . "/?admin&view=groups";
    return $code = 200;
}