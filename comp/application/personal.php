<?php

$otpUser = null;
$otpSetupSecret = null;
$otpSetupUri = null;
if (!empty($this->config->auth->otp) && method_exists($this->auth, 'otpAction')) {
    $TUserOtp = $this->compDriver("a_user");
    $otpUser = $TUserOtp->getByFields(array("login" => $this->auth->getLogin()));
    if ($otpUser && !$otpUser->otp_enabled && isset($_SESSION['otp_setup_secret'])) {
        $otpSetupSecret = $_SESSION['otp_setup_secret'];
        $otpSetupUri = totp::buildUri($this->config->project, $otpUser->login, $otpSetupSecret);
    }
}
$jsInclude = array("js/qrcode.min.js");

ob_start();
include("templates/personal/left.php");
$renderLeft = ob_get_clean();

ob_start();
include("templates/personal/personal.php");
$renderData = ob_get_clean();

