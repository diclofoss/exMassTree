<?php

class elementShow implements item {

    public static function getDataType($element) {
        return $element->dataType;
    }

    public static function getJs() {
        return null;
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
        include("templates/components/elementShow.php");
        return ob_get_clean();
    }

    public static function renderFilter($component, $parentElement, $element, $data, &$jsInclude) {
        
    }

    public static function renderFrontFilter($component, $parentElement, $element, $data, &$jsInclude) {
        
    }

    public static function renderList($config, $component, $parentElement, $element, $data, &$jsInclude, $hasParent = false, $elementPath = null) {
        return (isset($data->{$element->name})) ? $data->{$element->name} : "";
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
