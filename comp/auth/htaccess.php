<?php

class htaccess implements auth {

    var $login;

    public function getUser($config, $id) {
        return "";
    }

    public function login($config) {
        $login = $_POST['login'];
        $password = $_POST['password'];
        if (!$login) {
            return 502;
        }
        if (!$password) {
            return 502;
        }
        $file = file($config->config->auth->filePath);
        foreach ($file as $line) {
            list($rawLogin, $rawPass) = preg_split("/:/", $line);
            if ($rawLogin != $login) {
                continue;
            }
            $rawPass = trim($rawPass);
            if (!$this->matches($password, $rawPass)) {
                continue;
            }
            $_SESSION['login'] = $login;
            $this->login = $login;
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
        $this->login = $_SESSION['login'];
        return true;
    }

    public function getLogin() {
        return $this->login;
    }

    public function getName() {
        return $this->login;
    }

    private function matches($password, $filePasswd) {
        if (strpos($filePasswd, '$apr1') === 0) {
            // MD5
            $passParts = explode('$', $filePasswd);
            $salt = $passParts[2];
            $hashed = $this->crypt_apr1_md5($password, $salt);
            return $hashed == $filePasswd;
        } elseif (strpos($filePasswd, '{SHA}') === 0) {
            // SHA1
            $hashed = "{SHA}" . base64_encode(sha1($password, TRUE));
            return $hashed == $filePasswd;
        } elseif (strpos($filePasswd, '$2y$') === 0) {
            // Bcrypt
            return password_verify($password, $filePasswd);
        } else {
            // Crypt
            $salt = substr($filePasswd, 0, 2);
            $hashed = crypt($password, $salt);
            return $hashed == $filePasswd;
        }
        return false;
    }

    private function crypt_apr1_md5($plainpasswd, $salt) {
        $tmp = "";
        $len = strlen($plainpasswd);
        $text = $plainpasswd . '$apr1$' . $salt;
        $bin = pack("H32", md5($plainpasswd . $salt . $plainpasswd));
        for ($i = $len; $i > 0; $i -= 16) {
            $text .= substr($bin, 0, min(16, $i));
        }
        for ($i = $len; $i > 0; $i >>= 1) {
            $text .= ($i & 1) ? chr(0) : $plainpasswd[0];
        }
        $bin = pack("H32", md5($text));
        for ($i = 0; $i < 1000; $i++) {
            $new = ($i & 1) ? $plainpasswd : $bin;
            if ($i % 3)
                $new .= $salt;
            if ($i % 7)
                $new .= $plainpasswd;
            $new .= ($i & 1) ? $bin : $plainpasswd;
            $bin = pack("H32", md5($new));
        }
        for ($i = 0; $i < 5; $i++) {
            $k = $i + 6;
            $j = $i + 12;
            if ($j == 16)
                $j = 5;
            $tmp = $bin[$i] . $bin[$k] . $bin[$j] . $tmp;
        }
        $tmp = chr(0) . chr(0) . $bin[11] . $tmp;
        $tmp = strtr(strrev(substr(base64_encode($tmp), 2)), "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/", "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz");

        return "$" . "apr1" . "$" . $salt . "$" . $tmp;
    }

    /**
     * See: https://paragonie.com/blog/2015/07/how-safely-generate-random-strings-and-integers-in-php
     */
    public function salt() {
        try {
            $salt = strtr(base64_encode(random_bytes(6)), '+', '.');
        } catch (TypeError $e) {
            die('An unexpected error has occurred');
        } catch (Error $e) {
            die('An unexpected error has occurred');
        } catch (Exception $e) {
            die('Could not generate a random int. Is our OS secure?');
        }
        return $salt;
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
        return true;
    }

    public function getList($config) {
        $usersList = array();
        $file = file($config->config->auth->filePath);
        foreach ($file as $line) {
            $line = trim($line);
            if (!$line) {
                continue;
            }
            $obj = new stdClass();
            list($rawLogin, $rawPass) = preg_split("/:/", $line);
            $obj->login = $rawLogin;
            $obj->username = $rawLogin;
            $obj->group = 0;
            $usersList[] = $obj;
        }
        return $usersList;
    }

    public function allowChange() {
        return false;
    }

    public function addUser($config) {
        
    }

    public function updateUser($config) {
        
    }

    public function deleteUser($config) {
        
    }

    public function addGroup($config) {
        
    }

    public function deleteGroup($config) {
        
    }

    public function updatePersonal($config) {
        
    }

}
