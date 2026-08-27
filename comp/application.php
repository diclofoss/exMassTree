<?php

require_once './vendor/autoload.php';

include_once 'totp.php';
include_once 'auth.php';
include_once 'compDriver.php';
include_once 'components.php';
include_once 'utils.php';
include_once 'img.php';
include_once 'smtpmailer.php';

class application {

    var $auth;
    var $config;
    var $dirName;
    var $data;
    var $errorMessage;
    var $status;
    var $path;
    var $redirect;
    var $otpStep;

    public function __construct($fileName, $config) {
        $this->status = 200;
        $file = file_get_contents($fileName);
        $this->config = $this->readConf(json_decode($file));
        if ($config) {
            include($config);
        }
        eval("\$this->auth = new {$this->config->auth->type}();");
        eval("{$this->config->database->driver}::connectDb(\$this->config->database);");
        $this->dirName = dirname($_SERVER['SCRIPT_NAME']);
        if ($this->dirName == "\\" || $this->dirName == "/") {
            $this->dirName = "";
        }
        $this->path = $this->calculatePath();
    }

    private function readConf($conf) {
        foreach ($conf->categories as $category) {
            foreach ($category->components as $component) {
                for ($i = 0; $i < count($component->elements); $i++) {
                    $component->elements[$i] = $this->readElement($component->elements[$i]);
                }
            }
        }
        return $conf;
    }

    private function readElement($element) {
        if (!isset($element->elements)) {
            return $element;
        }
        if (!is_array($element->elements)) {
            die(var_dump($element->elements));
        }
        $additionalElements = array();
        for ($i = 0; $i < count($element->elements); $i++) {
            if (!isset($element->elements[$i]->type)) {
                var_dump($element->elements[$i]);
                die();
            }
            if ($element->elements[$i]->type == "fieldset") {
                for ($i1 = 0; $i1 < count($element->elements[$i]->elements); $i1++) {
                    $element->elements[$i]->elements[$i1]->fieldset = $element->elements[$i]->name;
                    $element->elements[$i]->elements[$i1]->fieldsetCol = $element->elements[$i]->col;
                    $additionalElements[] = $this->readElement($element->elements[$i]->elements[$i1]);
                }
                continue;
            }
            $element->elements[$i] = $this->readElement($element->elements[$i]);
        }
        $element->elements = array_merge($additionalElements, $element->elements);
        return $element;
    }

    public function compDriver($objName) {
        eval("\$compDriver = new {$this->config->database->driver}(\$objName);");
        return $compDriver;
    }

    public function onSubmit() {
        include "application/onSubmit.php";
    }

    public function showLogin() {
        global $EXMASSTREE_VERSION;
        include "templates/root/login.php";
    }

    public function showPersonal() {
        global $EXMASSTREE_VERSION;
        include "application/personal.php";
        include "templates/root/index.php";
    }

    public function showAdmin() {
        global $EXMASSTREE_VERSION;
        include "application/admin.php";
        include "templates/root/index.php";
    }

    public function prepareData() {
        include "application/prepareData.php";
    }

    public function onSubmitAdmin() {
        include "application/onSubmitAdmin.php";
        return $code;
    }

    public function onSubmitPersonal() {
        if (isset($_POST['otpAction']) && !empty($this->config->auth->otp) && method_exists($this->auth, 'otpAction')) {
            return $this->auth->otpAction($this);
        }
        $code = $this->auth->updatePersonal($this);
        return $code;
    }

    public function render() {
        global $EXMASSTREE_VERSION;
        include "application/render.php";
        include "templates/root/index.php";
    }

    public function calculatePath() {
        include "application/calculatePath.php";
        return $path;
    }

    public static function renderPagePanel($total, $curpage, $limit, $link, $parentElementName, $elementName) {
        include("application/renderPagePanel.php");
    }

}
