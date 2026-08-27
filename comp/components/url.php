<?php

class url implements item {

    public static function getDataType($element) {
        return "text";
    }

    public static function getJs() {
        return "";
    }

    public static function isNull($config) {
        return false;
    }

    public static function isTable() {
        return false;
    }

    public static function prepareFilter($component, $parentElement, $element, $value) {
        return "`" . $element->name . "` LIKE '%$value%'";
    }

    public static function render($config, $component, $element, $data, &$jsInclude) {
        ob_start();
        include("templates/components/url.php");
        return ob_get_clean();
    }

    public static function renderFilter($component, $parentElement, $element, $data, &$jsInclude) {
        
    }

    public static function renderFrontFilter($component, $parentElement, $element, $data, &$jsInclude) {
        ob_start();
        include("templates/components/stringFrontFilter.php");
        return ob_get_clean();
    }

    public static function renderList($config, $component, $parentElement, $element, $data, &$jsInclude, $hasParent = false, $elementPath = null) {
        if (!isset($data->{$element->name})) {
            return "";
        }
        return $data->{$element->name};
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
        return "";
    }

}
