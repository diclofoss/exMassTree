<div class="col-md-12 mb-3">
    <? if (isset($element->caption)) { ?>
        <h1 class="pb-2 mb-3 h2"><?= $element->caption ?></h1>
    <? } else { ?>
        <h1 class="pb-2 mb-3 h2"><?= $component->title ?></h1>
    <? } ?>
    <? if (!isset($parentSave) && (!isset($element->noSaveBtn) || !$element->noSaveBtn)) { ?>
        <form action="" method="post" enctype="multipart/form-data">
        <? 
        // Добавляем parent_id в скрытое поле, если мы добавляем вложенный элемент
        if (isset($_GET['action']) && $_GET['action'] == 'add' && isset($_GET['id']) && $_GET['id']) {
            // Используем глобальный parentIdName из конфига
            $parentIdName = mysql::$parentIdName;
            if ($parentIdName) {
                echo '<input type="hidden" name="' . htmlspecialchars($parentIdName) . '" value="' . htmlspecialchars($_GET['id']) . '">';
            }
        }
        ?>
        <? } ?>
        <? $total = 12; ?>
        <? if (!isset($parentSave) && (!isset($element->noSaveBtn) || !$element->noSaveBtn)) { ?>
            <? if (!isset($element->hideTopControll) || !$element->hideTopControll) { ?>
                <button type="submit" class="btn btn-info">Сохранить</button>
            <? } ?>
        <? } ?>
        <? eval("\$singleLine = {$element->type}::hasSingleLine();"); ?>
        <? if (!$singleLine && (!isset($element->noSaveBtn) || !$element->noSaveBtn)) { ?>
            <? 
            // Формируем правильный URL для кнопки "Отмена"
            // Если есть путь и он содержит более одного элемента, используем предпоследний
            // Иначе формируем URL на основе category и component
            $cancelUrl = '';
            if (isset($config->path) && is_array($config->path) && count($config->path) > 1) {
                $cancelUrl = $config->path[count($config->path) - 2][1];
            } else if (isset($_GET['category']) && isset($_GET['component'])) {
                $cancelUrl = $config->dirName . '/?category=' . urlencode($_GET['category']) . '&component=' . urlencode($_GET['component']);
            } else {
                $cancelUrl = $config->dirName . '/';
            }
            ?>
            <a href="<?= $cancelUrl ?>" type="submit" class="btn btn-dark">Отмена</a>
            <hr/>
        <? } ?>
        <div class="form-row <? if ($singleLine) { ?>mt-3<?}?>">
            <? $fieldset = ""; ?>
            <? foreach ($element->elements as $curElement) { ?>
                <? if ($curElement->type == "fieldset") { ?>
                    <? if (!isset($curElement->col)): ?>
                        <? $col = 12; ?>
                    <? else: ?>
                        <? $col = $curElement->col; ?>
                    <? endif; ?>
                    <? $total -= $col ?>
                    <? if ($total < 0 || isset($curElement->newrow) && $curElement->newrow): ?>
                        <? $total = 12 ?>
                    </div>
                    <div class="form-row">
                    <? endif; ?>
                    <div class="col-md-<?= $curElement->col ?>">
                        <? if (isset($curElement->caption)) { ?>
                            <h3 class="pb-2 mb-3 h2"><?= $curElement->caption ?></h3>
                        <? } ?>
                        <div class="form-row">
                            <? $fieldsetTotal = 12; ?>
                            <? foreach ($element->elements as $curEl) { ?>
                                <? if (!isset($curEl->fieldset) || $curEl->fieldset != $curElement->name) continue; ?>
                                <? if (!isset($curEl->renderData)) continue; ?>
                                <? if (!isset($curEl->col)): ?>
                                    <? $col = 12; ?>
                                <? else: ?>
                                    <? $col = $curEl->col; ?>
                                <? endif; ?>
                                <? $fieldsetTotal -= $col ?>
                                <? if ($fieldsetTotal < 0 || isset($curEl->newrow) && $curEl->newrow): ?>
                                    <? $fieldsetTotal = 12 ?>
                                </div>
                                <div class="form-row">
                                <? endif; ?>
                                <div class="form-group col-md-<?= $col ?>">
                                    <?= $curEl->renderData ?>
                                </div>
                            <? } ?>
                            <? if (!isset($curElement->hideBottomControll) || !$curElement->hideBottomControll) { ?>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <hr/>
                                    <button type="submit" class="btn btn-info">Сохранить</button>&nbsp;
                                    <? 
                                    // Формируем правильный URL для кнопки "Отмена"
                                    $cancelUrl = '';
                                    if (isset($config->path) && is_array($config->path) && count($config->path) > 1) {
                                        $cancelUrl = $config->path[count($config->path) - 2][1];
                                    } else if (isset($_GET['category']) && isset($_GET['component'])) {
                                        $cancelUrl = $config->dirName . '/?category=' . urlencode($_GET['category']) . '&component=' . urlencode($_GET['component']);
                                    } else {
                                        $cancelUrl = $config->dirName . '/';
                                    }
                                    ?>
                                    <a href="<?= $cancelUrl ?>" type="submit" class="btn btn-dark">Отмена</a>                                    
                                </div>
                            <? } else { ?>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <hr/>
                                </div>
                            <? } ?>
                        </div>
                    </div>
                <? } ?>
                <? if (isset($curElement->fieldset) && $curElement->fieldset) continue; ?>
                <? if (!isset($curElement->renderData)) continue; ?>
                <? if (!isset($curElement->col)): ?>
                    <? $col = 12; ?>
                <? else: ?>
                    <? $col = $curElement->col; ?>
                <? endif; ?>
                <? $total -= $col ?>
                <? if ($total < 0 || isset($curElement->newrow) && $curElement->newrow): ?>
                    <? $total = 12 ?>
                </div>
                <div class="form-row">
                <? endif; ?>
                <div class="form-group col-md-<?= $col ?>">
                    <?= $curElement->renderData ?>
                </div>
            <? } ?>
        </div>
        <? if (isset($element->hideBottomControll) && $element->hideBottomControll) { ?>
        <? } else { ?>
            <? if (!$singleLine) { ?>
                <hr/>
            <? } ?>
            <? if (!isset($parentSave) && (!isset($element->noSaveBtn) || !$element->noSaveBtn)) { ?>
                <button type="submit" class="btn btn-info">Сохранить</button>
            <? } ?>
            <? if (!$singleLine && (!isset($element->noSaveBtn) || !$element->noSaveBtn)) { ?>
                <? 
                // Формируем правильный URL для кнопки "Отмена"
                $cancelUrl = '';
                if (isset($config->path) && is_array($config->path) && count($config->path) > 1) {
                    $cancelUrl = $config->path[count($config->path) - 2][1];
                } else if (isset($_GET['category']) && isset($_GET['component'])) {
                    $cancelUrl = $config->dirName . '/?category=' . urlencode($_GET['category']) . '&component=' . urlencode($_GET['component']);
                } else {
                    $cancelUrl = $config->dirName . '/';
                }
                ?>
                <a href="<?= $cancelUrl ?>" type="submit" class="btn btn-dark">Отмена</a>
            <? } ?>
        <? } ?>
        <? if (!isset($parentSave)) { ?>
        </form>
    <? } ?>
</div>