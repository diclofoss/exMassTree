<div class="col-md-6">
    <nav aria-label="breadcrumb" class="pt-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $this->dirName ?>/?admin">Администрирование</a></li>
            <li class="breadcrumb-item active">Группы</li>
        </ol>
    </nav>    
    <h1 class="pt-3 pb-2 mb-3 h2">Группы</h1>
    <nav class="navbar navbar-expand-md navbar-light bg-light">
        <div class="collapse navbar-collapse" id="navbarsExample04">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a href="<?= $this->dirName ?>/?admin&view=groups&action=add" class="btn btn-success img-btn btn-sm"><span data-feather="plus-square"></span></a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>Название</th>
                    <th class="text-center">Администратор</th>
                    <th class="text-right">Действие</th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($groupList as $group) { ?>
                    <tr>
                        <td><?= $group->title ?></td>
                        <td class="text-center"><?= ($group->isAdmin == 1) ? "+" : "" ?></td>
                        <td class="text-right">
                            <a href="<?= $this->dirName ?>/?admin&view=groups&action=edit&id=<?= $group->{$this->config->database->idName} ?>" class="btn btn-info img-btn btn-sm"><span data-feather="edit-2"></span></a>
                            <a onclick="return window.confirm('Вы действительно хотите удалить?');" class="btn btn-danger img-btn btn-sm" href="<?= $this->dirName ?>/?admin&view=groups&id=<?= $group->{$this->config->database->idName} ?>&action=delete">
                                <span data-feather="trash-2"></span>
                            </a>
                        </td>
                    </tr>
                <? } ?>
            </tbody>
        </table>
    </div>
</div>
