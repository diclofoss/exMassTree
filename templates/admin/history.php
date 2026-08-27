<div class="col-md-12">
    <h1 class="pt-3 pb-2 mb-3 h2">История действий</h1>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
            </ul>
            <span class="navbar-text">
                <?= $pagePanel ?>
            </span>            
        </div>
    </nav>
    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Логин</th>
                    <th>Действие</th>
                    <th>Объект</th>
                    <th>ID</th>
                    <th>Данные до</th>
                    <th>Данные после</th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($historyList as $history) { ?>
                    <tr>
                        <td><?= $history->datetime ?></td>
                        <td><?= $history->username ?></td>
                        <td>
                            <? if ($history->action == 0) { ?>Авторизация<? } ?>
                            <? if ($history->action == 1) { ?>Добавление<? } ?>
                            <? if ($history->action == 2) { ?>Редактирование<? } ?>
                            <? if ($history->action == 3) { ?>Удаление<? } ?>
                        </td>
                        <td><?= $history->element ?></td>
                        <td><?= ($history->elementId) ? $history->elementId : "" ?></td>
                        <td>
                            <? $dataBefore = str_replace("\n", "<br/>\n", htmlentities($history->dataBefore)); ?>
                            <? if (strlen($dataBefore) > 100) { ?>
                                <div class="historyElement">
                                    <div class="fulltext">
                                        <a href="#" class="inpandHistory"><span data-feather="minimize-2"></span></a>
                                        <?= str_replace("\n", "<br/>\n", htmlentities($history->dataBefore)) ?>
                                    </div>
                                    <div class="pretext">
                                        <?= utils::pretext($history->dataBefore, 100); ?>
                                        <a href="#" class="expandHistory"><span data-feather="external-link"></span></a>
                                    </div>
                                </div>
                            <? } else { ?>
                                <?= str_replace("\n", "<br/>\n", htmlentities($history->dataBefore)) ?>
                            <? } ?>
                        </td>
                        <td>
                            <? $dataBefore = str_replace("\n", "<br/>\n", htmlentities($history->dataAfter)); ?>
                            <? if (strlen($dataBefore) > 100) { ?>
                                <div class="historyElement">
                                    <div class="fulltext">
                                        <?= str_replace("\n", "<br/>\n", htmlentities($history->dataAfter)) ?>
                                        <a href="#" class="inpandHistory"><span data-feather="minimize-2"></span></a>
                                    </div>
                                    <div class="pretext">
                                        <?= utils::pretext($history->dataAfter, 100); ?>
                                        <a href="#" class="expandHistory"><span data-feather="external-link"></span></a>
                                    </div>
                                </div>
                            <? } else { ?>
                                <?= str_replace("\n", "<br/>\n", htmlentities($history->dataAfter)) ?>
                            <? } ?>                            
                        </td>
                    </tr>
                <? } ?>
            </tbody>
        </table>
    </div>
</div>
