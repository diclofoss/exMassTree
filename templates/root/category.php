<div class="col-md-12">
    <h1 class="mb-4">Выберите раздел</h1>
    <div class="card-deck mb-3 text-center">
        <? $i = 0; ?>
        <? foreach ($category->components as $content) { ?>
            <? if (!in_array($content->name, $this->auth->componentList)) continue; ?>
            <? $i ++ ?>
            <? if ($i > 4) { ?>
                <? $i = 1 ?>
            </div>
            <div class="card-deck mb-3 text-center">
            <? } ?>
            <div class="card mb-4 box-shadow">
                <div class="card-body">
                    <a href="<?= $this->dirName ?>/?category=<?= $category->name ?>&component=<?= $content->name ?>" class="btn btn-lg btn-block btn-outline-secondary"><?= $content->title ?></a>
                </div>
            </div>
        <? } ?>
    </div>
</div>
