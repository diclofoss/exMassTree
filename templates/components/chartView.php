<script>
    const <?= $element->name ?>ChartLabels = [];
<? $baseline = $element->lines[0]; ?>
<? foreach ($baseline->data as $row) { ?>
    <?= $element->name ?>ChartLabels.push("<?= $row->{$baseline->x} ?>");
<? } ?>
        const <?= $element->name ?>ChartDatasets = [];
<? foreach ($element->lines as $line) { ?>
    <?= $element->name ?>ChartDatasets.push({
                label: '<?= $line->legend ?>',
                backgroundColor: 'rgb(255, 99, 132)',
                borderColor: 'rgb(255, 99, 132)',
                data: [
    <? foreach ($line->data as $row) { ?>
        <?= $row->{$line->y} ?>,
    <? } ?>
                ]
            });

<? } ?>

        const <?= $element->name ?>ChartData = {
            labels: <?= $element->name ?>ChartLabels,
            datasets: <?= $element->name ?>ChartDatasets
        };

        var <?= $element->name ?>ChartConfig = {
            type: 'line',
            data: <?= $element->name ?>ChartData,
            options: {}
        };

</script>
<div class="col-md-<?= ($hasParent) ? "12" : $element->col ?>">
    <? if (isset($element->caption)) { ?>
        <h1 class="pb-2 mb-3 h2"><?= $element->caption ?></h1>
    <? } else { ?>
        <h1 class="pb-2 mb-3 h2"><?= $component->title ?></h1>
    <? } ?>
    <? if (isset($element->textsearch)) { ?>
        <form class="form-inline" action="<?= $config->dirName ?>/?category=<?= $_GET['category'] ?>&component=<?= $_GET['component'] ?>&element=<?= $element->name ?>&textsearch" method="POST">
            <div class="form-group mb-2">
                <label for="<?= $element->name ?>_textsearch" class="sr-only"></label>
                <input type="text" class="form-control" name="textsearch" value="<?= (isset($_SESSION['filter'][$_GET['component']][$element->name]['textsearch'])) ? $_SESSION['filter'][$_GET['component']][$element->name]['textsearch'] : "" ?>" id="<?= $element->name ?>_textsearch" placeholder="поиск" />
            </div>            
            &nbsp;
            <button type="submit" class="btn btn-primary mb-2">Поиск</button>
            <input type="hidden" name="parentElement" value="<?= $element->name ?>" />
            <input type="hidden" name="component" value="<?= $component->name ?>" />
        </form>
    <? } ?>
    <? if (isset($element->frontFilterData)) { ?>
        <form class="form-inline" action="<?= $config->dirName ?>/?category=<?= $_GET['category'] ?>&component=<?= $_GET['component'] ?>&element=<?= $element->name ?>&frontFilter" method="POST">
            <? foreach ($element->frontFilterData as $filterData) { ?>
                <?= $filterData ?>
                &nbsp;
            <? } ?>
            <? if ($filterData) { ?>
                <button type="submit" class="btn btn-primary mb-2">Поиск</button>
            <? } ?>
            <input type="hidden" name="parentElement" value="<?= $element->name ?>" />
            <input type="hidden" name="component" value="<?= $component->name ?>" />
        </form>
    <? } ?>
    <? if (isset($element->filters)) { ?>
        <? foreach ($element->filterData as $filterData) { ?>
            <?= $filterData ?>
        <? } ?>
    <? } ?>
    <div>
        <canvas id="<?= $element->name ?>Chart" class="chartCanvas"></canvas>
    </div>
</div>
