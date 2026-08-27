<?php

class password implements item {

    public static function getDataType($element) {
        return "varchar(90)";
    }

    public static function getJs() {
        
    }

    public static function isNull($config) {
        return false;
    }

    public static function isTable() {
        return false;
    }

    public static function prepareFilter($component, $parentElement, $element, $value) {
        
    }

    public static function render($config, $component, $element, $data, &$jsInclude) {
        ob_start();
        include("templates/components/password.php");
        return ob_get_clean();
    }

    public static function renderFilter($component, $parentElement, $element, $data, &$jsInclude) {
        
    }

    public static function renderFrontFilter($component, $parentElement, $element, $data, &$jsInclude) {
        
    }

    public static function renderList($config, $component, $parentElement, $element, $data, &$jsInclude, $hasParent = false, $elementPath = null) {
        
    }

    public static function prepareDataForSave($component, $element, $value) {
        if (!$value) {
            return "#UNSET_DATA#";
        }
        if ($element->method == 'md5') {
            $value = md5($value);
        } else if ($element->method == 'sha1') {
            $value = strtoupper(
                    sha1(
                            sha1($value, true)
                    )
            );
            $value = '*' . $value;
        }
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
