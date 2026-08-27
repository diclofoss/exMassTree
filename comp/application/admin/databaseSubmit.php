<?php

include("uninstallElement.php");
include("installElement.php");
include("fixElement.php");

$componentName = $_POST['component'];
$action = $_POST['action'];

if ($action == "install") {
    $component = "";
    foreach ($this->config->categories as $curCategory) {
        foreach ($curCategory->components as $curComponent) {
            if ($curComponent->name == $componentName) {
                $component = $curComponent;
            }
        }
    }
    if (!$component) {
        return $code = 502;
    }

    foreach ($component->elements as $element) {
        eval("\$isTable = {$element->type}::isTable();");
        if (!$isTable) {
            continue;
        }
        $TElement = $this->compDriver($element->table);
        installElement($TElement, $element);
    }

    return $code = 200;
}

if ($action == "fix") {
    $component = "";
    foreach ($this->config->categories as $curCategory) {
        foreach ($curCategory->components as $curComponent) {
            if ($curComponent->name == $componentName) {
                $component = $curComponent;
            }
        }
    }
    if (!$component) {
        return $code = 502;
    }

    foreach ($component->elements as $element) {
        eval("\$isTable = {$element->type}::isTable();");
        if (!$isTable) {
            continue;
        }
        $TElement = $this->compDriver($element->table);
        fixElement($this, $TElement, $element);
    }
    
    return $code = 200;
}

if ($action == "uninstall") {
    $component = "";
    foreach ($this->config->categories as $curCategory) {
        foreach ($curCategory->components as $curComponent) {
            if ($curComponent->name == $componentName) {
                $component = $curComponent;
            }
        }
    }
    if (!$component) {
        return $code = 502;
    }

    foreach ($component->elements as $element) {
        eval("\$isTable = {$element->type}::isTable();");
        if (!$isTable) {
            continue;
        }
        $TElement = $this->compDriver($element->table);
        uninstallElement($TElement, $element);
    }
    
    return $code = 200;
}

