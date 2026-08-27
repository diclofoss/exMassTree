<?php

class button implements item {

    public static function getDataType($element) {
        return "";
    }

    public static function getJs() {
        return "js/components/button.js";
    }

    public static function getNoPostValue() {
        return "";
    }

    public static function hasSingleLine() {
        return false;
    }

    public static function isNull($application) {
        return false;
    }

    public static function isTable() {
        return false;
    }

    public static function prepareDataAfterSave($component, $element, $value) {
        
    }

    public static function prepareDataForSave($component, $element, $value) {
        
    }

    public static function prepareFilter($component, $parentElement, $element, $value) {
        
    }

    public static function render($application, $component, $element, $data, &$jsInclude) {
        ob_start();
        include("templates/components/button.php");
        return ob_get_clean();
    }

    public static function renderFilter($component, $parentElement, $element, $data, &$jsInclude) {
        
    }

    public static function renderFrontFilter($component, $parentElement, $element, $data, &$jsInclude) {
        
    }

    public static function renderList($application, $component, $parentElement, $element, $data, &$jsInclude, $hasParent = false, $elementPath = null) {
        // ВАЖНО: Добавляем JS файл в список для подключения
        $jsFile = self::getJs();
        if ($jsFile) {
            if (!in_array($jsFile, $jsInclude)) {
                $jsInclude[] = $jsFile;
            }
        }
        
        // Получаем имя таблицы родительского элемента
        $tableName = isset($parentElement->table) && $parentElement->table ? $parentElement->table : $parentElement->name;
        
        $TElement = $application->compDriver($tableName);
        $idName = $TElement->getIdName();
        
        // Получаем ID текущей строки из данных
        $rowId = isset($data->{$idName}) ? $data->{$idName} : null;
        
        if (!$rowId) {
            return "";
        }
        
        // Рендерим кнопку с ID из данных используя отдельный шаблон для списка
        ob_start();
        include("templates/components/buttonList.php");
        return ob_get_clean();
    }

    public static function getCss() {
        return "";
    }

    public static function urlExec($application, $element) {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
        }
        if (isset($element->url)) {
            include($element->url);
        }
    }

    public static function acyncExec($application, $element) {
        if (isset($_POST['id'])) {
            $id = $_POST['id'];
        } else if (isset($_GET['id'])) {
            $id = $_GET['id'];
        }
        $inputData = array();
        $fileInputData = array();
        if (isset($element->input) && $element->input) {
            if (isset($_POST['inputData']) && is_array($_POST['inputData'])) {
                foreach ($_POST['inputData'] as $var => $val) {
                    if ($var == "id") {
                        continue;
                    }
                    if (preg_match("/^file_upload/", $var)) {
                        continue;
                    }
                    $var = str_replace("_" . $element->name, "", $var);
                    $inputData[$var] = $val;
                }
            } else {
                foreach ($_POST as $var => $val) {
                    if ($var == "id") {
                        continue;
                    }
                    if (preg_match("/^file_upload/", $var)) {
                        continue;
                    }
                    $var = str_replace("_" . $element->name, "", $var);
                    $inputData[$var] = $val;
                }
            }
            foreach ($_FILES as $file) {
                $fileInputData[] = $file;
            }
        }
        if (isset($element->action)) {
            include($element->action);
        } else if (isset($element->url)) {
            $urlStr = "";
            foreach ($inputData as $var => $val) {
                if ($urlStr) {
                    $urlStr .= "&";
                }
                $urlStr .= "$var=$val";
            }
            if (isset($id)) {
                if ($urlStr) {
                    $urlStr .= "&";
                }
                $urlStr .= "id=$id";
            }
            print json_encode(array("url" => $application->dirName . "/?category=" . $_GET['category'] . "&component=" . $_GET['component'] . "&action=urlExec&element=" . $_GET['element'] . "&$urlStr"));
        } else {
            print json_encode(array("response" => "Сообщение отправлено"));
        }
    }

}
