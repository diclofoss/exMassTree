<?php

include_once("mysqlaccess.php");

class wpaccess extends mysqlaccess {

    public function addUser($config) {
        
    }

    public function updateUser($config) {
        $Tuser = $config->compDriver("a_user");
        $user = $Tuser->get($_GET['id']);
        if (!$user) {
            $config->errorMessage = "Логин не уже существует";
            return $code = 502;
        }
        $Tuser->updateFields($_GET['id'], array("group_id" => $_POST['group_id']));
        $config->redirect = $config->dirName . "/?admin&view=users";
        return $code = 200;
    }

    public function deleteUser($config) {
        $config->errorMessage = "Удаления пользователей осуществляется через Wordpress";
        $config->redirect = $config->dirName . "/?admin&view=users";
        return $code = 200;
    }

    public function getUser($config, $id) {
        $tblPreffix = $config->config->database->prefix;
        $idName = $config->config->database->idName;
        $TUser = $config->compDriver("a_user");
        $user = $TUser->getBySql("SELECT u.user_login login, u.display_name name, ut.* FROM `{$tblPreffix}users` u, `{$tblPreffix}a_user` ut WHERE u.ID = ut.user_id AND ut.$idName = $id");
        return $user;
    }

    public function getList($config) {
        $tblPreffix = $config->config->database->prefix;
        $Tuser = $config->compDriver("a_user");
        $TGroup = $config->compDriver("a_group");
        $usersList = $Tuser->getListBySql("SELECT u.user_login login, u.display_name name, ut.* FROM `{$tblPreffix}users` u, `{$tblPreffix}a_user` ut WHERE u.ID = ut.user_id");
        foreach ($usersList as $user) {
            $group = $TGroup->get($user->group_id);
            $user->group = $group;
        }
        return $usersList;
    }

    public function login($config) {
        if (!$_POST['login']) {
            return 502;
        }
        if (!$_POST['password']) {
            return 502;
        }
        $tblPreffix = $config->config->database->prefix;
        $Tuser = $config->compDriver("a_user");
        $user = $Tuser->getBySql("SELECT * FROM `{$tblPreffix}users` u, `{$tblPreffix}a_user` ut WHERE u.ID = ut.user_id AND u.user_login = '{$_POST['login']}'");
        if (!$user) {
            $config->errorMessage = "Логин не существует";
            return 402;
        }
        $result = wp_check_password($_POST['password'], $user->user_pass, $user->ID);
        if ($result) {
            $_SESSION['login'] = $user->user_login;
            $this->login = $user->user_login;
            $this->name = $user->display_name;
            $config->redirect = $config->dirName;
            return 200;
        }

        $config->errorMessage = "Логин или пароль неверны";
        return 402;
    }

    public function auth($config) {
        if (!isset($_SESSION['login'])) {
            return false;
        }
        if (!$_SESSION['login']) {
            return false;
        }
        $Tuser = $config->compDriver("a_user");
        $tblPreffix = $config->config->database->prefix;
        $user = $Tuser->getBySql("SELECT * FROM `{$tblPreffix}users` u, `{$tblPreffix}a_user` ut WHERE u.ID = ut.user_id AND u.user_login = '{$_SESSION['login']}'");
        if (!$user) {
            return false;
        }
        $this->login = $user->user_login;
        $this->user = $user->display_name;
        $TGroup = $config->compDriver("a_group");
        $group = $TGroup->get($user->group_id);
        $this->isAdmin = $group->isAdmin;
        $this->componentList = array();
        $TGroup = $config->compDriver("a_group");
        $TUser_group = $config->compDriver("a_user_group");
        $curGroupList = $TUser_group->getList($user->{$config->config->database->idName});
        foreach ($curGroupList as $curGroup) {
            $group = $TGroup->get($curGroup->group_id);
            $TGroup_component = $config->compDriver("a_group_component");
            $componentList = $TGroup_component->getList($group->{$config->config->database->idName});
            foreach ($componentList as $component) {
                $this->componentList[] = $component->componentName;
            }
        }
        $group = $TGroup->get($user->group_id);
        $TGroup_component = $config->compDriver("a_group_component");
        $componentList = $TGroup_component->getList($group->{$config->config->database->idName});
        foreach ($componentList as $component) {
            $this->componentList[] = $component->componentName;
        }
        $this->componentList = array_unique($this->componentList);
        if ($group->desctopUrl) {
            $this->defaultHome = $group->desctopUrl;
        } else {
            $this->defaultHome = $config->config->defaultHome;
        }
        return true;
    }

}
