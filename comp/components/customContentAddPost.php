<?php

class customContentAddPost implements item {

    public static function getDataType($element) {
        
    }

    public static function isNull($config) {
        return true;
    }

    public static function isTable() {
        return false;
    }

    public static function render($config, $component, $element, $data, &$jsInclude) {
        $TDomain = $config->compDriver($element->domainSource);
        $domainsList = $TDomain->getList($id = "", $fields = array(), $withList = array(), $fieldsExclude = array(), $withObjects = array(), $orderByList = array("id DESC"), $limit = "5");
        ob_start();
        include("templates/components/customContentAddPost.php");
        return ob_get_clean();
    }

    public static function renderList($config, $component, $parentElement, $element, $data, &$jsInclude, $hasParent = false, $elementPath = null) {
        
    }

    public static function renderFrontFilter($component, $parentElement, $element, $data, &$jsInclude) {
        
    }

    public static function getJs() {
        return "js/components/customContentAddPost.js";
    }

    public static function getCss() {
        return "";
    }

    public static function addPost($application, $element) {
        include "customContentAddPost/addPost.php";
    }

    public static function getUsers($application, $element) {
        include "customContentAddPost/getUsers.php";
    }

    public static function renderFilter($component, $parentElement, $element, $data, &$jsInclude) {
        
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

}
