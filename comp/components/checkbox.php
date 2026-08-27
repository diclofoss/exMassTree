<?php

class checkbox implements item {

    public static function getDataType($element) {
        return "int(11)";
    }

    public static function getJs() {
        
    }

    public static function getCss() {
        return "";
    }

    public static function isNull($application) {
        return false;
    }

    public static function isTable() {
        return false;
    }

    public static function prepareDataForSave($component, $element, $value) {
        return $value;
    }

    public static function prepareFilter($component, $parentElement, $element, $value) {
        $val = "";
        foreach ($value as $data) {
            if ($val != "") {
                $val .= ", ";
            }
            $val .= "$data";
        }
        return "`" . $element->name . "` IN ($val)";
    }

    public static function render($application, $component, $element, $data, &$jsInclude) {
        ob_start();
        include("templates/components/checkbox.php");
        return ob_get_clean();
    }

    public static function renderFilter($component, $parentElement, $element, $data, &$jsInclude) {
        $jsInclude[] = "js/filters/checkboxFilter.js";
        ob_start();
        include("templates/components/checkboxFilter.php");
        return ob_get_clean();
    }

    public static function renderFrontFilter($component, $parentElement, $element, $data, &$jsInclude) {
        
    }

    public static function renderList($application, $component, $parentElement, $element, $data, &$jsInclude, $hasParent = false, $elementPath = null) {
        if (isset($element->items)) {
            return $element->items[$data->{$element->name}];
        } else {
            if (isset($data->{$element->name}) && $data->{$element->name}) {
                return "Да";
            }
            return "Нет";
        }
    }

    public static function prepareDataAfterSave($component, $element, $value) {
        return $value;
    }

    public static function hasSingleLine() {
        
    }

    public static function getNoPostValue() {
        return "0";
    }

}
