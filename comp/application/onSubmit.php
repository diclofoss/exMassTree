<?php

// Роутинг виджетных запросов
if (isset($_GET['widgetAction'])) {
    include(__DIR__ . "/widgetApi.php");
    return;
}

// Обработка AJAX загрузки/удаления файлов
if (isset($_GET['fileAction'])) {
    $action = $_GET['fileAction'];
    $category = isset($_GET['category']) ? $_GET['category'] : '';
    $componentName = isset($_GET['component']) ? $_GET['component'] : '';
    $elementName = isset($_GET['element']) ? $_GET['element'] : '';
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    
    if (!$category || !$componentName || !$elementName) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(array('error' => 'Missing required parameters'));
        exit;
    }
    
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
    
    // Находим элемент
    $element = utils::findElement($componentObj->elements, $elementName);
    if (!$element || $element->type != 'fileEl') {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(array('error' => 'Element not found or not a file element'));
        exit;
    }
    
    // Находим родительский элемент для определения таблицы
    $parentElement = utils::findParentElement($componentObj->elements, $elementName);
    if ($parentElement) {
        // Если элемент вложенный, используем таблицу родительского элемента
        $tableName = isset($parentElement->table) && $parentElement->table ? $parentElement->table : $parentElement->name;
    } else {
        // Если элемент корневой, используем его собственную таблицу или имя
        $tableName = isset($element->table) && $element->table ? $element->table : $element->name;
    }
    
    header('Content-Type: application/json');
    
    if ($action == 'upload') {
        // Загрузка файла
        $fileInputName = 'file_upload_' . $elementName;
        if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(array('error' => 'File upload failed'));
            exit;
        }
        
        $path_parts = pathinfo($_FILES[$fileInputName]['name']);
        $postfix = isset($path_parts['extension']) ? $path_parts['extension'] : '';
        
        $isEditMode = ($id !== null);
        
        if ($isEditMode) {
            // Режим edit - сохраняем сразу в постоянную директорию и обновляем БД
            $TElement = $this->compDriver($tableName);
            $obj = $TElement->get($id);
            $oldValue = isset($obj->{$elementName}) ? $obj->{$elementName} : '';
            
            // Удаляем старый файл, если есть
            if ($oldValue && file_exists($_SERVER['DOCUMENT_ROOT'] . $oldValue)) {
                unlink($_SERVER['DOCUMENT_ROOT'] . $oldValue);
            }
            
            // Генерируем путь для нового файла
            $filePath = "/" . $componentName . "/" . date("Y-m-d") . "/" . time() . rand(0, 1000) . "." . $postfix;
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $filePath;
            
            // Создаем директорию, если нужно
            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            // Перемещаем файл
            if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $fullPath)) {
                // Обновляем БД - используем имя элемента как имя поля
                $updateResult = $TElement->updateFields($id, array($elementName => $filePath));
                if ($updateResult !== 0) {
                    http_response_code(500);
                    echo json_encode(array('error' => 'Failed to update database'));
                    exit;
                }
                echo json_encode(array('success' => true, 'filePath' => $filePath, 'fileName' => $_FILES[$fileInputName]['name']));
            } else {
                http_response_code(500);
                echo json_encode(array('error' => 'Failed to move uploaded file'));
            }
        } else {
            // Режим add - сохраняем во временную директорию
            $tempDir = "/tmp/" . $componentName . "/" . session_id();
            if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $tempDir)) {
                mkdir($_SERVER['DOCUMENT_ROOT'] . $tempDir, 0755, true);
            }
            
            $fileName = time() . rand(0, 1000) . "." . $postfix;
            $filePath = $tempDir . "/" . $fileName;
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $filePath;
            
            if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $fullPath)) {
                // Сохраняем информацию о временном файле в сессии
                if (!isset($_SESSION['temp_files'])) {
                    $_SESSION['temp_files'] = array();
                }
                if (!isset($_SESSION['temp_files'][$elementName])) {
                    $_SESSION['temp_files'][$elementName] = array();
                }
                $_SESSION['temp_files'][$elementName]['path'] = $filePath;
                $_SESSION['temp_files'][$elementName]['originalName'] = $_FILES[$fileInputName]['name'];
                echo json_encode(array('success' => true, 'filePath' => $filePath, 'fileName' => $_FILES[$fileInputName]['name'], 'temp' => true));
            } else {
                http_response_code(500);
                echo json_encode(array('error' => 'Failed to move uploaded file'));
            }
        }
    } else if ($action == 'delete') {
        // Удаление файла
        $isEditMode = ($id !== null);
        
        if ($isEditMode) {
            // Режим edit - удаляем файл и обновляем БД
            $TElement = $this->compDriver($tableName);
            $obj = $TElement->get($id);
            $filePath = isset($obj->{$elementName}) ? $obj->{$elementName} : '';
            
            if ($filePath && file_exists($_SERVER['DOCUMENT_ROOT'] . $filePath)) {
                unlink($_SERVER['DOCUMENT_ROOT'] . $filePath);
            }
            
            // Обновляем БД - используем имя элемента как имя поля
            $updateResult = $TElement->updateFields($id, array($elementName => ''));
            if ($updateResult !== 0) {
                http_response_code(500);
                echo json_encode(array('error' => 'Failed to update database'));
                exit;
            }
            echo json_encode(array('success' => true));
        } else {
            // Режим add - удаляем временный файл
            if (isset($_SESSION['temp_files'][$elementName]['path'])) {
                $tempPath = $_SESSION['temp_files'][$elementName]['path'];
                if (file_exists($_SERVER['DOCUMENT_ROOT'] . $tempPath)) {
                    unlink($_SERVER['DOCUMENT_ROOT'] . $tempPath);
                }
                unset($_SESSION['temp_files'][$elementName]);
            }
            echo json_encode(array('success' => true));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('error' => 'Invalid action'));
    }
    exit;
}

$TAdminAction = $this->compDriver("a_actionlog");
if (isset($_GET['login'])) {
    $this->status = $this->auth->login($this);
    if ($this->status == 200) {
        $TAdminAction->createByFields(array("username" => $this->auth->getLogin(), "action" => 0));
        // Возвращаем на сохраненный URL или на главную
        if (isset($_SESSION['return_url']) && $_SESSION['return_url']) {
            $this->redirect = $this->dirName . $_SESSION['return_url'];
            unset($_SESSION['return_url']); // Удаляем после использования
        } else {
            $this->redirect = $this->dirName . "/";
        }
    }
    return;
}

if (isset($_GET['admin'])) {
    $this->status = $this->onSubmitAdmin();
    return;
}

if (isset($_GET['personal'])) {
    $this->status = $this->onSubmitPersonal();
    return;
}

if (isset($_GET['filter'])) {
    if (!isset($_SESSION['filter'])) {
        $_SESSION['filter'] = array();
        if (!isset($_SESSION['filter'][$_POST['component']])) {
            $_SESSION['filter'][$_POST['component']] = array();
        }
        if (!isset($_SESSION['filter'][$_POST['component']][$_POST['parentElement']])) {
            $_SESSION['filter'][$_POST['component']][$_POST['parentElement']] = array();
        }
    }
    if (isset($_POST['clearFilter'])) {
        unset($_SESSION['filter'][$_POST['component']][$_POST['parentElement']][$_POST['element']]);
        return;
    }
    $_SESSION['filter'][$_POST['component']][$_POST['parentElement']][$_POST['element']] = $_POST['data'];
    return;
}

if (isset($_GET['frontFilter'])) {
    foreach ($_POST as $var => $val) {
        if (!preg_match("/(.*)FrontFilter/", $var, $matches)) {
            continue;
        }
        if (!isset($_SESSION['filter'])) {
            $_SESSION['filter'] = array();
            if (!isset($_SESSION['filter'][$_POST['component']])) {
                $_SESSION['filter'][$_POST['component']] = array();
            }
            if (!isset($_SESSION['filter'][$_POST['component']][$_POST['parentElement']])) {
                $_SESSION['filter'][$_POST['component']][$_POST['parentElement']] = array();
            }
        }
        if (isset($_POST['clearFilter'])) {
            unset($_SESSION['filter'][$_POST['component']][$_POST['parentElement']][$_POST['element']]);
            return;
        }
        $_SESSION['filter'][$_POST['component']][$_POST['parentElement']][$matches[1]] = $val;
    }
    return;
}

if (isset($_GET['textsearch'])) {
    if (!isset($_POST['textsearch'])) {
        $this->status = 502;
        return;
    }
    if (!isset($_SESSION['filter'])) {
        $_SESSION['filter'] = array();
        if (!isset($_SESSION['filter'][$_POST['component']])) {
            $_SESSION['filter'][$_POST['component']] = array();
        }
        if (!isset($_SESSION['filter'][$_POST['component']][$_POST['parentElement']])) {
            $_SESSION['filter'][$_POST['component']][$_POST['parentElement']] = array();
        }
    }
    if (!$_POST['textsearch']) {
        unset($_SESSION['filter'][$_POST['component']][$_POST['parentElement']]['textsearch']);
        return;
    }
    $_SESSION['filter'][$_POST['component']][$_POST['parentElement']]['textsearch'] = $_POST['textsearch'];
    return;
}


if (isset($_GET['pagination'])) {
    if (!isset($_SESSION['pages'][$_GET['parentElement']])) {
        $_SESSION['pages'][$_GET['parentElement']] = array();
    }
    $_SESSION['pages'][$_GET['parentElement']][$_GET['element']] = $_GET['pagination'];
    print json_encode(array("result" => true));
    return;
}
$category = "";
$component = "";
$elementList = array();
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
            if (isset($_GET['actionElement'])) {
                $rootElement = isset($_GET['rootElement']) ? $_GET['rootElement'] : (isset($_GET['element']) ? $_GET['element'] : "");
                $elementList = array(utils::findElementInContext($curComponent->elements, $_GET['actionElement'], $rootElement));
            } else {
                if (isset($_GET['element'])) {
                    $elementList = array(utils::findElement($curComponent->elements, $_GET['element']));
                } else {
                    $elementList = $curComponent->elements;
                }
            }
        }
    }
}

if (!$elementList) {
    $this->status = 404;
    return;
}
foreach ($elementList as $element) {
    eval("\$singleLine = {$element->type}::hasSingleLine();");
    if ($singleLine) {
        $_GET['id'] = 1;
        $_GET['action'] = "edit";
    } else {
        if (!isset($_GET['action'])) {
            continue;
        }
    }
    if (!isset($_GET['id']) && in_array($_GET['action'], array("edit", "delete"))) {
        $this->status = 404;
        return;
    }

    $TElement = $this->compDriver($element->name);
    if ($_GET['action'] == "add") {
        foreach ($element->elements as $curElement) {
            if (isset($_POST[$curElement->name])) {
                eval("\$_POST[\$curElement->name] = {$curElement->type}::prepareDataForSave(\$component, \$curElement, \$_POST[\$curElement->name]);");
                if ($_POST[$curElement->name] == "#UNSET_DATA#") {
                    unset($_POST[$curElement->name]);
                }
            }
        }
        // Проверяем parent ID: сначала в POST (скрытое поле), потом в GET parent_id, потом в GET id
        $parentIdName = $TElement->getParentIdName();
        $parentIdValue = null; // Сохраняем для использования в редиректе
        if ($parentIdName) {
            // Проверяем, может быть уже установлен в POST
            if (isset($_POST[$parentIdName]) && $_POST[$parentIdName]) {
                $parentIdValue = $_POST[$parentIdName];
            } elseif (isset($_GET['parent_id']) && $_GET['parent_id']) {
                $parentIdValue = $_GET['parent_id'];
                $_POST[$parentIdName] = $parentIdValue;
            } elseif (isset($_GET['id']) && $_GET['id']) {
                $parentIdValue = $_GET['id'];
                $_POST[$parentIdName] = $parentIdValue;
            }
        }
        $id = $TElement->createByPost();
        if (isset($element->sort) && $element->sort) {
            $TElement->updateFields($id, array($element->sort => $id));
        }
        if (!$id) {
            $this->status = 502;
            return;
        }
        foreach ($element->elements as $curElement) {
            if (isset($_POST[$curElement->name])) {
                eval("{$curElement->type}::prepareDataAfterSave(\$component, \$curElement, \$_POST[\$curElement->name]);");
            }
        }
        $application = $this;
        if (isset($element->afterUpdate) && $element->afterUpdate) {
            include($element->afterUpdate);
        }
        $TAdminAction->createByFields(array("username" => $this->auth->getName(), "action" => 1, "element" => $element->name, "elementId" => $id));
        $redirectUrl = $this->path[count($this->path) - 2][1];
        // Сохраняем parent ID в редиректе, если он был в исходном запросе
        if ($parentIdValue) {
            $redirectUrl .= (strpos($redirectUrl, '?') !== false ? '&' : '?') . 'id=' . urlencode($parentIdValue);
        }
        $this->redirect = $redirectUrl;
        return;
    } else if ($_GET['action'] == "delete") {
        $code = dropElement($this, $element, $_GET['id']);
        if ($code) {
            $this->status = 502;
            return;
        }
        $TAdminAction->createByFields(array("username" => $this->auth->getName(), "action" => 3, "element" => $element->name, "elementId" => $_GET['id']));
        $this->redirect = $this->path[count($this->path) - 2][1];
        return;
    } else if ($_GET['action'] == "edit") {
        updateData($this, $element, $component, $TElement, $TAdminAction, $singleLine, $_GET['id']);
        return;
    } else if (isset($_GET['action'])) {
        eval($element->type . "::" . $_GET['action'] . "(\$this, \$element);");
    }
}

function updateData($application, $element, $component, $TElement, $TAdminAction, $singleLine, $id) {
    foreach ($element->elements as $curElement) {
        if ($curElement->type == "fieldset") {
            continue;
        }
        if (isset($_POST[$curElement->name])) {
            eval("\$_POST[\$curElement->name] = {$curElement->type}::prepareDataForSave(\$component, \$curElement, \$_POST[\$curElement->name]);");
            if ($_POST[$curElement->name] == "#UNSET_DATA#") {
                unset($_POST[$curElement->name]);
            }
        } else {
            eval("\$nopostval = {$curElement->type}::getNoPostValue();");
            if ($nopostval != "") {
                $_POST[$curElement->name] = $nopostval;
            }
        }
    }
    $obj = $TElement->get($id);
    $code = $TElement->updateByPost($id);
    if ($code) {
        $application->status = 502;
        return;
    }
    foreach ($element->elements as $curElement) {
        if (isset($_POST[$curElement->name])) {
            eval("{$curElement->type}::prepareDataAfterSave(\$component, \$curElement, \$_POST[\$curElement->name]);");
        }
    }
    if (isset($element->afterUpdate) && $element->afterUpdate) {
        include($element->afterUpdate);
    }

    $dataBefore = "";
    $dataAfter = "";
    foreach ($_POST as $var => $val) {
        if (!isset($obj->{$var})) {
            continue;
        }
        if ($val == $obj->{$var}) {
            continue;
        }
        if ($dataBefore) {
            $dataBefore .= "; ";
            $dataAfter .= "; ";
        }
        $dataBefore .= $var . "=" . $obj->{$var};
        $dataAfter .= $var . "=" . $val;
    }

    $col = 200;
    $dataBefore = strip_tags($dataBefore);
    if (strlen($dataBefore) > $col) {
        $dataBefore = substr($dataBefore, 0, $col);
        $dataBefore = preg_replace("/([^ ]+)$/", "", $dataBefore);
        $dataBefore = substr($dataBefore, 0, strlen($dataBefore) - 1) . "...";
    }

    $dataAfter = strip_tags($dataAfter);
    if (strlen($dataAfter) > $col) {
        $dataAfter = substr($dataAfter, 0, $col);
        $dataAfter = preg_replace("/([^ ]+)$/", "", $dataAfter);
        $dataAfter = substr($dataAfter, 0, strlen($dataAfter) - 1) . "...";
    }

    if ($dataAfter) {
        $TAdminAction->createByFields(array("username" => $application->auth->getName(), "element" => $element->name, "elementId" => $id, "action" => 2, "dataBefore" => $dataBefore, "dataAfter" => $dataAfter));
    }

    if ($singleLine) {
        
    } else {
        $application->redirect = $application->path[count($application->path) - 2][1];
    }

    foreach ($element->elements as $curElement) {
        if ($curElement->type == "fieldset") {
            continue;
        }
        eval("\$singleLine = {$curElement->type}::hasSingleLine();");
        if ($singleLine) {
            $TElement = $application->compDriver($curElement->name);
            $elementData = $TElement->getList($id);
            if (!$elementData) {
                continue;
            }
            updateData($application, $curElement, $component, $TElement, $TAdminAction, $singleLine, $elementData[0]->{$TElement->getIdName()});
        }
    }

    return;
}

function dropElement($application, $element, $id) {
    if (isset($element->elements)) {
        foreach ($element->elements as $curElement) {
            if ($curElement->type == "fieldset") {
                continue;
            }
            eval("\$isTable = {$curElement->type}::isTable();");
            if (!$isTable) {
                continue;
            }
            $TElement = $application->compDriver($curElement->name);
            $listId = $TElement->getList($id);
            foreach ($listId as $curId) {
                $idName = $TElement::$idName;
                $code = dropElement($application, $curElement, $curId->{$idName});
                if ($code) {
                    return $code;
                }
            }
        }
    }
    $TElement = $application->compDriver($element->name);
    $code = $TElement->delete($id);
    return 0;
}
