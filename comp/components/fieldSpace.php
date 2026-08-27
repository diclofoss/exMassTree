<?php

class fieldSpace implements item {
    
    public static function getDataType($element) {
        return null;
    }

    public static function getJs() {
        return "";
    }

    public static function isNull($config) {
        return true;
    }

    public static function isTable() {
        return false;
    }

    public static function prepareFilter($component, $parentElement, $element, $value) {
        
    }

    public static function render($config, $component, $element, $data, &$jsInclude) {
        foreach ($element->elements as $curElement) {
            eval("\$isTable = {$curElement->type}::isTable();");
            if (!isset($data->{$element->name})) {
                $data->{$element->name} = array();
            }
            if (!$isTable) {
                eval("\$curElement->renderData = {$curElement->type}::render(\$config, \$component, \$curElement, \$data->{\$element->name}, \$jsInclude);");
                eval("\$jsInclude[] = {$curElement->type}::getJs();");
            } else if ($data[$element->name]) {
                eval("\$curElement->renderData = {$curElement->type}::renderList(\$config, \$component, \$curElement, \$data[\$element->name], \$jsInclude, true);");
            }
        }
        ob_start();
        include("templates/components/fieldSpace.php");
        return ob_get_clean();
    }

    public static function renderFilter($component, $parentElement, $element, $data, &$jsInclude) {
        
    }

    public static function renderFrontFilter($component, $parentElement, $element, $data, &$jsInclude) {
        
    }

    public static function renderList($config, $component, $parentElement, $element, $data, &$jsInclude, $hasParent = false, $elementPath = null) {
        
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