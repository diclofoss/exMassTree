<div class="form-row">
    <? $total = 12; ?>
    <? foreach ($element->elements as $curElement) { ?>
        <? if (!isset($curElement->renderData)) continue; ?>
        <? if (!isset($curElement->col)): ?>
            <? $col = 12; ?>
        <? else: ?>
            <? $col = $curElement->col; ?>
        <? endif; ?>
        <? $total -= $col ?>
        <? if ($total < 0 || isset($curElement->newrow) && $curElement->newrow): ?>
            <? $total = 12 ?>
        </div>
        <div class="form-row">
        <? endif; ?>
        <div class="form-group col-md-<?= $col ?>">
            <?= $curElement->renderData ?>
        </div>
    <? } ?>
</div>