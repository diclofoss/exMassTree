<?php

$category = "";
$component = "";
$element = "";
foreach ($this->config->categories as $curCategory) {
    if ($curCategory->name != $_GET['category']) {
        continue;
    }
    $category = $curCategory;
    if (isset($_GET['component'])) {
        foreach ($curCategory->components as $curComponent) {
            if ($curComponent->name != $_GET['component']) {
                continue;
            }
            $component = $curComponent;
            if (isset($_GET['element'])) {
                $element = utils::findElement($component->elements, $_GET['element']);
            }
        }
    }
}

if (isset($category->sqlMenu) && $category->sqlMenu) {
    $TElement = $this->compDriver("all");
    $category->categorySqlMenuItems = $TElement->getListBySql($category->sqlMenu);
}

if (!$component) {
    return;
}

$mainListElements = $component->elements;
if ($element) {
    $mainListElements = $element->elements;
    $TElement = $this->compDriver($element->table);
}

$this->data = array();
if (!isset($_GET['action']) || $_GET['action'] != "add") {
    if ($element) {
        $this->data[$element->name] = $TElement->get($_GET['id']);
    } else {
        $element = new stdClass();
        $element->name = "rootGlobalElement";
        $this->data[$element->name] = new stdClass();
    }
    foreach ($mainListElements as $curElement) {
        if ($curElement->type == "fieldset") {
            continue;
        }
        eval("\$isTable = {$curElement->type}::isTable();");
        if (!$isTable) {
            continue;
        }
        
        // Пропускаем загрузку данных для виджетов
        // Виджетами являются tableView и singleView - они будут загружаться через AJAX
        // Виджеты создаются только для режима List (не action=edit и не action=add)
        // В режиме карточки данные загружаются как обычно, но пагинация работает через AJAX
        if ((!isset($_GET['action']) || ($_GET['action'] != 'edit' && $_GET['action'] != 'add'))) {
            // Это будет виджет - пропускаем загрузку данных на сервере
            // Создаем пустую структуру данных для совместимости с шаблоном
            if (!isset($this->data[$element->name])) {
                $this->data[$element->name] = new stdClass();
            }
            $this->data[$element->name]->{$curElement->name} = array(); // Пустой массив
            $this->data[$element->name]->{$curElement->name . "pagePanel"} = ''; // Пустая пагинация
            $this->data[$element->name]->{$curElement->name . "offset"} = 0; // Нулевой offset
            continue; // Пропускаем загрузку данных - они загрузятся через AJAX
        }
        if (isset($curElement->table) && $curElement->table) {
            $TElement = $this->compDriver($curElement->table);
        } else {
            $TElement = $this->compDriver($curElement->name);
        }
        if (isset($curElement->query) && $curElement->query) {
            if (isset($_GET['id'])) {
                $sql = str_replace("#VALUE#", $_GET['id'], $curElement->query);
            } else {
                $sql = $curElement->query;
            }
            $where2 = "";
            foreach ($curElement->elements as $elm) {
                if (isset($_SESSION['filter'][$_GET['component']][$curElement->name]["textsearch"])) {
                    $filterValue = $_SESSION['filter'][$_GET['component']][$curElement->name]["textsearch"];
                    if ($where2) {
                        $where2 .= " OR ";
                    }
                    eval("\$where2 .= {$elm->type}::prepareFilter(\$component, \$curElement, \$elm, \$filterValue);");
                }
            }
            if ($where2) {
                $sql = "select * from ($sql) a where ($where2)";
            }
            $orderBy = "";
            if (isset($curElement->order) && $curElement->order) {
                $orderBy = " ORDER BY " . $curElement->order;
            }
            if (isset($curElement->limit) && $curElement->limit) {
                $limit = $curElement->limit;
                $pagePanel = "";
                $perPage = $limit;
                $curpage = 1;
            } else {
                $curpage = 1;
                $perPage = 30;
                if (isset($_SESSION['pages'][$element->name][$curElement->name])) {
                    $curpage = $_SESSION['pages'][$element->name][$curElement->name];
                }
                $limit = ($curpage - 1) * $perPage;
                $limit = "$limit, $perPage";
                $totalObj = $TElement->getBySql("SELECT count(*) as total FROM ($sql) d");
                ob_start();
                application::renderPagePanel($totalObj->total, $curpage, $perPage, "#", $element->name, $curElement->name);
                $pagePanel = ob_get_clean();
            }
            // Убеждаемся, что $this->data[$element->name] - это объект, а не строка
            if (!is_object($this->data[$element->name])) {
                $this->data[$element->name] = new stdClass();
            }
            $this->data[$element->name]->{$curElement->name . "pagePanel"} = $pagePanel;
            $this->data[$element->name]->{$curElement->name . "offset"} = ($curpage - 1) * $perPage;
            $this->data[$element->name]->{$curElement->name} = $TElement->getListBySql($sql .  " $orderBy LIMIT $limit");
        } else {
            if (!isset($curElement->order)) {
                $curElement->order = "";
            }
            if ($element->name != "rootGlobalElement") {
                $parent_id = $TElement->getParentIdName();
                $where = "$parent_id = " . $_GET['id'];
            } else {
                $where = "";
            }
            $orderBy = "";
            if (isset($curElement->order) && $curElement->order) {
                $orderBy = " ORDER BY " . $curElement->order;
            }
            if (isset($curElement->sort) && $curElement->sort) {
                $orderBy = " ORDER BY " . $curElement->sort;
            }
            foreach ($curElement->elements as $elm) {
                if (isset($_SESSION['filter'][$_GET['component']][$curElement->name][$elm->name]) && $_SESSION['filter'][$_GET['component']][$curElement->name][$elm->name]) {
                    $filterValue = $_SESSION['filter'][$_GET['component']][$curElement->name][$elm->name];
                    if ($where) {
                        $where .= " AND ";
                    }
                    eval("\$where .= {$elm->type}::prepareFilter(\$component, \$curElement, \$elm, \$filterValue);");
                }
            }
            if (isset($_SESSION['filter'][$_GET['component']][$curElement->name]['textsearch']) && isset($curElement->textsearch)) {
                $curWhere = "";
                $filterValue = $_SESSION['filter'][$_GET['component']][$curElement->name]['textsearch'];
                foreach ($curElement->elements as $elm) {
                    if (in_array($elm->name, $curElement->textsearch)) {
                        if ($curWhere) {
                            $curWhere .= " OR ";
                        }
                        eval("\$curWhere .= {$elm->type}::prepareFilter(\$component, \$curElement, \$elm, \$filterValue);");
                    }
                }
                if (in_array("#ID#", $curElement->textsearch)) {
                    if ($curWhere) {
                        $curWhere .= " OR ";
                    }
                    $curWhere .= $TElement->getIdName() . " = '$filterValue'";
                }
                if ($curWhere) {
                    if ($where) {
                        $where .= " AND ";
                    }
                    $where .= "($curWhere)";
                }
            }
            if ($where) {
                $where = "WHERE $where";
            }
            if (isset($curElement->limit) && $curElement->limit) {
                $limit = $curElement->limit;
                $pagePanel = "";
                $perPage = $limit;
                $curpage = 1;
            } else {
                $curpage = 1;
                $perPage = 30;
                if (isset($_SESSION['pages'][$element->name][$curElement->name])) {
                    $curpage = $_SESSION['pages'][$element->name][$curElement->name];
                }
                $limit = ($curpage - 1) * $perPage;
                $limit = "$limit, $perPage";
                $totalObj = $TElement->getBySql("SELECT count(*) as total FROM {$TElement->tblname} $where");
                ob_start();
                application::renderPagePanel($totalObj->total, $curpage, $perPage, "#", $element->name, $curElement->name);
                $pagePanel = ob_get_clean();
            }
            eval("\$singleLine = {$curElement->type}::hasSingleLine();");
            // Убеждаемся, что $this->data[$element->name] - это объект, а не строка
            if (!is_object($this->data[$element->name])) {
                $this->data[$element->name] = new stdClass();
            }
            if ($singleLine) {
                $this->data[$element->name]->{$curElement->name} = $TElement->getBySql("SELECT * FROM {$TElement->tblname} $where");
            } else {
                $this->data[$element->name]->{$curElement->name . "pagePanel"} = $pagePanel;
                $this->data[$element->name]->{$curElement->name . "offset"} = ($curpage - 1) * $perPage;
                $this->data[$element->name]->{$curElement->name} = $TElement->getListBySql("SELECT * FROM {$TElement->tblname} $where $orderBy LIMIT $limit");
            }
        }
    }
}