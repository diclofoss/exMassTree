<?php

include_once 'tableListView.php';

class singleView extends tableListView {

    public static function renderList($config, $component, $parentElement, $element, $data, &$jsInclude, $hasParent = false, $elementPath = null) {
        // Генерация widgetId с учетом вложенности
        $currentPath = self::generateWidgetIdForElement($config, $component, $element, $elementPath);
        
        foreach ($element->elements as $curElement) {
            if ($curElement->type == "fieldset") {
                continue;
            }
            eval("\$isTable = {$curElement->type}::isTable();");
            if (isset($data->{$element->name})) {
                $curData = $data->{$element->name};
            } else if (!isset($data[$element->name])) {
                $curData = $data['rootGlobalElement']->{$element->name};
            }
            if (!$isTable) {
                if (isset($data->{$element->name})) {
                    eval("\$curElement->renderData = {$curElement->type}::render(\$config, \$component, \$curElement, \$curData, \$jsInclude);");
                } else {
                    eval("\$curElement->renderData = {$curElement->type}::render(\$config, \$component, \$curElement, \$curData, \$jsInclude);");
                }
                eval("\$jsInclude[] = {$curElement->type}::getJs();");
            } else if ($data[$element->name]) {
                // Передаем путь вложенности для вложенных виджетов
                eval("\$curElement->renderData = {$curElement->type}::renderList(\$config, \$component, \$element, \$curElement, \$data[\$element->name], \$jsInclude, true, \$currentPath);");
            }
        }
        ob_start();
        if ($parentElement) {
            $parentSave = true;
        }
        include("templates/components/tableListView.php");
        return ob_get_clean();
    }

    public static function hasSingleLine() {
        return true;
    }

    public static function getNoPostValue() {
        return "";
    }

    public static function getCss() {
        return "";
    }

}
