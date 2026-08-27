<?php

class calc implements item {

    public static function getDataType($element) {
        return "";
    }

    public static function getJs() {
        return "";
    }

    public static function getCss() {
        return "";
    }

    public static function isNull($config) {
        return true;
    }

    public static function isTable() {
        return false;
    }

    public static function prepareDataForSave($component, $element, $value) {
        
    }

    public static function prepareFilter($component, $parentElement, $element, $value) {
        return "1 = '223232'";
    }

    public static function render($application, $component, $element, $data, &$jsInclude) {
        $TElement = $application->compDriver($element->name);
        $idName = $TElement->getIdName();
        $query = str_replace("#VALUE#", $data->{$idName}, $element->query);
        $query = str_replace("#ID#", $_GET['id'], $query);
        $data = $TElement->getBySql($query);
        if (!$data || !$data->data || $data->data == NULL) {
            if (isset($element->default) && $element->default != "") {
                if (!$data) {
                    $data = new stdClass();
                }
                $data->data = $element->default;
            }
        }
        ob_start();
        include("templates/components/calc.php");
        return ob_get_clean();
    }

    public static function renderFilter($component, $parentElement, $element, $data, &$jsInclude) {
        
    }

    public static function renderFrontFilter($component, $parentElement, $element, $data, &$jsInclude) {
        
    }

    public static function renderList($application, $component, $parentElement, $element, $data, &$jsInclude, $hasParent = false, $elementPath = null) {
        $TElement = $application->compDriver($element->name);
        $idName = $TElement->getIdName();
        if (!isset($element->query)) {
            return $data->{$element->name};
        }
        $query = str_replace("#VALUE#", $data->{$idName}, $element->query);
        $data = $TElement->getBySql($query);
        if (!$data || !$data->data || $data->data == NULL) {
            if (isset($element->default) && $element->default != "") {
                return $element->default;
            }
            return "";
        }
        if (isset($element->items) && is_array($element->items) && isset($element->items[$data->data])) {
            return $element->items[$data->data];
        }
        return $data->data;
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
