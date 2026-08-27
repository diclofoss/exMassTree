<?php

class popup {

    public static function getDataType($element) {
        return null;
    }

    public static function isTable() {
        return false;
    }

    public static function getJs() {
        return "js/components/popup.js";
    }

    public static function render($config, $component, $element, $data, &$jsInclude) {
        ob_start();
        include("templates/components/popup.php");
        return ob_get_clean();
    }

}
