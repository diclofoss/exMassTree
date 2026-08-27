<?php

class mysqlaccess implements auth {

    var $login;
    var $name;
    var $isAdmin;
    var $componentList;
    var $otpEnabled;

    // Требуется ли обязательная настройка OTP (включается в config.json: auth.otp).
    public function otpRequired($config) {
        return !empty($config->config->auth->otp);
    }

    public function getUser($config, $id) {
        $TUser = $config->compDriver("a_user");
        return $TUser->get($id);
    }

    public function login($config) {
        // Шаг 2: пароль уже проверен, ждём одноразовый код
        if (isset($_POST['otp']) && isset($_SESSION['otp_pending_login'])) {
            return $this->loginOtpStep($config);
        }
        if (!$_POST['login']) {
            return 502;
        }
        if (!$_POST['password']) {
            return 502;
        }
        $Tuser = $config->compDriver("a_user");
        $user = $Tuser->getByFields(array("login" => $_POST['login']));
        if (!$user) {
            $config->errorMessage = "Логин не существует";
            return 402;
        }
        $password = '*' . strtoupper(sha1(sha1($_POST['login'] . $_POST['password'], true)));
        if ($user->password == $password) {
            if (!empty($config->config->auth->otp) && $user->otp_enabled && $user->otp_secret) {
                // Пароль верный, но вход завершится только после одноразового кода
                $_SESSION['otp_pending_login'] = $user->login;
                $_SESSION['otp_pending_time'] = time();
                $config->otpStep = true;
                return 402;
            }
            session_regenerate_id(true);
            $_SESSION['login'] = $user->login;
            $this->login = $user->login;
            $this->name = $user->name;
            $config->redirect = $config->dirName;
            return 200;
        }

        $config->errorMessage = "Логин или пароль неверны";
        return 402;
    }

    private function loginOtpStep($config) {
        $idName = $config->config->database->idName;
        if (time() - $_SESSION['otp_pending_time'] > 300) {
            unset($_SESSION['otp_pending_login'], $_SESSION['otp_pending_time']);
            $config->errorMessage = "Время ввода кода истекло, войдите заново";
            return 402;
        }
        $Tuser = $config->compDriver("a_user");
        $user = $Tuser->getByFields(array("login" => $_SESSION['otp_pending_login']));
        if (!$user || !$user->otp_enabled || !$user->otp_secret) {
            unset($_SESSION['otp_pending_login'], $_SESSION['otp_pending_time']);
            $config->errorMessage = "Сессия авторизации устарела, войдите заново";
            return 402;
        }
        $slot = totp::verify($user->otp_secret, $_POST['otp']);
        if ($slot === false || $slot <= $user->otp_last_slot) {
            // slot <= otp_last_slot — защита от повторного использования кода
            $config->otpStep = true;
            $config->errorMessage = "Неверный код";
            return 402;
        }
        $Tuser->updateFields($user->{$idName}, array("otp_last_slot" => $slot));
        unset($_SESSION['otp_pending_login'], $_SESSION['otp_pending_time']);
        session_regenerate_id(true);
        $_SESSION['login'] = $user->login;
        $this->login = $user->login;
        $this->name = $user->name;
        $config->redirect = $config->dirName;
        return 200;
    }

    public function auth($config) {
        if (!isset($_SESSION['login'])) {
            return false;
        }
        if (!$_SESSION['login']) {
            return false;
        }
        $Tuser = $config->compDriver("a_user");
        $user = $Tuser->getByFields(array("login" => $_SESSION['login']));
        if (!$user) {
            return false;
        }
        $this->login = $user->login;
        $this->user = $user->name;
        $this->otpEnabled = (isset($user->otp_enabled) && $user->otp_enabled && isset($user->otp_secret) && $user->otp_secret) ? true : false;
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

    public function getLogin() {
        return $this->login;
    }

    public function getName() {
        return $this->user;
    }

    public function isAuthed() {
        if ($this->login) {
            return true;
        }
        return false;
    }

    public function logout() {
        unset($_SESSION['login']);
    }

    public function isAdmin() {
        return $this->isAdmin;
    }

    public function getList($config) {
        $Tuser = $config->compDriver("a_user");
        $TGroup = $config->compDriver("a_group");
        $usersList = $Tuser->getList();
        foreach ($usersList as $user) {
            $group = $TGroup->get($user->group_id);
            $user->group = $group;
        }
        return $usersList;
    }

    public function allowChange() {
        return false;
    }

    public function addUser($config) {
        $Tuser = $config->compDriver("a_user");
        $user = $Tuser->getByFields(array("login" => $_POST['login']));
        if ($user) {
            $config->errorMessage = "Логин уже существует";
            return $code = 502;
        }
        $_POST['password'] = '*' . strtoupper(sha1(sha1($_POST['login'] . $_POST['password'], true)));
        $Tuser->createByPost();
        $config->redirect = $config->dirName . "/?admin&view=users";
        return $code = 200;
    }

    public function updateUser($config) {
        $Tuser = $config->compDriver("a_user");
        $user = $Tuser->get($_GET['id']);
        if (!$user) {
            $config->errorMessage = "Логин не уже существует";
            return $code = 502;
        }
        if (!isset($_POST['password']) || !$_POST['password']) {
            unset($_POST['password']);
        } else {
            $_POST['password'] = '*' . strtoupper(sha1(sha1($user->login . $_POST['password'], true)));
        }
        $found = true;
        $Tgroup = $config->compDriver("a_group");
        $group = $Tgroup->get($user->group_id);
        if ($group->isAdmin) {
            $found = false;
            $userList = $Tuser->getList();
            foreach ($userList as $curUser) {
                $curGroup = $Tgroup->get($curUser->group_id);
                if ($curGroup->isAdmin && $curUser->{$config->config->database->idName} != $user->{$config->config->database->idName}) {
                    $found = true;
                }
            }
        }
        if ($found) {
            $Tuser->updateByPost($_GET['id'], array(), array(), array("otp_secret", "otp_enabled", "otp_last_slot"));
            if (isset($_POST['resetOtp']) && $_POST['resetOtp']) {
                $Tuser->updateFields($_GET['id'], array("otp_secret" => "", "otp_enabled" => 0, "otp_last_slot" => 0));
            }
        }
        $config->redirect = $config->dirName . "/?admin&view=users";
        return $code = 200;
    }

    public function deleteUser($config) {
        $Tuser = $config->compDriver("a_user");
        $Tuser_group = $config->compDriver("a_user_group");
        $user = $Tuser->get($_GET['id']);
        if (!$user) {
            $config->errorMessage = "Логин не уже существует";
            return $code = 502;
        }
        $Tgroup = $config->compDriver("a_group");
        $group = $Tgroup->get($user->group_id);
        $found = true;
        if ($group->isAdmin) {
            $found = false;
            $userList = $Tuser->getList();
            foreach ($userList as $curUser) {
                $curGroup = $Tgroup->get($curUser->group_id);
                if ($curGroup->isAdmin && $curUser->{$config->config->database->idName} != $user->{$config->config->database->idName}) {
                    $found = true;
                }
            }
        }
        if ($found) {
            $Tuser->delete($_GET['id']);
            $Tuser_group->delete("", array($Tgroup->getParentIdName() => $_GET['id']));
        }
        $config->redirect = $config->dirName . "/?admin&view=users";
        return $code = 200;
    }

    public function addGroup($config) {
        $Tuser = $config->compDriver("a_user");
        $Tgroup = $config->compDriver("a_group");
        $group = $Tgroup->get($_POST['newgroup_id']);
        $Tuser_group = $config->compDriver("a_user_group");
        $user = $Tuser->get($_GET['id']);
        if (!$user) {
            $config->errorMessage = "Логин не уже существует";
            return $code = 502;
        }
        $Tuser_group->createByFields(array($Tgroup->getParentIdName() => $user->{$config->config->database->idName}, "group_id" => $group->{$config->config->database->idName}));
        $userId = $user->{$config->config->database->idName};
        $config->redirect = $config->dirName . "/?admin&view=users&id=$userId&action=edit";
        return $code = 200;
    }

    public function deleteGroup($config) {
        $Tuser_group = $config->compDriver("a_user_group");
        $Tuser = $config->compDriver("a_user");
        $Tgroup = $config->compDriver("a_group");
        $group = $Tgroup->get($_GET['group_id']);
        $user = $Tuser->get($_GET['id']);
        if (!$user) {
            $config->errorMessage = "Логин не уже существует";
            return $code = 502;
        }
        $Tuser_group->delete("", array($Tgroup->getParentIdName() => $user->{$config->config->database->idName}, "group_id" => $group->{$config->config->database->idName}));
        $userId = $user->{$config->config->database->idName};
        $config->redirect = $config->dirName . "/?admin&view=users&id=$userId&action=edit";
        return $code = 200;
    }

    public function otpAction($config) {
        $idName = $config->config->database->idName;
        $Tuser = $config->compDriver("a_user");
        $user = $Tuser->getByFields(array("login" => $this->login));
        if (!$user) {
            $config->errorMessage = "Пользователь не найден";
            return 502;
        }
        $action = $_POST['otpAction'];

        if ($action == "start") {
            $_SESSION['otp_setup_secret'] = totp::generateSecret();
            $config->redirect = $config->dirName . "/?personal";
            return 200;
        }

        if ($action == "cancel") {
            unset($_SESSION['otp_setup_secret']);
            $config->redirect = $config->dirName . "/?personal";
            return 200;
        }

        if ($action == "confirm") {
            if (!isset($_SESSION['otp_setup_secret'])) {
                $config->errorMessage = "Настройка не начата";
                return 502;
            }
            $slot = totp::verify($_SESSION['otp_setup_secret'], isset($_POST['otp']) ? $_POST['otp'] : "");
            if ($slot === false) {
                $config->errorMessage = "Неверный код. Проверьте, что аккаунт добавлен в приложение, и попробуйте ещё раз";
                return 402;
            }
            $Tuser->updateFields($user->{$idName}, array(
                "otp_secret" => $_SESSION['otp_setup_secret'],
                "otp_enabled" => 1,
                "otp_last_slot" => $slot,
            ));
            unset($_SESSION['otp_setup_secret']);
            $config->redirect = $config->dirName . "/?personal";
            return 200;
        }

        if ($action == "disable") {
            if (!$user->otp_enabled) {
                $config->redirect = $config->dirName . "/?personal";
                return 200;
            }
            $slot = totp::verify($user->otp_secret, isset($_POST['otp']) ? $_POST['otp'] : "");
            if ($slot === false) {
                $config->errorMessage = "Для отключения введите действующий код из приложения";
                return 402;
            }
            $Tuser->updateFields($user->{$idName}, array(
                "otp_secret" => "",
                "otp_enabled" => 0,
                "otp_last_slot" => 0,
            ));
            $config->redirect = $config->dirName . "/?personal";
            return 200;
        }

        return 502;
    }

    public function updatePersonal($config) {
        $Tuser = $config->compDriver("a_user");
        $user = $Tuser->getByFields(array("login" => $this->login));
        if (!$user) {
            $config->errorMessage = "Логин не уже существует";
            return $code = 502;
        }
        if (!isset($_POST['password']) || !$_POST['password']) {
            unset($_POST['password']);
        } else {
            $_POST['password'] = '*' . strtoupper(sha1(sha1($this->login . $_POST['password'], true)));
        }
        // otp-поля меняются только через otpAction с проверкой кода
        $Tuser->updateByPost($user->{$config->config->database->idName}, array(), array(), array("otp_secret", "otp_enabled", "otp_last_slot"));
        $config->redirect = $config->dirName . "/?personal";
        return $code = 200;
    }

}
