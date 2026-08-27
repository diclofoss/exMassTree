<?php

class tableListView implements item {

    public static function render($config, $component, $element, $data, &$jsInclude) {
        foreach ($element->elements as $curElement) {
            if ($curElement->type == "fieldset") {
                continue;
            }
            eval("\$isTable = {$curElement->type}::isTable();");
            if (!isset($data[$element->name])) {
                $data[$element->name] = array();
            }
            if (!$isTable) {
                eval("\$curElement->renderData = {$curElement->type}::render(\$config, \$component, \$curElement, \$data[\$element->name], \$jsInclude);");
            } else if ($data[$element->name]) {
                // Передаем путь вложенности для вложенных виджетов в режиме карточки
                // В режиме карточки (edit/add) вложенные элементы должны быть виджетами
                $isCardMode = (isset($_GET['action']) && ($_GET['action'] == 'edit' || $_GET['action'] == 'add'));
                $nestedPath = null;
                if ($isCardMode) {
                    // В режиме карточки создаем полный путь до родительского элемента
                    // Вычисляем полный путь к текущему элементу из конфигурации
                    $currentElementPath = utils::findElementPath($component->elements, $element->name);
                    if ($currentElementPath) {
                        $nestedPath = $currentElementPath;
                    } else {
                        // Если не удалось найти полный путь, используем имя элемента как fallback
                        $nestedPath = array($element->name);
                    }
                    // Имя текущего элемента ($curElement->name) будет добавлено в generateWidgetIdForElement
                }
                // Передаем parent_id для вложенных элементов (ID текущей карточки)
                $parentId = isset($_GET['id']) ? $_GET['id'] : null;
                eval("\$curElement->renderData = {$curElement->type}::renderList(\$config, \$component, \$element, \$curElement, \$data[\$element->name], \$jsInclude, true, \$nestedPath);");
                // Добавляем data-parent-id к виджет-контейнеру, если это вложенный виджет
                if ($nestedPath !== null && $parentId !== null && isset($curElement->widgetId) && $curElement->widgetId) {
                    $curElement->renderData = preg_replace(
                        '/(<div[^>]*data-widget-id="[^"]*"[^>]*)/',
                        '$1 data-parent-id="' . htmlspecialchars($parentId) . '"',
                        $curElement->renderData,
                        1
                    );
                }
            }
            eval("\$curJsIncludeList[] = {$curElement->type}::getJs();");
            foreach ($curJsIncludeList as $curJsInclude) {
                if (is_array($curJsInclude)) {
                    foreach ($curJsInclude as $curJsIncludeItem) {
                        if (!is_array($curJsIncludeItem)) {
                            $jsInclude[] = $curJsIncludeItem;
                        }
                    }
                } else {
                    $jsInclude[] = $curJsInclude;
                }
            }
        }
        $jsInclude = array_unique($jsInclude);
        ob_start();
        include("templates/components/tableListView.php");
        return ob_get_clean();
    }

    public static function isTable() {
        return true;
    }

    public static function getDataType($element) {
        return null;
    }

    public static function isNull($config) {
        return false;
    }

    // Генерация widgetId (вынесено в отдельный метод для переиспользования)
    protected static function generateWidgetIdForElement($config, $component, $element, $elementPath) {
        // Виджет для режима List (не action=edit и не action=add)
        // ИЛИ для вложенных элементов (когда elementPath не null) - даже в режиме карточки
        $isListMode = (!isset($_GET['action']) || ($_GET['action'] != 'edit' && $_GET['action'] != 'add'));
        $isNestedElement = ($elementPath !== null && is_array($elementPath) && count($elementPath) > 0);
        
        if ($isListMode || $isNestedElement) {
            if ($elementPath === null || !is_array($elementPath) || count($elementPath) == 0) {
                // Корневой элемент - строим путь из category, component, element
                $category = isset($_GET['category']) ? $_GET['category'] : '';
                if (!$category || !$component->name) {
                    $element->widgetId = null;
                    return null;
                }
                $newPath = array($element->name);
                $element->widgetId = WidgetUtils::generateWidgetId($category, $component->name, $newPath);
                return $newPath;
            } else {
                // Вложенный элемент - $elementPath содержит путь до родительского элемента
                // Добавляем имя текущего элемента к пути
                $category = isset($_GET['category']) ? $_GET['category'] : '';
                if (!$category || !$component->name) {
                    $element->widgetId = null;
                    return $elementPath;
                }
                
                // Добавляем имя текущего элемента к пути родителя
                $newPath = array_merge($elementPath, array($element->name));
                $element->widgetId = WidgetUtils::generateWidgetId($category, $component->name, $newPath);
                return $newPath;
            }
        } else {
            // Режим Card - не виджет (только для корневых элементов)
            $element->widgetId = null;
            return $elementPath;
        }
    }

    public static function renderList($config, $component, $parentElement, $element, $data, &$jsInclude, $hasParent = false, $elementPath = null) {
        // Проверяем, является ли это AJAX запросом для виджета (любое действие виджета)
        $isAjaxRequest = isset($_GET['widgetAction']) && in_array($_GET['widgetAction'], ['load', 'pagination', 'filter', 'frontFilter', 'search']);
        
        // Генерация widgetId с учетом вложенности
        // Важно: генерируем widgetId ДО обработки данных, чтобы он был доступен в шаблоне
        if ($isAjaxRequest) {
            // При AJAX запросе widgetId уже установлен в widgetApi.php
            // Восстанавливаем elementPath из widgetId
            if (isset($element->widgetId) && $element->widgetId) {
                $pathInfo = WidgetUtils::parseWidgetId($element->widgetId);
                if ($pathInfo) {
                    $elementPath = $pathInfo['elementPath'];
                }
            }
        } else {
            // При обычном рендере генерируем widgetId
            $elementPath = self::generateWidgetIdForElement($config, $component, $element, $elementPath);
            
            // Если есть widgetId и это первый рендер (не AJAX), возвращаем только placeholder
            // НО: для вложенных элементов в режиме карточки (edit/add) сразу загружаем данные
            // чтобы не было задержки при открытии карточки
            $isCardMode = (isset($_GET['action']) && ($_GET['action'] == 'edit' || $_GET['action'] == 'add'));
            $isNestedInCard = ($isCardMode && $elementPath !== null);
            
            if (isset($element->widgetId) && $element->widgetId && !$isNestedInCard) {
                // Показываем placeholder только для корневых элементов в режиме List
                ob_start();
                include("templates/components/tableListViewList.php");
                return ob_get_clean();
            }
            // Для вложенных элементов в режиме карточки продолжаем рендеринг с данными
        }
        
        $renderData = "";
        // Определяем, откуда брать данные
        if (is_array($data)) {
            // Если $data - массив (виджетный запрос)
            if (isset($data['rootGlobalElement'])) {
                $curData = $data['rootGlobalElement'];
            } else if (isset($data[$element->name])) {
                $curData = $data[$element->name];
            } else {
                $curData = new stdClass();
            }
        } else {
            // Если $data - объект (обычный запрос)
            if (isset($data->{$element->name})) {
                $curData = $data;
            } else {
                $curData = new stdClass();
            }
        }
        foreach ($element->elements as $curElement) {
            if ($curElement->type == "fieldset") {
                continue;
            }
            if (isset($curElement->captionSql) && $curElement->captionSql) {
                $TElement = $config->compDriver($curElement->name);
                $captionData = $TElement->getBySql($curElement->captionSql);
                if ($captionData) {
                    $curElement->caption = $captionData->data;
                }
            }
            // ВАЖНО: Добавляем JS файлы для всех элементов списка при рендеринге
            eval("\$curJsFile = {$curElement->type}::getJs();");
            if ($curJsFile) {
                if (is_array($curJsFile)) {
                    foreach ($curJsFile as $jsItem) {
                        if ($jsItem && !in_array($jsItem, $jsInclude)) {
                            $jsInclude[] = $jsItem;
                        }
                    }
                } else {
                    if (!in_array($curJsFile, $jsInclude)) {
                        $jsInclude[] = $curJsFile;
                    }
                }
            }
        }
        ob_start();
        // Убеждаемся, что $elementPath доступен в шаблоне
        // (он уже установлен выше в generateWidgetIdForElement или передан как параметр)
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
        // $elementPath должен быть доступен в шаблоне (установлен выше)
        include("templates/components/tableListViewList.php");
        return ob_get_clean();
    }

    public static function getJs() {
        return "js/components/tableView.js";
    }

    public static function renderFilter($component, $parentElement, $element, $data, &$jsInclude) {
        
    }

    public static function renderFrontFilter($component, $parentElement, $element, $data, &$jsInclude) {
        
    }

    static function prepareFilter($component, $parentElement, $element, $value) {
        
    }

    public static function prepareDataForSave($component, $element, $value) {
        return $value;
    }

    public static function prepareDataAfterSave($component, $element, $value) {
        return $value;
    }

    public static function updateSortOrder($application, $element) {
        $TElement = $application->compDriver($element->name);
        $dataList = array();
        $where = "";
        foreach ($_POST['idData'] as $id) {
            if ($where) {
                $where .= ", ";
            }
            $where .= $id;
        }
        $idName = $TElement->getIdName();
        $dataList = $TElement->getListBySql("SELECT {$element->sort} FROM " . $TElement->tblname . " WHERE $idName IN ($where) ORDER BY " . $element->sort);
        $orderList = array();
        foreach ($dataList as $data) {
            $orderList[] = $data->{$element->sort};
        }
        $i = 0;
        foreach ($_POST['idData'] as $id) {
            $sort = $orderList[$i];
            $TElement->updateFields($id, array($element->sort => $sort));
            $i++;
        }
        print json_encode(array("result" => 0));
    }

    public static function hasSingleLine() {
        
    }

    public static function getNoPostValue() {
        return "";
    }

    public static function massAction($application, $element) {
        if (!isset($_POST["idDataList"])) {
            return;
        }
        foreach ($_POST["idDataList"] as $var => $val) {
            foreach ($element->massActions as $massAction) {
                if (isset($_POST["action"]) && $_POST["action"] == $massAction->name) {
                    $id = $val;
                    include($massAction->action);
                }
            }
        }
        print json_encode(array("result" => 0));
    }

    public static function getCss() {
        return "";
    }

}
