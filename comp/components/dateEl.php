<?php

class dateEl implements item {

    public static function getDataType($element) {
        return "date";
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
        include("templates/components/dateEl.php");
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
        if (!isset($_POST[$element->name . "_date"]) || !$_POST[$element->name . "_date"]) {
            return "NULL";
        }
        $date = $_POST[$element->name . "_date"];
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
