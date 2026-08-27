<?php

$dir = 'comp/components/';
$files = scandir($dir);
foreach ($files as $file) {
    if ($file == ".") {
        continue;
    }
    if ($file == "..") {
        continue;
    }
    $dir = implode(DIRECTORY_SEPARATOR, array(dirname(__FILE__), 'components', $file));
    if (is_dir($dir)) {
        continue;
    }
    include_once 'components/' . $file;
}

interface item {

    static function isTable();

    static function render($application, $component, $element, $data, &$jsInclude);

    static function renderList($application, $component, $parentElement, $element, $data, &$jsInclude, $hasParent = false, $elementPath = null);

    static function getDataType($element);

    static function prepareFilter($component, $parentElement, $element, $value);

    static function isNull($application);

    static function getJs();

    static function getCss();

    static function prepareDataForSave($component, $element, $value);

    static function prepareDataAfterSave($component, $element, $value);

    static function renderFilter($component, $parentElement, $element, $data, &$jsInclude);

    static function renderFrontFilter($component, $parentElement, $element, $data, &$jsInclude);

    static function hasSingleLine();

    static function getNoPostValue();
}
