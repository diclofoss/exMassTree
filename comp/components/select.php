<?php

class select implements item {

    public static function render($config, $component, $element, $data, &$jsInclude) {
        ob_start();
        include("templates/components/select.php");
        return ob_get_clean();
    }

    public static function isTable() {
        return false;
    }

    public static function getDataType($element) {
        if (isset($element->dataType) && $element->dataType) {
            return $element->dataType;
        }
        return "int(11)";
    }

    public static function isNull($config) {
        return false;
    }

    public static function renderList($config, $component, $parentElement, $element, $data, &$jsInclude, $hasParent = false, $elementPath = null) {
        if (!isset($data->{$element->name})) {
            return "";
        }
        $cur = $data->{$element->name};
        if (isset($element->specialKeys) && $element->specialKeys) {
            foreach ($element->items as $var => $val) {
                if ((string) $var === (string) $cur) {
                    return $val;
                }
            }
            // DB value present but not in items (e.g. varchar stored correctly)
            return $cur !== null && $cur !== '' ? (string) $cur : "";
        }
        if (!isset($element->items[$cur])) {
            return $cur !== null && $cur !== '' ? (string) $cur : "";
        }
        return $element->items[$cur];
    }

    public static function getJs() {
        return "";
    }

    public static function renderFilter($component, $element, $curElement, $data, &$jsInclude) {
        $jsInclude[] = "js/filters/selectFilter.js";
        ob_start();
        include("templates/components/selectFilter.php");
        return ob_get_clean();
    }

    public static function renderFrontFilter($component, $parentElement, $element, $data, &$jsInclude) {
        
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
