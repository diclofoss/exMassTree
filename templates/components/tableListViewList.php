<div class="col-md-<?= ($hasParent) ? "12" : $element->col ?>">
    <? 
    // Проверяем, является ли это AJAX запросом для виджета (любое действие виджета)
    $isAjaxRequest = isset($_GET['widgetAction']) && in_array($_GET['widgetAction'], ['load', 'pagination', 'filter', 'frontFilter', 'search']);
    // Если есть widgetId и это НЕ AJAX запрос, показываем placeholder
    if (isset($element->widgetId) && $element->widgetId && !$isAjaxRequest) { 
        // Определяем parent_id для вложенных виджетов (для placeholder тоже нужно)
        // Вложенный виджет - когда elementPath содержит более одного элемента
        $parentIdAttr = '';
        if (isset($elementPath) && is_array($elementPath) && count($elementPath) > 1) {
            // Вложенный виджет - используем $_GET['id'] (ID текущей карточки или родительского элемента)
            $parentId = isset($_GET['id']) ? $_GET['id'] : null;
            if ($parentId) {
                $parentIdAttr = ' data-parent-id="' . htmlspecialchars($parentId) . '"';
            }
        }
    ?>
        <!-- Виджет: <?= $element->widgetId ?> - контент загрузится через AJAX -->
        <div data-widget-id="<?= $element->widgetId ?>" class="widget-container"<?= $parentIdAttr ?>>
            <!-- Placeholder со спиннером - будет заменен на контент через AJAX -->
            <div class="widget-loading">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Загрузка...</span>
                </div>
            </div>
        </div>
    <? } else { ?>
        <!-- Обычный рендеринг (с виджетом при AJAX или без виджета) -->
        <? if (isset($element->widgetId) && $element->widgetId) { 
            // Определяем parent_id для вложенных виджетов
            // Вложенный виджет - когда elementPath содержит более одного элемента
            $parentIdAttr = '';
            if (isset($elementPath) && is_array($elementPath) && count($elementPath) > 1) {
                // Вложенный виджет - используем $_GET['id'] (ID текущей карточки или родительского элемента)
                $parentId = isset($_GET['id']) ? $_GET['id'] : null;
                if ($parentId) {
                    $parentIdAttr = ' data-parent-id="' . htmlspecialchars($parentId) . '"';
                }
            }
        ?>
            <!-- Виджет с данными (вложенный элемент в режиме карточки) -->
            <div data-widget-id="<?= $element->widgetId ?>" class="widget-container"<?= $parentIdAttr ?>>
        <? } ?>
        <? if (isset($element->caption)) { ?>
        <h1 class="pb-2 mb-3 h2"><?= $element->caption ?></h1>
    <? } else { ?>
        <h1 class="pb-2 mb-3 h2"><?= $component->title ?></h1>
    <? } ?>
    <? if (isset($element->textsearch)) { 
        // Получаем значение поиска из состояния виджета (для виджетов) или из сессии (для обычных элементов)
        $textsearchValue = "";
        if (isset($element->widgetId) && $element->widgetId) {
            // Для виджетов используем состояние из cookie
            $widgetState = WidgetUtils::getWidgetState($element->widgetId);
            $textsearchValue = isset($widgetState['textsearch']) ? htmlspecialchars($widgetState['textsearch']) : "";
        } else {
            // Для обычных элементов используем сессию
            $textsearchValue = (isset($_SESSION['filter'][(isset($_GET['component']) ? $_GET['component'] : $component->name)][$element->name]['textsearch'])) ? htmlspecialchars($_SESSION['filter'][(isset($_GET['component']) ? $_GET['component'] : $component->name)][$element->name]['textsearch']) : "";
        }
    ?>
        <form class="form-inline" <? if (isset($element->widgetId) && $element->widgetId) { ?>data-textsearch="true" data-widget-id="<?= $element->widgetId ?>"<? } ?> action="<?= $config->dirName ?>/?category=<?= isset($_GET['category']) ? $_GET['category'] : '' ?>&component=<?= isset($_GET['component']) ? $_GET['component'] : $component->name ?>&element=<?= $element->name ?>&textsearch" method="POST">
            <div class="form-group mb-2">
                <label for="<?= $element->name ?>_textsearch" class="sr-only"></label>
                <input type="text" class="form-control" name="textsearch" value="<?= $textsearchValue ?>" id="<?= $element->name ?>_textsearch" placeholder="поиск" />
            </div>            
            &nbsp;
            <button type="submit" class="btn btn-primary mb-2">Поиск</button>
            <input type="hidden" name="parentElement" value="<?= $element->name ?>" />
            <input type="hidden" name="component" value="<?= $component->name ?>" />
        </form>
    <? } ?>
    <? if (isset($element->frontFilterData)) { ?>
        <form class="form-inline" <? if (isset($element->widgetId) && $element->widgetId) { ?>data-front-filter="true" data-widget-id="<?= $element->widgetId ?>"<? } ?> action="<?= $config->dirName ?>/?category=<?= isset($_GET['category']) ? $_GET['category'] : '' ?>&component=<?= isset($_GET['component']) ? $_GET['component'] : $component->name ?>&element=<?= $element->name ?>&frontFilter" method="POST">
            <? foreach ($element->frontFilterData as $filterData) { ?>
                <?= $filterData ?>
                &nbsp;
            <? } ?>
            <button type="submit" class="btn btn-primary mb-2">Поиск</button>
            <input type="hidden" name="parentElement" value="<?= $element->name ?>" />
            <input type="hidden" name="component" value="<?= $component->name ?>" />
        </form>
    <? } ?>
    <nav class="navbar navbar-expand-md navbar-light bg-light">
        <div class="collapse navbar-collapse" id="navbarsExample04">
            <ul class="navbar-nav mr-auto">
                <? if (in_array("add", $element->controll)): ?>
                    <li class="nav-item">
                        <? 
                        // Определяем parent ID для вложенных элементов
                        $parentIdForAdd = '';
                        if (isset($_GET['parent_id']) && $_GET['parent_id']) {
                            $parentIdForAdd = '&parent_id=' . htmlspecialchars($_GET['parent_id']);
                        } elseif (isset($_GET['id']) && $_GET['id']) {
                            $parentIdForAdd = '&id=' . htmlspecialchars($_GET['id']);
                        }
                        ?>
                        <a href="<?= $config->dirName ?>/?category=<?= isset($_GET['category']) ? $_GET['category'] : '' ?>&component=<?= isset($_GET['component']) ? $_GET['component'] : $component->name ?>&element=<?= $element->name ?>&action=add<?= $parentIdForAdd ?>" class="btn btn-success img-btn btn-sm"><span data-feather="plus-square"></span></a>
                    </li>
                <? endif; ?>
                <? if (isset($element->massActions) && !in_array("add", $element->controll)) { ?>
                    <? foreach ($element->massActions as $action) { ?>
                        <li class="nav-item ml-1">
                            <button type="button" refreshPage="<?= ($action->refreshPage) ? "true" : "" ?>" element="<?= $element->name ?>" goUrl="<?= $config->dirName ?>/?category=<?= isset($_GET['category']) ? $_GET['category'] : '' ?>&component=<?= isset($_GET['component']) ? $_GET['component'] : $component->name ?>&element=<?= $element->name ?>&action=massAction&dataType=json" name="<?= $action->name ?>" class="btn btn-sm btn-dark massActionBtn"><?= $action->caption ?></button>
                        </li>
                    <? } ?>
                <? } ?>
            </ul>
            <span class="navbar-text">
                <?= $curData->{$element->name . "pagePanel"} ?>
            </span>            
        </div>
    </nav>
    <? if (isset($element->filters)) { ?>
        <? foreach ($element->filterData as $filterData) { ?>
            <?= $filterData ?>
        <? } ?>
    <? } ?>
    <div class="table-responsive">
        <table category="<?= isset($_GET['category']) ? $_GET['category'] : '' ?>" offset="<?= $curData->{$element->name . "offset"} ?>" element="<?= $element->name ?>" component="<?= $component->name ?>" class="table table-striped table-sm <?= (isset($element->sort) && $element->sort) ? "tableViewSortable" : "" ?>" id="tableView_<?= $element->name ?>">
            <thead>
                <tr>
                    <? if (isset($element->massActions)) { ?>
                        <th width="30"></th>
                    <? } ?>
                    <? if (isset($element->sort)) { ?>
                        <th width="50"></th>
                    <? } ?>                    
                    <? if (!isset($element->noId) || !$element->noId) { ?>
                        <th>#</th>
                    <? } ?>
                    <? $i = 0; ?>
                    <? 
                    $widgetFilterState = null;
                    if (isset($element->widgetId) && $element->widgetId) {
                        $widgetFilterState = WidgetUtils::getWidgetState($element->widgetId);
                    }
                    $componentNameForFilter = isset($_GET['component']) ? $_GET['component'] : $component->name;
                    ?>
                    <? foreach ($element->preview as $preview): ?>
                        <? $i++ ?>
                        <? foreach ($element->elements as $curElement): ?>
                            <? if ($curElement->name == $preview): ?>
                                <? if (isset($element->filters) && in_array($curElement->name, $element->filters)) { 
                                    $filterActive = false;
                                    if ($widgetFilterState !== null) {
                                        $filterActive = isset($widgetFilterState['filters'][$curElement->name]);
                                    } else {
                                        $filterActive = isset($_SESSION['filter'][$componentNameForFilter][$element->name][$curElement->name]);
                                    }
                                ?>
                                    <th <?= ($i > 1 ) ? 'class="text-center"' : "" ?>><a href="#" <?= $filterActive ? 'style="color:red"' : "" ?> class="filterLink" parentElement="<?= $element->name ?>" element="<?= $curElement->name ?>" filterCaption="<?= $curElement->caption ?>"><?= $curElement->caption ?> <span data-feather="filter"></span></a></th>
                                <? } else { ?>
                                    <th <?= ($i > 1 ) ? 'class="text-center"' : "" ?>><?= $curElement->caption ?></th>
                                <? } ?>
                            <? endif; ?>
                        <? endforeach; ?>
                    <? endforeach; ?>
                    <? if ($element->controll) { ?>
                        <th class="text-center">Действие</th>
                    <? } ?>
                </tr>
            </thead>
            <tbody>
                <? if (isset($curData->{$element->name})) { ?>
                    <? foreach ($curData->{$element->name} as $row): ?>
                        <tr <? if (isset($element->sort)) { ?>sort="<?= $row->{$element->sort} ?>"<? } ?> data="<?= isset($row->{$config->config->database->idName}) ? $row->{$config->config->database->idName} : "" ?>">
                            <? if (isset($element->massActions)) { ?>
                                <td><input class="form-control mt-2" value="<?= isset($row->{$config->config->database->idName}) ? $row->{$config->config->database->idName} : "" ?>" type="checkbox" name="<?= $element->name . "_massAction[]" ?>"></td>
                            <? } ?>
                            <? if (isset($element->sort)) { ?>
                                <td class="align-middle">
                                    <a href="#" class="btn btn-info img-btn btn-sm sortable"><span data-feather="move"></span></a>
                                </td>
                            <? } ?>
                            <? if (!isset($element->noId) || !$element->noId) { ?>
                                <td class="align-middle">
                                    <?= $row->{$config->config->database->idName} ?>
                                </td>
                            <? } ?>
                            <? $i = 0; ?>
                            <? foreach ($element->preview as $preview): ?>
                                <? $i++ ?>
                                <? foreach ($element->elements as $curElement): ?>
                                    <? if ($curElement->name == $preview): ?>
                                        <td <?= ($i > 1 ) ? 'class="text-center align-middle"' : 'class="align-middle"' ?>><? 
                                            // Передаем elementPath для вложенных элементов
                                            // Вложенный элемент всегда должен иметь elementPath, даже если родитель не виджет
                                            $nestedElementPath = null;
                                            
                                            // Строим путь для вложенного элемента
                                            // $elementPath содержит путь до родительского элемента (если есть)
                                            // Имя текущего элемента ($curElement->name) будет добавлено в generateWidgetIdForElement
                                            
                                            if (isset($elementPath) && is_array($elementPath) && count($elementPath) > 0) {
                                                // Используем существующий elementPath (путь до родителя)
                                                // Имя $curElement будет добавлено в generateWidgetIdForElement
                                                $nestedElementPath = $elementPath;
                                            } else if (isset($element->widgetId) && $element->widgetId) {
                                                // Если родительский элемент - виджет, извлекаем его путь
                                                $pathInfo = WidgetUtils::parseWidgetId($element->widgetId);
                                                if ($pathInfo && isset($pathInfo['elementPath']) && is_array($pathInfo['elementPath'])) {
                                                    // Используем путь родительского элемента
                                                    // Имя $curElement будет добавлено в generateWidgetIdForElement
                                                    $nestedElementPath = $pathInfo['elementPath'];
                                                } else {
                                                    // Если не удалось распарсить, строим путь из текущего элемента
                                                    $nestedElementPath = array($element->name);
                                                    // Имя $curElement будет добавлено в generateWidgetIdForElement
                                                }
                                            } else {
                                                // Если родительский элемент не виджет, но мы в режиме карточки,
                                                // все равно создаем путь для вложенного элемента, чтобы он стал виджетом
                                                $isCardMode = (isset($_GET['action']) && ($_GET['action'] == 'edit' || $_GET['action'] == 'add'));
                                                if ($isCardMode) {
                                                    // В режиме карточки вложенные элементы должны быть виджетами
                                                    // Путь должен содержать полный путь до родительского элемента
                                                    // Вычисляем полный путь к текущему элементу из конфигурации
                                                    $currentElementPath = utils::findElementPath($component->elements, $element->name);
                                                    if ($currentElementPath) {
                                                        $nestedElementPath = $currentElementPath;
                                                    } else {
                                                        // Если не удалось найти полный путь, используем имя элемента как fallback
                                                        $nestedElementPath = array($element->name);
                                                    }
                                                    // Имя $curElement будет добавлено в generateWidgetIdForElement
                                                }
                                            }
                                            
                                            // Для вложенных элементов внутри строк таблицы нужно передать ID строки
                                            // Сохраняем его в переменной для передачи в renderList
                                            $rowParentId = isset($row->{$config->config->database->idName}) ? $row->{$config->config->database->idName} : null;
                                            
                                            eval("\$renderVal = {$curElement->type}::renderList(\$config, \$component, \$element, \$curElement, \$row, \$jsInclude, true, \$nestedElementPath);"); 
                                            
                                            // Добавляем JS файлы для вложенных элементов
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
                                            
                                            // Если это вложенный виджет внутри строки, добавляем data-parent-id
                                            if ($nestedElementPath !== null && $rowParentId !== null) {
                                                // Ищем виджет-контейнер в сгенерированном HTML и добавляем data-parent-id
                                                $renderVal = preg_replace(
                                                    '/(<div[^>]*data-widget-id="[^"]*"[^>]*)/',
                                                    '$1 data-parent-id="' . htmlspecialchars($rowParentId) . '"',
                                                    $renderVal,
                                                    1
                                                );
                                            }
                                        ?><?= $renderVal ?></td>
                                    <? endif; ?>
                                <? endforeach; ?>
                            <? endforeach; ?>
                            <? if ($element->controll) { ?>
                                <td class="align-middle text-center" style="white-space: nowrap">
                                    <? if (in_array("edit", $element->controll)): ?>
                                        <a href="<?= $config->dirName ?>/?category=<?= isset($_GET['category']) ? $_GET['category'] : '' ?>&component=<?= isset($_GET['component']) ? $_GET['component'] : $component->name ?>&element=<?= $element->name ?>&id=<?= $row->{$config->config->database->idName} ?>&action=edit&parent_id=<?= isset($_GET['id']) ? $_GET['id'] : "" ?>" class="btn btn-info img-btn btn-sm"><span data-feather="edit-2"></span></a>
                                    <? endif; ?>
                                    <? if (in_array("delete", $element->controll)): ?>
                                        <a onclick="return window.confirm('Вы действительно хотите удалить?');" class="btn btn-danger img-btn btn-sm" href="<?= $config->dirName ?>/?category=<?= isset($_GET['category']) ? $_GET['category'] : '' ?>&component=<?= isset($_GET['component']) ? $_GET['component'] : $component->name ?>&element=<?= (isset($element) && $element != null) ? $element->name : $element->name ?>&actionElement=<?= $element->name ?>&id=<?= $row->{$config->config->database->idName} ?>&action=delete">
                                            <span data-feather="trash-2"></span>
                                        </a>
                                    <? endif; ?>
                                </td>
                            <? } ?>
                        </tr>
                    <? endforeach; ?>
                <? } ?>
            </tbody>
        </table>
    </div>
    <nav class="navbar navbar-expand-md navbar-light bg-light">
        <div class="collapse navbar-collapse" id="navbarsExample04">
            <ul class="navbar-nav mr-auto">
                <? if (isset($element->massActions)) { ?>
                    <? foreach ($element->massActions as $action) { ?>
                        <li class="nav-item ml-1">
                            <button type="button" refreshPage="<?= ($action->refreshPage) ? "true" : "" ?>" element="<?= $element->name ?>" goUrl="<?= $config->dirName ?>/?category=<?= isset($_GET['category']) ? $_GET['category'] : '' ?>&component=<?= isset($_GET['component']) ? $_GET['component'] : $component->name ?>&element=<?= $element->name ?>&action=massAction&dataType=json" name="<?= $action->name ?>" class="btn btn-sm btn-dark massActionBtn"><?= $action->caption ?></button>
                        </li>
                    <? } ?>
                <? } ?>
            </ul>
            <span class="navbar-text">
                <?= $curData->{$element->name . "pagePanel"} ?>
            </span>            
        </div>
    </nav>
    <? if (isset($element->widgetId) && $element->widgetId) { ?>
        </div>
        <!-- Конец виджета с данными (вложенный элемент в режиме карточки) -->
    <? } ?>
    <? } ?>
    <!-- Конец блока else (обычный рендеринг) -->
</div>
