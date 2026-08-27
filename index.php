<?php

$EXMASSTREE_VERSION = "2.1.1";

require_once 'comp/application.php';

session_start();
$application = new application("config/config.json", "config/config.php");
$application->auth->auth($application);

if (!$application->auth->isAuthed() && !isset($_GET['login'])) {
    $queryString = $_SERVER['QUERY_STRING'];
    if ($queryString) {
        $returnUrl = '?' . $queryString;
    } else {
        $returnUrl = $_SERVER['REQUEST_URI'];
        if (strpos($returnUrl, $application->dirName) === 0) {
            $returnUrl = substr($returnUrl, strlen($application->dirName));
        }
        if ($returnUrl === '/' || $returnUrl === '') {
            $returnUrl = '/';
        }
    }
    $_SESSION['return_url'] = $returnUrl;
    header("Location: {$application->dirName}/?login\n\n");
    exit();
}

if ($application->auth->isAuthed()
        && method_exists($application->auth, 'otpRequired') && $application->auth->otpRequired($application)
        && empty($application->auth->otpEnabled)) {
    $isOtpSetupSubmit = isset($_GET['personal']) && isset($_POST['otpAction']);
    $allowed = isset($_GET['logout']) || isset($_GET['personal']) || $isOtpSetupSubmit;
    if (!$allowed) {
        $_SESSION['otp_force_setup'] = true;
        header("Location: {$application->dirName}/?personal\n\n");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' || (isset($_REQUEST['POST']) && $_REQUEST['POST'] == true) || (isset($_GET['action']) && !in_array($_GET['action'], array("edit", "add")))) {
    $application->onSubmit();
    if (!isset($_GET['dataType']) || $_GET['dataType'] == "html") {
        if ($application->status == 200) {
            if (isset($application->redirect)) {
                header("Location: {$application->redirect}");
            } else {
                header("Location: {$_SERVER['HTTP_REFERER']}");
            }
            exit();
        }
    } else {
        exit();
    }
}

if (count($_GET) == 0 && count($_POST) == 0) {
    header("Location: {$application->dirName}/{$application->auth->defaultHome}\n\n");
    exit();
}

if (isset($_GET['login'])) {
    $application->showLogin();
    exit();
}

if (isset($_GET['logout'])) {
    $application->auth->logout();
    header("Location: {$application->dirName}/?login\n\n");
    exit();
}

if (isset($_GET['personal'])) {
    $application->showPersonal();
    exit();
}

if (isset($_GET['admin'])) {
    if (!$application->auth->isAdmin()) {
        header("Location: {$application->dirName}/{$application->auth->defaultHome}\n\n");
        exit();
    }
    $application->showAdmin();
    exit();
}
if (isset($_GET['component'])) {
    if (!in_array($_GET['component'], $application->auth->componentList)) {
        header("Location: {$application->dirName}/{$application->auth->defaultHome}\n\n");
        exit();
    }
}
$application->prepareData();
$application->render();
