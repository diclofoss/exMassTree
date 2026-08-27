<div class="col-md-8">
    <nav aria-label="breadcrumb" class="pt-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $this->dirName ?>/?admin">Администрирование</a></li>
            <li class="breadcrumb-item active">Пользователи</li>
        </ol>
    </nav>    
    <h1 class="pt-3 pb-2 mb-3 h2">Пользователи</h1>
    <nav class="navbar navbar-expand-md navbar-light bg-light">
        <div class="collapse navbar-collapse" id="navbarsExample04">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a href="<?= $this->dirName ?>/?admin&view=users&action=add" class="btn btn-success img-btn btn-sm"><span data-feather="plus-square"></span></a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>Логин</th>
                    <th>Имя</th>
                    <th>Группа</th>
                    <th>OTP</th>
                    <th class="text-right">Действие</th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($userList as $user) { ?>
                    <tr>
                        <td><?= $user->login ?></td>
                        <td><?= $user->name ?></td>
                        <td><?= $user->group->title ?></td>
                        <td>
                            <? if (isset($user->otp_enabled) && $user->otp_enabled) { ?>
                                <span class="badge badge-success">вкл</span>
                            <? } else { ?>
                                <span class="badge badge-secondary">выкл</span>
                            <? } ?>
                        </td>
                        <td class="text-right">
                            <a class="btn btn-info img-btn btn-sm" href="<?= $this->dirName ?>/?admin&view=users&id=<?= $user->{$this->config->database->idName} ?>&action=edit">
                                <span data-feather="edit-2"></span>
                            </a>
                            <a onclick="return window.confirm('Вы действительно хотите удалить?');" class="btn btn-danger img-btn btn-sm" href="<?= $this->dirName ?>/?admin&view=users&id=<?= $user->{$this->config->database->idName} ?>&action=delete">
                                <span data-feather="trash-2"></span>
                            </a>
                        </td>
                    </tr>
                <? } ?>
            </tbody>
        </table>
    </div>
</div>
