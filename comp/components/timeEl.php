<?php

class timeEl implements item {

    public static function getDataType($element) {
        return "time";
    }

    public static function getJs() {
        
    }

    public static function getCss() {
        return "";
    }

    public static function isNull($element) {
        if (isset($element->isNull) && $element->isNull) {
            return true;
        }
        return false;
    }

    public static function isTable() {
        return false;
    }

    public static function render($config, $component, $element, $data, &$jsInclude) {
        ob_start();
        include("templates/components/timeEl.php");
        return ob_get_clean();
    }

    public static function renderFrontFilter($component, $parentElement, $element, $data, &$jsInclude) {
        
    }

    public static function renderList($config, $component, $parentElement, $element, $data, &$jsInclude, $hasParent = false, $elementPath = null) {
        return $data->{$element->name};
    }

    public static function renderFilter($component, $parentElement, $element, $data, &$jsInclude) {
        
    }

    static function prepareFilter($component, $parentElement, $element, $value) {
        
    }

    public static function prepareDataForSave($component, $element, $value) {
        if (!isset($_POST[$element->name . "_h"]) || !$_POST[$element->name . "_m"]) {
            return "NULL";
        }
        $h = $_POST[$element->name . "_h"];
        $m = $_POST[$element->name . "_m"];
        $date = "$h:$m";
        return $date;
    }

    public static function prepareDataAfterSave($component, $element, $value) {
        return $value;
    }

    public static function hasSingleLine() {
        
    }

    public static function getNoPostValue() {
        return "";
    }

}
