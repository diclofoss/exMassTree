<?php

class datetimeEl implements item {

    public static function getDataType($element) {
        return "datetime";
    }

    public static function getJs() {
        return "js/components/datetimeEl.js";
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
        include("templates/components/datetimeEl.php");
        return ob_get_clean();
    }

    public static function renderFrontFilter($component, $parentElement, $element, $data, &$jsInclude) {
        ob_start();
        include("templates/components/datetimeElFrontFilter.php");
        return ob_get_clean();
    }

    public static function renderList($config, $component, $parentElement, $element, $data, &$jsInclude, $hasParent = false, $elementPath = null) {
        return $data->{$element->name};
    }

    public static function renderFilter($component, $parentElement, $element, $data, &$jsInclude) {
        
    }

    static function prepareFilter($component, $parentElement, $element, $value) {
        list($from, $to) = preg_split("/ - /", $value);
        return "`" . $element->name . "` BETWEEN '$from' AND '$to'";
    }

    public static function prepareDataForSave($component, $element, $value) {
        return $value;
    }

    public static function prepareDataAfterSave($component, $element, $value) {
        return $value;
    }

    public static function hasSingleLine() {
        
    }

    public static function getNoPostValue() {
        return "";
    }

    public static function getCss() {
        return "css/daterangepicker.css";
    }

}
