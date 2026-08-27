<div class="col-md-12">
    <nav aria-label="breadcrumb" class="pt-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $this->dirName ?>/?admin">Администрирование</a></li>
            <li class="breadcrumb-item"><a href="<?= $this->dirName ?>/?admin&view=users">Пользователи</a></li>
            <li class="breadcrumb-item active">Пользователь</li>
        </ol>
    </nav>
    <h1 class="pb-2 mb-3 h2">Пользователь</h1>
    <form action="" method="post">
        <button type="submit" class="btn btn-info">Сохранить</button>
        <a href="<?= $this->dirName ?>/?admin&view=users" type="submit" class="btn btn-dark">Отмена</a>
        <hr>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="login">Логин</label>
                <input name="login" class="form-control" value="<?= (isset($user)) ? $user->login : "" ?>" id="login" placeholder="" type="text" />
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="name">Имя</label>
                <input name="name" class="form-control" value="<?= (isset($user)) ? $user->name : "" ?>" id="name" placeholder="" type="text" />
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="password">Пароль</label>
                <input name="password" class="form-control" value="" id="password" placeholder="" type="password" />
            </div>
        </div>
        <? if ($_GET['action'] == "edit" && isset($user->otp_enabled)) { ?>
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Двухфакторная аутентификация (OTP)</label>
                    <? if ($user->otp_enabled) { ?>
                        <div>
                            <span class="badge badge-success">Включена</span>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="resetOtp" value="1" id="resetOtp">
                            <label class="form-check-label" for="resetOtp">
                                Сбросить OTP (если пользователь потерял доступ к приложению)
                            </label>
                        </div>
                    <? } else { ?>
                        <div>
                            <span class="badge badge-secondary">Выключена</span>
                        </div>
                    <? } ?>
                </div>
            </div>
        <? } ?>
        <div class="form-row">
            <div class="form-group col-md-2">
                <label for="group_id">Группа</label>
                <select id="group_id" name="group_id" class="form-control">
                    <? foreach ($groupList as $group) { ?>
                        <? $found = false; ?>
                        <? foreach ($curGroupList as $curGroup) { ?>
                            <? if ($curGroup->group->{$this->config->database->idName} == $group->{$this->config->database->idName}) $found = true; ?>
                        <? } ?>
                        <? if (!$found) { ?>
                            <option <?= (isset($user) && $user->group_id == $group->{$this->config->database->idName}) ? "selected" : "" ?> value="<?= $group->{$this->config->database->idName} ?>"><?= $group->title ?></option>
                        <? } ?>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-3">
                <? if ($this->errorMessage) { ?>
                    <div class="alert alert-danger" role="alert">
                        <?= $this->errorMessage ?>
                    </div>
                <? } ?>
            </div>
        </div>
        <hr>
        <? if ($_GET['action'] == "edit") { ?>
            <div class="row">
                <div class="col-md-5">
                    <h3 class="pb-2 mb-3 h2">Дополнительные группы</h3>
                    <nav class="navbar navbar-expand-md navbar-light bg-light">
                        <div class="collapse navbar-collapse" id="navbarsExample04">
                            <ul class="navbar-nav mr-auto">
                                <li class="nav-item">
                                    <select name="newgroup_id">
                                        <option value="">Добавить группу</option>
                                        <? foreach ($groupList as $group) { ?>
                                            <? if ($group->{$this->config->database->idName} == $user->group_id) continue; ?>
                                            <? $found = false; ?>
                                            <? foreach ($curGroupList as $curGroup) { ?>
                                                <? if ($curGroup->group->{$this->config->database->idName} == $group->{$this->config->database->idName}) $found = true; ?>
                                            <? } ?>
                                            <? if (!$found) { ?>
                                                <option value="<?= $group->{$this->config->database->idName} ?>"><?= $group->title ?></option>
                                            <? } ?>
                                        <? } ?>
                                    </select>&nbsp;
                                </li>
                                <li class="nav-item">
                                    <button tyle="submit" name="addGroup" class="btn btn-success img-btn btn-sm"><span data-feather="plus-square"></span></button>
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
                                <? foreach ($curGroupList as $curGroup) { ?>
                                    <tr>
                                        <td><?= $curGroup->group->title ?></td>
                                        <td class="text-right">
                                            <a onclick="return window.confirm('Вы действительно хотите удалить?');" class="btn btn-danger img-btn btn-sm" href="<?= $this->dirName ?>/?admin&view=users&id=<?= $user->{$this->config->database->idName} ?>&group_id=<?= $curGroup->group->{$this->config->database->idName} ?>&action=delete">
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
        <a href="<?= $this->dirName ?>/?admin&view=users" type="submit" class="btn btn-dark">Отмена</a>
    </form>
</div>