<div class="alertSpace">
    
</div>
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label for="<?= $element->name ?>Domain">Домен</label>
            <select name="<?= $element->name ?>Domain" id="<?= $element->name ?>Domain" class="form-control <?= $element->name ?>Domain">
                <? $i = 0; ?>
                <? foreach ($domainsList as $domain): ?>
                    <? if ($domain->status) continue; ?>
                    <option value="<?= $domain->id ?>"><?= $domain->domain ?></option>
                <? endforeach; ?>
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group usersListDiv">
            <label for="<?= $element->name ?>User">Пользователь</label>
            <select name="<?= $element->name ?>User" id="<?= $element->name ?>User" class="form-control <?= $element->name ?>User">
                <? $i = 0; ?>
                <? foreach ($domainsList as $domain): ?>
                    <? if ($domain->status) continue; ?>
                    <option value="<?= $domain->domain ?>"><?= $domain->domain ?></option>
                <? endforeach; ?>
            </select>
            <small id="<?= $element->name ?>UserHelp" class="form-text text-muted">Укажите имя пользователя</small>
        </div>
        <div class="form-group getUsersListDiv">
            <label for="<?= $element->name ?>User">Пользователь</label>
            <button type="button" name="<?= $element->name ?>GetUsers" id="<?= $element->name ?>User" class="form-control customContentAddPostGetUsers btn btn-primary">Список пользователей</button>
        </div>
    </div>
</div>
<button type="button" name="<?= $element->name ?>Submit" class="<?= $element->name ?>Btn btn btn-primary">Создать пост</button>
<input type="hidden" name="<?= $element->name ?>ElementName" class="<?= $element->name ?>ElementName" value="<?= $element->name ?>" />
