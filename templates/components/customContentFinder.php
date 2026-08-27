<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="<?= $element->name ?>Domain">Домен</label>
            <select name="<?= $element->name ?>Domain" id="<?= $element->name ?>Domain" class="form-control customContentFinderDomain">
                <? $i = 0; ?>
                <? foreach ($domainsList as $domain): ?>
                    <? if ($domain->status) continue; ?>
                    <option value="<?= $domain->domain ?>"><?= $domain->domain ?></option>
                <? endforeach; ?>
            </select>
            <small id="<?= $element->name ?>DomainHelp" class="form-text text-muted">Укажите сайт на котором может быть пост</small>
            <input type="hidden" name="<?= $element->name ?>ElementName" class="customContentFinderElementName" value="<?= $element->name ?>" />
        </div>
    </div>
    <div class="col-md-8">
        <div class="form-group">
            <label for="<?= $element->name ?>inputText">Поиск</label>
            <input name="<?= $element->name ?>inputText" type="text" class="customContentFinderInput form-control" id="<?= $element->name ?>inputText" placeholder="текст в посте">
            <small id="<?= $element->name ?>inputTextHelp" class="form-text text-muted">Введите любое предложение с поста</small>
        </div>
    </div>
</div>
<button type="button" name="<?= $element->name ?>Submit" class="customContentFinderBtn btn btn-primary">Поиск</button>