<?php

// API endpoint для обработки AJAX запросов от виджетов

$widgetId = isset($_GET['widgetId']) ? $_GET['widgetId'] : '';
$action = isset($_GET['widgetAction']) ? $_GET['widgetAction'] : 'load';

if (!$widgetId) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(array('error' => 'widgetId required'));
    exit;
}

// Парсим widgetId для получения пути
$pathInfo = WidgetUtils::parseWidgetId($widgetId);
if (!$pathInfo) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(array('error' => 'Invalid widgetId'));
    exit;
}

$category = $pathInfo['category'];
$componentName = $pathInfo['component'];
$elementPath = $pathInfo['elementPath'];

// Находим категорию и компонент
$categoryObj = null;
$componentObj = null;
foreach ($this->config->categories as $curCategory) {
    if ($curCategory->name == $category) {
        $categoryObj = $curCategory;
        foreach ($curCategory->components as $curComponent) {
            if ($curComponent->name == $componentName) {
                $componentObj = $curComponent;
                break;
            }
        }
        break;
    }
}

if (!$categoryObj || !$componentObj) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(array('error' => 'Category or component not found'));
    exit;
}

// Находим элемент по пути
// Если elementPath пустой, это ошибка (должен быть хотя бы один элемент)
if (empty($elementPath) || !is_array($elementPath)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(array('error' => 'Empty elementPath', 'debug' => array('elementPath' => $elementPath)));
    exit;
}

// Начинаем поиск с компонента (для корневых элементов ищем в $componentObj->elements)
$element = null;
$currentContainer = $componentObj; // Начинаем с компонента

// Проходим по пути элементов
foreach ($elementPath as $index => $elementName) {
    $found = false;
    
    // Ищем элемент в текущем контейнере
    // Для первого элемента (корневого) ищем в $componentObj->elements
    // Для вложенных элементов ищем в $element->elements
    if (isset($currentContainer->elements) && is_array($currentContainer->elements)) {
        foreach ($currentContainer->elements as $curElement) {
            if ($curElement->name == $elementName) {
                $element = $curElement;
                $currentContainer = $curElement; // Для следующей итерации ищем в этом элементе
                $found = true;
                break;
            }
        }
    }
    
    if (!$found) {
        // Отладочный вывод
        $availableElements = array();
        if (isset($currentContainer->elements) && is_array($currentContainer->elements)) {
            foreach ($currentContainer->elements as $el) {
                $availableElements[] = $el->name;
            }
        }
        
        error_log("WidgetApi: Element not found. Path: " . implode(' -> ', $elementPath) . ", Looking for: " . $elementName . " (index: " . $index . ")");
        error_log("WidgetApi: Current container: " . (isset($currentContainer->name) ? $currentContainer->name : (isset($currentContainer->title) ? $currentContainer->title : 'unknown')));
        error_log("WidgetApi: Available elements: " . implode(', ', $availableElements));
        
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(array(
            'error' => 'Element not found in path',
            'debug' => array(
                'path' => $elementPath,
                'looking_for' => $elementName,
                'index' => $index,
                'container' => isset($currentContainer->name) ? $currentContainer->name : (isset($currentContainer->title) ? $currentContainer->title : 'unknown'),
                'available_elements' => $availableElements
            )
        ));
        exit;
    }
}

// Если элемент не найден после прохода по пути
if (!$element) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(array('error' => 'Element not found after path traversal', 'debug' => array('path' => $elementPath)));
    exit;
}

// Получаем состояние виджета из cookies
$state = WidgetUtils::getWidgetState($widgetId);

// Определяем parent_id для виджетов
// Логика аналогична prepareData.php:
// - Для вложенных виджетов (elementPath > 1) используем parent_id из запроса (ID строки таблицы)
// - Если parent_id нет в запросе, используем $_GET['id'] (ID текущей карточки) как fallback
// - Для корневых виджетов (elementPath == 1) используем $_GET['id'] только если открыта карточка
$parentId = null;
if (count($elementPath) > 1) {
    // Вложенный виджет - используем parent_id из запроса (ID строки таблицы)
    // Если нет в запросе, используем $_GET['id'] (ID текущей карточки) как fallback
    $parentId = isset($_GET['parent_id']) ? $_GET['parent_id'] : (isset($_GET['id']) ? $_GET['id'] : null);
} else {
    // Корневой виджет - используем $_GET['id'] только если открыта карточка
    // (аналогично prepareData.php: if ($element->name != "rootGlobalElement"))
    $isCardMode = (isset($_GET['action']) && ($_GET['action'] == 'edit' || $_GET['action'] == 'add'));
    if ($isCardMode && isset($_GET['id'])) {
        $parentId = $_GET['id'];
    } else {
        // Для списка корневой виджет не использует parent_id
        $parentId = null;
    }
}

// Устанавливаем $_GET для совместимости с шаблонами
$_GET['category'] = $category;
$_GET['component'] = $componentName;

// Обрабатываем действие
switch ($action) {
    case 'load':
        // Загрузить данные виджета
        $data = prepareWidgetData($this, $componentObj, $element, $state, $parentId);
        $jsInclude = array();
        // Устанавливаем widgetId из запроса, чтобы не генерировать новый
        if (!isset($element->widgetId)) {
            $element->widgetId = $widgetId;
        }
        eval("\$html = {$element->type}::renderList(\$this, \$componentObj, null, \$element, \$data, \$jsInclude, false, \$elementPath);");
        // Обрабатываем JS файлы из $jsInclude
        $jsFiles = array();
        foreach ($jsInclude as $jInclude) {
            if (is_array($jInclude)) {
                foreach ($jInclude as $jI) {
                    if ($jI && !in_array($jI, $jsFiles)) {
                        $jsFiles[] = $jI;
                    }
                }
            } else {
                if ($jInclude && !in_array($jInclude, $jsFiles)) {
                    $jsFiles[] = $jInclude;
                }
            }
        }
        $jsFiles = array_unique($jsFiles);
        header('Content-Type: application/json');
        echo json_encode(array('html' => $html, 'jsFiles' => $jsFiles));
        break;
        
    case 'pagination':
        // Смена страницы
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        if ($page < 1) $page = 1;
        WidgetUtils::setWidgetCookie($widgetId, 'page', $page);
        $state = WidgetUtils::getWidgetState($widgetId);
        $data = prepareWidgetData($this, $componentObj, $element, $state, $parentId);
        $jsInclude = array();
        // Устанавливаем widgetId из запроса, чтобы не генерировать новый
        if (!isset($element->widgetId)) {
            $element->widgetId = $widgetId;
        }
        eval("\$html = {$element->type}::renderList(\$this, \$componentObj, null, \$element, \$data, \$jsInclude, false, \$elementPath);");
        // Обрабатываем JS файлы из $jsInclude
        $jsFiles = array();
        foreach ($jsInclude as $jInclude) {
            if (is_array($jInclude)) {
                foreach ($jInclude as $jI) {
                    if ($jI && !in_array($jI, $jsFiles)) {
                        $jsFiles[] = $jI;
                    }
                }
            } else {
                if ($jInclude && !in_array($jInclude, $jsFiles)) {
                    $jsFiles[] = $jInclude;
                }
            }
        }
        $jsFiles = array_unique($jsFiles);
        // Если это вложенный виджет и есть parent_id, добавляем атрибут в HTML
        if (count($elementPath) > 1 && $parentId) {
            $html = preg_replace(
                '/(<div[^>]*data-widget-id="[^"]*"[^>]*)/',
                '$1 data-parent-id="' . htmlspecialchars($parentId) . '"',
                $html,
                1
            );
        }
        header('Content-Type: application/json');
        echo json_encode(array('html' => $html, 'jsFiles' => $jsFiles));
        break;
        
    case 'filter':
        // Устанавливаем widgetId из запроса
        if (!isset($element->widgetId)) {
            $element->widgetId = $widgetId;
        }
        // Обработка фильтров (модальное окно)
        $filterName = isset($_POST['element']) ? $_POST['element'] : '';
        $filterValue = null;
        
        // Проверяем массив данных (для multiselect)
        if (isset($_POST['data']) && is_array($_POST['data'])) {
            $filterValue = $_POST['data'];
        } else if (isset($_POST['data'])) {
            $filterValue = $_POST['data'];
        } else if (isset($_POST['data[]']) && is_array($_POST['data[]'])) {
            $filterValue = $_POST['data[]'];
        } else if (isset($_POST['data[]'])) {
            $filterValue = array($_POST['data[]']);
        }
        
        if (isset($_POST['clearFilter']) && $_POST['clearFilter']) {
            // Очистить фильтр
            $filters = isset($state['filters']) ? $state['filters'] : array();
            unset($filters[$filterName]);
            WidgetUtils::setWidgetCookie($widgetId, 'filters', $filters);
        } else if ($filterName && $filterValue !== null) {
            // Установить фильтр
            $filters = isset($state['filters']) ? $state['filters'] : array();
            $filters[$filterName] = $filterValue;
            WidgetUtils::setWidgetCookie($widgetId, 'filters', $filters);
            WidgetUtils::setWidgetCookie($widgetId, 'page', 1); // Сброс на первую страницу
        }
        
        $state = WidgetUtils::getWidgetState($widgetId);
        $data = prepareWidgetData($this, $componentObj, $element, $state, $parentId);
        $jsInclude = array();
        eval("\$html = {$element->type}::renderList(\$this, \$componentObj, null, \$element, \$data, \$jsInclude, false, \$elementPath);");
        // Обрабатываем JS файлы из $jsInclude
        $jsFiles = array();
        foreach ($jsInclude as $jInclude) {
            if (is_array($jInclude)) {
                foreach ($jInclude as $jI) {
                    if ($jI && !in_array($jI, $jsFiles)) {
                        $jsFiles[] = $jI;
                    }
                }
            } else {
                if ($jInclude && !in_array($jInclude, $jsFiles)) {
                    $jsFiles[] = $jInclude;
                }
            }
        }
        $jsFiles = array_unique($jsFiles);
        // Если это вложенный виджет и есть parent_id, добавляем атрибут в HTML
        if (count($elementPath) > 1 && $parentId) {
            $html = preg_replace(
                '/(<div[^>]*data-widget-id="[^"]*"[^>]*)/',
                '$1 data-parent-id="' . htmlspecialchars($parentId) . '"',
                $html,
                1
            );
        }
        header('Content-Type: application/json');
        echo json_encode(array('html' => $html, 'jsFiles' => $jsFiles));
        break;
        
    case 'frontFilter':
        // Устанавливаем widgetId из запроса
        if (!isset($element->widgetId)) {
            $element->widgetId = $widgetId;
        }
        // Обработка фронтальных фильтров
        $frontFilters = array();
        foreach ($_POST as $key => $value) {
            if (preg_match('/(.*)FrontFilter/', $key, $matches)) {
                $frontFilters[$matches[1]] = $value;
            }
        }
        
        if (isset($_POST['clearFilter']) && $_POST['clearFilter']) {
            WidgetUtils::setWidgetCookie($widgetId, 'frontFilters', array());
        } else {
            WidgetUtils::setWidgetCookie($widgetId, 'frontFilters', $frontFilters);
            WidgetUtils::setWidgetCookie($widgetId, 'page', 1); // Сброс на первую страницу
        }
        
        $state = WidgetUtils::getWidgetState($widgetId);
        $data = prepareWidgetData($this, $componentObj, $element, $state, $parentId);
        $jsInclude = array();
        eval("\$html = {$element->type}::renderList(\$this, \$componentObj, null, \$element, \$data, \$jsInclude, false, \$elementPath);");
        // Обрабатываем JS файлы из $jsInclude
        $jsFiles = array();
        foreach ($jsInclude as $jInclude) {
            if (is_array($jInclude)) {
                foreach ($jInclude as $jI) {
                    if ($jI && !in_array($jI, $jsFiles)) {
                        $jsFiles[] = $jI;
                    }
                }
            } else {
                if ($jInclude && !in_array($jInclude, $jsFiles)) {
                    $jsFiles[] = $jInclude;
                }
            }
        }
        $jsFiles = array_unique($jsFiles);
        // Если это вложенный виджет и есть parent_id, добавляем атрибут в HTML
        if (count($elementPath) > 1 && $parentId) {
            $html = preg_replace(
                '/(<div[^>]*data-widget-id="[^"]*"[^>]*)/',
                '$1 data-parent-id="' . htmlspecialchars($parentId) . '"',
                $html,
                1
            );
        }
        header('Content-Type: application/json');
        echo json_encode(array('html' => $html, 'jsFiles' => $jsFiles));
        break;
        
    case 'search':
        // Устанавливаем widgetId из запроса
        if (!isset($element->widgetId)) {
            $element->widgetId = $widgetId;
        }
        // Обработка текстового поиска
        $textsearch = isset($_POST['textsearch']) ? $_POST['textsearch'] : '';
        if (empty($textsearch)) {
            WidgetUtils::setWidgetCookie($widgetId, 'textsearch', '');
        } else {
            WidgetUtils::setWidgetCookie($widgetId, 'textsearch', $textsearch);
            WidgetUtils::setWidgetCookie($widgetId, 'page', 1); // Сброс на первую страницу
        }
        
        $state = WidgetUtils::getWidgetState($widgetId);
        $data = prepareWidgetData($this, $componentObj, $element, $state, $parentId);
        $jsInclude = array();
        eval("\$html = {$element->type}::renderList(\$this, \$componentObj, null, \$element, \$data, \$jsInclude, false, \$elementPath);");
        // Обрабатываем JS файлы из $jsInclude
        $jsFiles = array();
        foreach ($jsInclude as $jInclude) {
            if (is_array($jInclude)) {
                foreach ($jInclude as $jI) {
                    if ($jI && !in_array($jI, $jsFiles)) {
                        $jsFiles[] = $jI;
                    }
                }
            } else {
                if ($jInclude && !in_array($jInclude, $jsFiles)) {
                    $jsFiles[] = $jInclude;
                }
            }
        }
        $jsFiles = array_unique($jsFiles);
        // Если это вложенный виджет и есть parent_id, добавляем атрибут в HTML
        if (count($elementPath) > 1 && $parentId) {
            $html = preg_replace(
                '/(<div[^>]*data-widget-id="[^"]*"[^>]*)/',
                '$1 data-parent-id="' . htmlspecialchars($parentId) . '"',
                $html,
                1
            );
        }
        header('Content-Type: application/json');
        echo json_encode(array('html' => $html, 'jsFiles' => $jsFiles));
        break;
        
    default:
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(array('error' => 'Unknown action'));
        exit;
}

// Функция подготовки данных для виджета (аналогично prepareData.php, но использует cookies)
function prepareWidgetData($application, $component, $element, $state, $parentId = null) {
    // Отладочный вывод (можно убрать после проверки)
    // error_log("prepareWidgetData: element=" . $element->name . ", page=" . ($state['page'] ?? 1));
    $data = array();
    
    // Создаем объект для данных элемента
    $elementData = new stdClass();
    $elementData->name = "rootGlobalElement";
    $data[$elementData->name] = $elementData;
    
    // Определяем драйвер для элемента
    if (isset($element->table) && $element->table) {
        $TElement = $application->compDriver($element->table);
    } else {
        $TElement = $application->compDriver($element->name);
    }
    
    // Получаем данные из состояния
    $page = isset($state['page']) ? intval($state['page']) : 1;
    $filters = isset($state['filters']) ? $state['filters'] : array();
    $frontFilters = isset($state['frontFilters']) ? $state['frontFilters'] : array();
    $textsearch = isset($state['textsearch']) ? $state['textsearch'] : '';
    
    if (isset($element->query) && $element->query) {
        // Кастомный SQL запрос
        if ($parentId) {
            $sql = str_replace("#VALUE#", $parentId, $element->query);
        } else {
            $sql = $element->query;
        }
        
        // Применяем текстовый поиск
        $where2 = "";
        if ($textsearch && isset($element->textsearch)) {
            foreach ($element->elements as $elm) {
                if (in_array($elm->name, $element->textsearch)) {
                    if ($where2) {
                        $where2 .= " OR ";
                    }
                    eval("\$where2 .= {$elm->type}::prepareFilter(\$component, \$element, \$elm, \$textsearch);");
                }
            }
            if (in_array("#ID#", $element->textsearch)) {
                if ($where2) {
                    $where2 .= " OR ";
                }
                $where2 .= $TElement->getIdName() . " = '$textsearch'";
            }
        }
        
        if ($where2) {
            $sql = "select * from ($sql) a where ($where2)";
        }
        
        $orderBy = "";
        if (isset($element->order) && $element->order) {
            $orderBy = " ORDER BY " . $element->order;
        }
        if (isset($element->sort) && $element->sort) {
            $orderBy = " ORDER BY " . $element->sort;
        }
        
        if (isset($element->limit) && $element->limit) {
            $limit = $element->limit;
            $pagePanel = "";
            $perPage = $limit;
            $curpage = 1;
        } else {
            $curpage = $page;
            $perPage = 30;
            $limit = ($curpage - 1) * $perPage;
            $limit = "$limit, $perPage";
            $totalObj = $TElement->getBySql("SELECT count(*) as total FROM ($sql) d");
            ob_start();
            application::renderPagePanel($totalObj->total, $curpage, $perPage, "#", $element->name, $element->name);
            $pagePanel = ob_get_clean();
        }
        
        $elementData->{$element->name . "pagePanel"} = $pagePanel;
        $elementData->{$element->name . "offset"} = ($curpage - 1) * $perPage;
        $elementData->{$element->name} = $TElement->getListBySql($sql . " $orderBy LIMIT $limit");
    } else {
        // Обычный запрос к таблице
        if (!isset($element->order)) {
            $element->order = "";
        }
        
        $where = "";
        // Используем parent_id только если элемент не является корневым
        // (аналогично prepareData.php: if ($element->name != "rootGlobalElement"))
        if ($parentId && $element->name != "rootGlobalElement") {
            $parent_id = $TElement->getParentIdName();
            $where = "$parent_id = " . intval($parentId);
        }
        
        $orderBy = "";
        if (isset($element->order) && $element->order) {
            $orderBy = " ORDER BY " . $element->order;
        }
        if (isset($element->sort) && $element->sort) {
            $orderBy = " ORDER BY " . $element->sort;
        }
        
        // Применяем фильтры
        foreach ($element->elements as $elm) {
            if (isset($filters[$elm->name]) && $filters[$elm->name]) {
                $filterValue = $filters[$elm->name];
                if ($where) {
                    $where .= " AND ";
                }
                eval("\$where .= {$elm->type}::prepareFilter(\$component, \$element, \$elm, \$filterValue);");
            }
        }
        
        // Применяем фронтальные фильтры
        foreach ($element->elements as $elm) {
            if (isset($frontFilters[$elm->name]) && $frontFilters[$elm->name]) {
                $filterValue = $frontFilters[$elm->name];
                if ($where) {
                    $where .= " AND ";
                }
                eval("\$where .= {$elm->type}::prepareFilter(\$component, \$element, \$elm, \$filterValue);");
            }
        }
        
        // Применяем текстовый поиск
        if ($textsearch && isset($element->textsearch)) {
            $curWhere = "";
            foreach ($element->elements as $elm) {
                if (in_array($elm->name, $element->textsearch)) {
                    if ($curWhere) {
                        $curWhere .= " OR ";
                    }
                    eval("\$curWhere .= {$elm->type}::prepareFilter(\$component, \$element, \$elm, \$textsearch);");
                }
            }
            if (in_array("#ID#", $element->textsearch)) {
                if ($curWhere) {
                    $curWhere .= " OR ";
                }
                $curWhere .= $TElement->getIdName() . " = '$textsearch'";
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
        
        if (isset($element->limit) && $element->limit) {
            $limit = $element->limit;
            $pagePanel = "";
            $perPage = $limit;
            $curpage = 1;
        } else {
            $curpage = $page;
            $perPage = 30;
            $limit = ($curpage - 1) * $perPage;
            $limit = "$limit, $perPage";
            $totalObj = $TElement->getBySql("SELECT count(*) as total FROM {$TElement->tblname} $where");
            ob_start();
            application::renderPagePanel($totalObj->total, $curpage, $perPage, "#", $element->name, $element->name);
            $pagePanel = ob_get_clean();
        }
        
        eval("\$singleLine = {$element->type}::hasSingleLine();");
        if ($singleLine) {
            $elementData->{$element->name} = $TElement->getBySql("SELECT * FROM {$TElement->tblname} $where");
        } else {
            $elementData->{$element->name . "pagePanel"} = $pagePanel;
            $elementData->{$element->name . "offset"} = ($curpage - 1) * $perPage;
            $sqlQuery = "SELECT * FROM {$TElement->tblname} $where $orderBy LIMIT $limit";
            $elementData->{$element->name} = $TElement->getListBySql($sqlQuery);
            // Отладочный вывод (можно убрать после проверки)
            // error_log("prepareWidgetData SQL: " . $sqlQuery);
            // error_log("prepareWidgetData result count: " . (is_array($elementData->{$element->name}) ? count($elementData->{$element->name}) : 0));
        }
    }
    
    return $data;
}
