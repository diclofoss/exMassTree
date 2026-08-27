<? #var_dump($result[2])      ?>
<div class="col-md-12">
    <h1 class="pt-3 pb-2 mb-3 h2">Очистка компонентов</h1>
    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>Таблица</th>
                    <th>Поле</th>
                    <th>Описание ошибки</th>
                </tr>
            </thead>
            <tbody>
                <? $curTable = "" ?>
                <? foreach ($result as $resultData) { ?>
                    <? if ($resultData->status) continue; ?>
                    <? if (!$resultData->tableFound) { ?> 
                        <tr>
                            <td><?= $resultData->table ?></td>
                            <td>

                            </td>
                            <td>
                                DROP TABLE <?= $resultData->table ?>;
                            </td>
                        </tr>
                    <? } else { ?>
                        <tr>
                            <td><?= $resultData->table ?></td>
                            <td>
                                <? foreach ($resultData->fields as $field) { ?>
                                    <? if ($field->status) continue; ?>
                                    <?= $field->field ?><br/>
                                <? } ?>
                            </td>
                            <td>
                                <? foreach ($resultData->fields as $field) { ?>
                                    <? if ($field->status) continue; ?>
                                    <?= $field->preDrop ?>
                                    ALTER TABLE <?= $resultData->table ?> DROP <?= $field->field ?>;<br/>
                                <? } ?>
                            </td>
                        </tr>
                    <? } ?>
                <? } ?>
            </tbody>
        </table>
    </div>
</div>