<?php

include_once 'tableListView.php';

class galleryView extends tableListView {

    public static function renderList($config, $component, $parentElement, $element, $data, &$jsInclude, $hasParent = false, $elementPath = null) {
        ob_start();
        include("templates/components/galleryView.php");
        return ob_get_clean();
    }

    public static function getNoPostValue() {
        return "";
    }

}
