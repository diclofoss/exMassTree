<?php

include_once 'stringFloat.php';

class progressBar extends stringFloat {
    public static function renderList($application, $component, $parentElement, $element, $data, &$jsInclude, $hasParent = false, $elementPath = null) {
        ob_start();
        $valueShown = round($data->{$element->name} * 100);
        include("templates/components/progressBarList.php");
        return ob_get_clean();
    }
}
