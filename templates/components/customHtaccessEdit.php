<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="<?= $element->name ?>Domain">Сайт</label>
            <select name="<?= $element->name ?>Domain" id="<?= $element->name ?>Domain" class="form-control customHtaccessEditDomain">
                <? $i = 0; ?>
                <? foreach ($domainsList as $domain): ?>
                    <? if ($domain->status) continue; ?>
                    <option value="<?= $domain->id ?>"><?= $domain->domain ?></option>
                <? endforeach; ?>
            </select>
            <small id="<?= $element->name ?>DomainHelp" class="form-text text-muted">Укажите сайт</small>
            <input type="hidden" name="<?= $element->name ?>ElementName" class="customHtaccessEditName" value="<?= $element->name ?>" />
        </div>
    </div>
</div>
<button type="button" name="<?= $element->name ?>Find" class="customHtaccessEditBtn btn btn-primary">Поиск</button>
<button type="button" name="<?= $element->name ?>Submit" class="customHtaccessEditSubmitBtn btn btn-success">Сохранить</button>