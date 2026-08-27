<? if (!in_array($category->components[0]->name, $this->auth->componentList)) return; ?>
<div class="col-md-12">
    <div class="card-deck mb-3 text-center">
        <? $i = 0; ?>
        <? foreach ($category->categorySqlMenuItems as $data) { ?>
            <? $i ++ ?>
            <? if ($i > 4) { ?>
                <? $i = 1 ?>
            </div>
            <div class="card-deck mb-3 text-center">
            <? } ?>
            <div class="card mb-4 box-shadow">
                <div class="card-body">
                    <a class="btn btn-lg btn-block btn-outline-secondary" href="<?= str_replace("#ID#", $data->id, $category->sqlMenuUrl) ?>">
                        <?= $data->data ?>
                    </a>
                </div>
            </div>
        <? } ?>
    </div>
</div>
