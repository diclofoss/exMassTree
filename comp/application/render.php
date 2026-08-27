<?php

$renderData = "";
$jsInclude = array();
$cssInclude = array();
$renderLeft = "";

$component = "";
$category = "";
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
        }
    }
}

if ($category && count($category->components) == 1 && (!isset($category->sqlMenu) || !$category->sqlMenu)) {
    $renderLeft = "";
} else {
    ob_start();
    if (isset($category->sqlMenu) && $category->sqlMenu) {
        include("templates/root/leftBySql.php");
    } else {
        include("templates/root/left.php");
    }
    $renderLeft = ob_get_clean();
}

if (!$component) {
    if ($category && count($category->components) == 1 && (!isset($category->sqlMenu) || !$category->sqlMenu)) {
        header("Location: /?category={$category->name}&component={$category->components[0]->name}");
    } else {
        ob_start();
        if (isset($category->sqlMenu) && $category->sqlMenu) {
            include("templates/root/categoryBySql.php");
        } else {
            include("templates/root/category.php");
        }
        $renderData = ob_get_clean();
    }
    return;
}
$element = "";
if (!isset($_GET['element'])) {
    $colsInRow = 12;
    foreach ($component->elements as $element) {
        $colsInRow -= $element->col;
        if ($colsInRow < 0 || isset($element->newrow) && $element->newrow) {
            $colsInRow = 12;
            $renderData .= "</div><div class=\"row mt-4\">";
        }
        eval("\$isTable = {$element->type}::isTable();");
        if ($isTable) {
            // Передаем null для elementPath - будет сгенерирован в renderList
            eval("\$renderData .= {$element->type}::renderList(\$this, \$component, null, \$element, \$this->data, \$jsInclude, false, null);");
        } else {
            $renderData .= "<div class=\"col-md-{$element->col}\">";
            eval("\$renderData .= {$element->type}::render(\$this, \$component, \$element, \$this->data, \$jsInclude);");
            $renderData .= "</div>";
        }
        eval("\$jsInclude[] = {$element->type}::getJs();");
        eval("\$cssInclude[] = {$element->type}::getCss();");
    }
} else {
    $element = utils::findElement($component->elements, $_GET['element']);
    if (!$element) {
        return;
    }
    eval("\$renderData .= {$element->type}::render(\$this, \$component, \$element, \$this->data, \$jsInclude);");
    eval("\$jsInclude[] = {$element->type}::getJs();");
    eval("\$cssInclude[] = {$element->type}::getCss();");
}
$jsInclude2 = array();
foreach ($jsInclude as $jInclude) {
    if (is_array($jInclude)) {
        foreach ($jInclude as $jI) {
            $jsInclude2[] = $jI;
        }
    } else {
        $jsInclude2[] = $jInclude;
    }
}
$jsInclude = array_unique($jsInclude2);
$cssInclude = array_unique($cssInclude);
