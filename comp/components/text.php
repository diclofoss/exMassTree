<?php

class text implements item {

    public static function render($config, $component, $element, $data, &$jsInclude) {
        ob_start();
        include("templates/components/text.php");
        return ob_get_clean();
    }

    public static function isTable() {
        return false;
    }

    public static function getDataType($element) {
        return "text";
    }

    public static function isNull($config) {
        return false;
    }

    public static function renderList($config, $component, $parentElement, $element, $data, &$jsInclude, $hasParent = false, $elementPath = null) {
        if (!isset($data->{$element->name})) {
            return "";
        }
        $text = $data->{$element->name};
        $col = 300;

        $text = strip_tags($text);
        if (strlen($text) > $col) {
            $text = substr($text, 0, $col);
            $text = preg_replace("([^ ]+)$", "", $text);
            $text = substr($text, 0, strlen($text) - 1) . "...";
        }
        return $value = $text;
    }

    public static function getJs() {
        return "js/components/text.js";
    }

    public static function renderFilter($component, $parentElement, $element, $data, &$jsInclude) {
        
    }

    public static function renderFrontFilter($component, $parentElement, $element, $data, &$jsInclude) {
        
    }

    static function prepareFilter($component, $parentElement, $element, $value) {
        
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
