<?php

include_once 'tableListView.php';

class chartView extends tableListView {

    public static function getJs() {
        return array("js/chart.min.js", "js/components/chartView.js");
    }

    public static function renderList($config, $component, $parentElement, $element, $data, &$jsInclude, $hasParent = false, $elementPath = null) {
        $renderData = "";

        $TElement = $config->compDriver($element->name);
        $idName = $TElement->getIdName();
        if (!isset($element->lines)) {
            return "Nodata for " . $element->name;
        }
        foreach ($element->lines as $line) {
            $filterData = "";
            if (isset($_SESSION['filter'][$component->name][$element->name])) {
                foreach ($_SESSION['filter'][$component->name][$element->name] as $filterKey => $filterVal) {
                    foreach ($element->elements as $curElement) {
                        if ($curElement->name == $filterKey) {
                            if ($filterData) {
                                $filterData .= " AND ";
                            }
                            eval("\$filterData .= {$curElement->type}::prepareFilter(\$component, \$element, \$curElement, \$filterVal);");
                        }
                    }
                }
            }
            $filterData_where_and = "";
            $filterData_where = "";
            if ($filterData) {
                $filterData_where_and = " $filterData";
                $filterData_where = " WHERE $filterData";
            }
            $line->query = str_replace("#TPREFIX#", $config->config->database->prefix, $line->query);
            $line->query = str_replace("#WHERE#", $filterData_where, $line->query);
            if (isset($_GET['id'])) {
                $line->query = str_replace("#PARENT#", $_GET['id'], $line->query);
            }
            $line->query = str_replace("#WHERE_AND#", $filterData_where_and, $line->query);
            $line->data = $TElement->getListBySql($line->query);
        }

        if (isset($data->{$element->name})) {
            $curData = $data;
        } else if (!isset($data[$element->name])) {
            $curData = $data['rootGlobalElement'];
        }
        foreach ($element->elements as $curElement) {
            if (isset($curElement->captionSql) && $curElement->captionSql) {
                $TElement = $config->compDriver($curElement->name);
                $captionData = $TElement->getBySql($curElement->captionSql);
                if ($captionData) {
                    $curElement->caption = $captionData->data;
                }
            }
        }
        ob_start();
        if (isset($element->filters)) {
            $elements = array();
            $element->filterData = array();
            foreach ($element->elements as $curElement) {
                foreach ($element->filters as $filterName) {
                    if ($curElement->name == $filterName) {
                        $elements[] = $curElement;
                    }
                }
            }
            foreach ($elements as $curElement) {
                eval("\$element->filterData[] = {$curElement->type}::renderFilter(\$component, \$element, \$curElement, \$curData, \$jsInclude);");
            }
        }
        if (isset($element->frontFilters)) {
            $elements = array();
            $element->frontFilterData = array();
            foreach ($element->elements as $curElement) {
                foreach ($element->frontFilters as $filterName) {
                    if ($curElement->name == $filterName) {
                        $elements[] = $curElement;
                    }
                }
            }
            foreach ($elements as $curElement) {
                eval("\$element->frontFilterData[] = {$curElement->type}::renderFrontFilter(\$component, \$element, \$curElement, \$curData, \$jsInclude);");
            }
        }
        include("templates/components/chartView.php");
        return ob_get_clean();
    }

}
