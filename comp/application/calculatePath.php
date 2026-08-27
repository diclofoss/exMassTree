<?php

if (!function_exists('findPathElement')) {
    function findPathElement($application, $category, $component, $element, $id, $recured = false) {
        $dirName = $application->dirName;
        if (isset($_GET['action']) && $_GET['action'] == "add") {
            $pathElementList = array();
            $parentElement = utils::findParentElement($component->elements, $element->name);
            if ($parentElement) {
                $pathElementList = findPathElement($application, $category, $component, $parentElement, $id, true);
            }
            if ($recured) {
                return array_merge($pathElementList, array(array($element->caption, $dirName . "/?category=" . $category->name . "&component=" . $component->name . "&action=edit&element=" . $element->name . "&id=" . $id)));
            } else {
                return array_merge($pathElementList, array(array($element->caption, $dirName . "/?category=" . $category->name . "&component=" . $component->name . "&element=" . $element->name)));
            }
        } else {
            $TElement = $application->compDriver($element->name);
            $elementData = $TElement->get($id);
            $parentId = $TElement->getParentId($elementData);
            $pathElementList = array();
            if ($parentId) {
                $parentElement = utils::findParentElement($component->elements, $element->name);
                if ($parentElement) {
                    $pathElementList = findPathElement($application, $category, $component, $parentElement, $parentId, true);
                }
            }
            return array_merge($pathElementList, array(array(
                    $element->caption,
                    $dirName . "/?category=" . $category->name . "&component=" . $component->name . "&action=edit&element=" . $element->name . "&id=" . $id)
            ));
        }
    }
}

$component = "";
$category = "";
$path = array();

if (isset($_GET['admin'])) {
    return array("Администрирование", $this->dirName . "/?admin");
}

if (!isset($_GET['category'])) {
    return array();
}

foreach ($this->config->categories as $curCategory) {
    if ($curCategory->name != $_GET['category']) {
        continue;
    }

    $path[] = array($curCategory->title, $this->dirName . "/?category=" . $curCategory->name);
    if (isset($_GET['component'])) {
        foreach ($curCategory->components as $curComponent) {
            if ($curComponent->name != $_GET['component']) {
                continue;
            }
            $path[] = array($curComponent->title, $this->dirName . "/?category=" . $curCategory->name . "&component=" . $curComponent->name);
            $component = $curComponent;
            if (isset($_GET['element'])) {
                $element = utils::findElement($component->elements, $_GET['element']);
                if (!$element) {
                    return;
                }
                $pathElementList = findPathElement($this, $curCategory, $curComponent, $element, isset($_GET['id']) ? $_GET['id'] : "");
                if (!$pathElementList) {
                    return;
                }
                $path = array_merge($path, $pathElementList);
            }
        }
    }
}
