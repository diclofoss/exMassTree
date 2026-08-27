<div class="col-md-<?= ($hasParent) ? "12" : $element->col ?>">
    <? if (isset($element->caption)) { ?>
        <h1 class="pb-2 mb-3 h2"><?= $element->caption ?></h1>
    <? } else { ?>
        <h1 class="pb-2 mb-3 h2"><?= $component->title ?></h1>
    <? } ?>
    <nav class="navbar navbar-expand-md navbar-light bg-light">
        <div class="collapse navbar-collapse" id="navbarsExample04">
            <ul class="navbar-nav mr-auto">
                <? if (in_array("add", $element->controll)): ?>
                    <li class="nav-item">
                        <a href="<?= $config->dirName ?>/?category=<?= $_GET['category'] ?>&component=<?= $_GET['component'] ?>&element=<?= $element->name ?>&action=add&id=<?= isset($_GET['id']) ? $_GET['id'] : "" ?>" class="btn btn-success img-btn btn-sm"><span data-feather="plus-square"></span></a>
                    </li>
                <? endif; ?>
            </ul>
        </div>
    </nav>
    <div class="row text-center text-lg-left">
        <? if (isset($data->{$element->name})) { ?>
            <? foreach ($data->{$element->name} as $row): ?>
                <div class="d-block mb-4 mt-4 ml-auto mr-auto h-100 justify-content-center text-center">
                    <? if (in_array("edit", $element->controll)): ?>
                        <a class="d-block mb-3" href="<?= $config->dirName ?>/?category=<?= $_GET['category'] ?>&component=<?= $_GET['component'] ?>&element=<?= $element->name ?>&id=<?= $row->{$config->config->database->idName} ?>&action=edit&parent_id=<?= isset($_GET['id']) ? $_GET['id'] : "" ?>">
                        <? endif; ?>
                        <? foreach ($element->elements as $curElement) { ?>
                            <? if ($curElement->name == $element->preview) { ?>
                                <? eval("\$renderVal = {$curElement->type}::renderList(\$config, \$component, \$element, \$curElement, \$row, \$jsInclude);"); ?><?= $renderVal ?>
                            <? } ?>
                        <? } ?>
                        <? if (in_array("edit", $element->controll)): ?>
                        </a>
                    <? endif; ?>
                    <? if (in_array("delete", $element->controll)): ?>
                        <a onclick="return window.confirm('Вы действительно хотите удалить?');" class="btn btn-danger img-btn btn-sm" href="<?= $config->dirName ?>/?category=<?= $_GET['category'] ?>&component=<?= $_GET['component'] ?>&element=<?= (isset($element) && $element != null) ? $element->name : $element->name ?>&actionElement=<?= $element->name ?>&id=<?= $row->{$config->config->database->idName} ?>&action=delete">
                            <span data-feather="trash-2"></span>
                        </a>
                    <? endif; ?>
                </div>
            <? endforeach ?>                
        <? } ?>                
    </div>
</div>
