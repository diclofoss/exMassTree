<div class="col-md-12">
    <nav aria-label="breadcrumb" class="pt-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $this->dirName ?>/?admin">Администрирование</a></li>
            <li class="breadcrumb-item"><a href="<?= $this->dirName ?>/?admin&view=groups">Группы</a></li>
            <li class="breadcrumb-item active">Группа</li>
        </ol>
    </nav>
    <h1 class="pb-2 mb-3 h2">Группа</h1>
    <form action="" method="post">
        <button type="submit" class="btn btn-info">Сохранить</button>
        <a href="<?= $this->dirName ?>/?admin&view=groups" class="btn btn-dark">Отмена</a>
        <hr>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="title">Название</label>
                <input name="title" class="form-control" value="<?= (isset($group)) ? $group->title : "" ?>" id="title" placeholder="" type="text">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="desctopUrl">Стартовая страница</label>
                <input name="desctopUrl" class="form-control" placeholder="по умолчаню" value="<?= (isset($group)) ? $group->desctopUrl : "" ?>" id="desctopUrl" placeholder="" type="text">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group form-check">&nbsp;
                <input <?= (isset($group) && $group->isAdmin) ? "checked" : "" ?> value="1" name="isAdmin" type="checkbox" class="form-check-input" id="isAdmin" />
                <label class="form-check-label" for="isAdmin">Администратор</label>
            </div>
        </div>
        <hr>
        <? if ($_GET['action'] == "edit") { ?>
            <div class="row">
                <div class="col-md-5">
                    <h3 class="pb-2 mb-3 h2">Доступы к компонентам</h3>
                    <nav class="navbar navbar-expand-md navbar-light bg-light">
                        <div class="collapse navbar-collapse" id="navbarsExample04">
                            <ul class="navbar-nav mr-auto">
                                <li class="nav-item">
                                    <select name="component_id">
                                        <option value="">Добавить компонент</option>
                                        <? foreach ($allComponents as $component) { ?>
                                            <? $found = false; ?>
                                            <? foreach ($componentNameList as $componentName) { ?>
                                                <? if ($component->name == $componentName->componentName) { ?>
                                                    <? $found = true; ?>
                                                <? } ?>
                                            <? } ?>
                                            <? if ($found) continue; ?>
                                            <option value="<?= $component->name ?>"><?= $component->title ?></option>
                                        <? } ?>
                                    </select>&nbsp;
                                </li>
                                <li class="nav-item">
                                    <button tyle="submit" name="newComponent" class="btn btn-success img-btn btn-sm"><span data-feather="plus-square"></span></button>
                                </li>
                            </ul>
                        </div>
                    </nav>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Название</th>
                                    <th class="text-right">Действие</th>
                                </tr>
                            </thead>
                            <tbody>
                                <? foreach ($componentList as $component) { ?>
                                    <tr>
                                        <td><?= $component->title ?></td>
                                        <td class="text-right">
                                            <a onclick="return window.confirm('Вы действительно хотите удалить?');" class="btn btn-danger img-btn btn-sm" href="<?= $this->dirName ?>/?admin&view=groups&component_id=<?= $component->name ?>&id=<?= $group->{$this->config->database->idName} ?>&action=delete">
                                                <span data-feather="trash-2"></span>
                                            </a>
                                        </td>
                                    </tr>
                                <? } ?>
                            </tbody>
                        </table>
                    </div>        
                </div>        
            </div>       
        <? } ?>
        <button type="submit" class="btn btn-info">Сохранить</button>
        <a href="<?= $this->dirName ?>/?admin&view=groups" class="btn btn-dark">Отмена</a>
    </form>
</div>