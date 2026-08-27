<? if (!in_array($category->components[0]->name, $this->auth->componentList)) return; ?>
<? foreach ($category->categorySqlMenuItems as $data) { ?>
    <li class="nav-item">
        <a class="nav-link <?= ($_SERVER['REQUEST_URI'] == str_replace("#ID#", $data->id, $category->sqlMenuUrl)) ? "active" : ""?>" href="<?= str_replace("#ID#", $data->id, $category->sqlMenuUrl) ?>">
            <? if (isset($category->sqlMenuIcon) && $category->sqlMenuIcon) { ?>
                <span data-feather="<?= $category->sqlMenuIcon ?>"></span>
            <? } ?>
            <?= $data->data ?>
        </a>
    </li>
<? } ?>
