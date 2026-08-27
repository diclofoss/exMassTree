<div class="col-md-12">
    <h1 class="pt-3 pb-2 mb-3 h2">Установка и удаление компонентов</h1>
    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>Компонент</th>
                    <th>Статус</th>
                    <th>Описание ошибки</th>
                    <th class="text-right">Действие</th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($this->config->categories as $curCategory) { ?>
                    <? foreach ($curCategory->components as $component) { ?>
                        <tr>
                            <td><?= $component->name ?></td>
                            <td style="<?= ($component->validationResult->status) ? "color:green" : "color:red" ?>"><?= ($component->validationResult->status) ? "Установлен" : "Не установлен" ?></td>
                            <td>
                                <? foreach ($component->validationResult->elements as $element) { ?>
                                    <? if ($element->status) continue; ?>
                                    <? printValidationResult($element); ?>
                                <? } ?>
                            </td>
                            <td class="text-right">
                                <form action="" method="post">
                                    <input type="hidden" name="component" value="<?= $component->name ?>" />
                                    <? if (!$component->validationResult->status) { ?>
                                        <button  onclick="return window.confirm('Вы действительно хотите установить?');"name="action" value="install" class="btn btn-info img-btn btn-sm"><span data-feather="arrow-down"></span></button>
                                    <? } ?>
                                    <button onclick="return window.confirm('Вы действительно хотите удалить?');" name="action" value="uninstall" class="btn btn-danger img-btn btn-sm"><span data-feather="trash"></span></button>
                                    <? if (getSuggesstions($element)) { ?>
                                        <button onclick="return window.confirm('Вы действительно хотите починить структуру?');" name="action" value="fix" class="btn btn-warning img-btn btn-sm"><span data-feather="minimize-2"></span></button>
                                        <? } ?>
                                </form>
                            </td>
                        </tr>
                    <? } ?>
                <? } ?>
            </tbody>
        </table>
    </div>
</div>

<?

function getSuggesstions($element) {
    $suggesstionList = "";
    if (isset($element->elements)) {
        foreach ($element->elements as $curElement) {
            if ($curElement->status)
                continue;
            $suggesstionList .= getSuggesstions($curElement);
        }
    }
    if (isset($element->suggesstion)) {
        $suggesstionList .= $element->suggesstion;
    }
    return $suggesstionList;
}

function printValidationResult($element) {
    ?>    
    <? if (isset($element->missedTable) && $element->missedTable) { ?>
        <strong>Нет таблицы <?= $element->elementName ?></strong>
        <br/>
    <? } ?>
    <? if (isset($element->missedField) && $element->missedField) { ?>
        /*<strong>Элемент: <?= $element->parentElementName ?></strong> Нет поля <?= $element->elementName ?>*/ <?= $element->suggesstion ?>
        <br/>
    <? } ?>
    <? if (isset($element->missedFk) && $element->missedFk) { ?>
        /*<strong>Элемент: <?= $element->parentElementName ?></strong>*/ <?= $element->fkSuggesstion ?>
        <br/>
    <? } ?>
    <? if (isset($element->descr)) { ?>
        <strong>Элемент: <?= $element->parentElementName ?></strong> Поле: <?= $element->elementName ?> Ошибка: <?= $element->descr ?>
        <br/>
    <? } ?>
    <? if (isset($element->elements)) { ?>
        <? foreach ($element->elements as $curElement) { ?>
            <? if ($curElement->status) continue; ?>
            <? printValidationResult($curElement); ?>
        <? } ?>
    <? } ?>
<? } ?>
