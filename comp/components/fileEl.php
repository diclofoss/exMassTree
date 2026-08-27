<?php

class fileEl implements item {

    public static function getDataType($element) {
        return "varchar(90)";
    }

    public static function getJs() {
        return "js/components/file.js";
    }

    public static function isNull($application) {
        return false;
    }

    public static function isTable() {
        return false;
    }

    public static function prepareDataForSave($component, $element, $value) {
        // Проверяем, есть ли временный файл в сессии (режим add)
        if (isset($_SESSION['temp_files'][$element->name]['path'])) {
            $tempPath = $_SESSION['temp_files'][$element->name]['path'];
            $tempFullPath = $_SERVER['DOCUMENT_ROOT'] . $tempPath;
            
            if (file_exists($tempFullPath)) {
                // Перемещаем временный файл в постоянную директорию
                $path_parts = pathinfo($tempPath);
                $postfix = isset($path_parts['extension']) ? $path_parts['extension'] : '';
                
                $newPath = "/" . $component->name . "/" . date("Y-m-d") . "/" . time() . rand(0, 1000) . "." . $postfix;
                $newFullPath = $_SERVER['DOCUMENT_ROOT'] . $newPath;
                
                // Создаем директорию, если нужно
                $dir = dirname($newFullPath);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                
                // Перемещаем файл
                if (rename($tempFullPath, $newFullPath)) {
                    // Удаляем информацию о временном файле из сессии
                    unset($_SESSION['temp_files'][$element->name]);
                    return $newPath;
                }
            }
            // Если не удалось переместить, удаляем информацию о временном файле
            unset($_SESSION['temp_files'][$element->name]);
        }
        
        // Старая логика для обратной совместимости (если файл загружается через форму)
        if (isset($_POST["file_delete_" . $element->name]) && $_POST["file_delete_" . $element->name] == 'true') {
            // Удалить файл если идет команда на удаление
            if ($value && file_exists($_SERVER['DOCUMENT_ROOT'] . $value)) {
                unlink($_SERVER['DOCUMENT_ROOT'] . $value);
            }
            return "";
        } else if (isset($_FILES["file_upload_" . $element->name]['tmp_name']) && $_FILES["file_upload_" . $element->name]['tmp_name']) {
            $path_parts = pathinfo($_FILES["file_upload_" . $element->name]['name']);
            if (isset($path_parts['extension'])) {
                $postfix = $path_parts['extension'];
            } else {
                $postfix = "";
            }
            if ($value && file_exists($_SERVER['DOCUMENT_ROOT'] . $value)) {
                unlink($_SERVER['DOCUMENT_ROOT'] . $value);
            }
            $value = "/" . $component->name . "/" . date("Y-m-d") . "/" . time() . rand(0, 1000) . "." . $postfix;
            return $value;
        }
        
        // Если значение уже установлено (например, через AJAX в режиме edit), возвращаем его
        if (isset($_POST[$element->name]) && $_POST[$element->name]) {
            return $_POST[$element->name];
        }

        return "#UNSET_DATA#";
    }

    public static function prepareFilter($component, $parentElement, $element, $value) {
        return $value;
    }

    public static function render($application, $component, $element, $data, &$jsInclude) {
        ob_start();
        include("templates/components/file.php");
        return ob_get_clean();
    }

    public static function renderFilter($component, $parentElement, $element, $data, &$jsInclude) {
        
    }

    public static function renderFrontFilter($component, $parentElement, $element, $data, &$jsInclude) {
        
    }

    public static function renderList($application, $component, $parentElement, $element, $data, &$jsInclude, $hasParent = false, $elementPath = null) {
        if (!isset($data->{$element->name})) {
            return "";
        }
        return $data->{$element->name};
    }

    public static function prepareDataAfterSave($component, $element, $value) {
        // Обработка файла, загруженного через форму (старая логика для обратной совместимости)
        if (isset($_FILES["file_upload_" . $element->name]['tmp_name']) && $_FILES["file_upload_" . $element->name]['tmp_name']) {
            if (!is_dir($_SERVER['DOCUMENT_ROOT'] . "/" . $component->name . "/" . date("Y-m-d"))) {
                mkdir($_SERVER['DOCUMENT_ROOT'] . "/" . $component->name . "/" . date("Y-m-d"), 0755, true);
            }
            move_uploaded_file($_FILES["file_upload_" . $element->name]['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $value);
            return 0;
        }
        
        // Файлы, загруженные через AJAX, уже обработаны в prepareDataForSave или в API endpoint
        // Здесь ничего делать не нужно
        return 0;
    }

    public static function hasSingleLine() {
        
    }

    public static function getNoPostValue() {
        return "";
    }

    public static function getCss() {
        return "";
    }

}
