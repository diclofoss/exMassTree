<? foreach ($this->config->categories as $curCategory) { ?>
    <? if ($curCategory->name != $_GET['category']) continue; ?>
    <? foreach ($curCategory->components as $curComponent) { ?>
        <? if (!in_array($curComponent->name, $this->auth->componentList)) continue; ?>
        <li class="nav-item">
            <a class="nav-link <?= (isset($_GET['component']) && $_GET['component'] == $curComponent->name) ? "active" : "" ?>" href="<?= $this->dirName ?>/?category=<?= $curCategory->name ?>&component=<?= $curComponent->name ?>">
                <? if (!empty($curComponent->icon)) { ?>
                    <span class="sidebar-secondary-icon" data-feather="<?= htmlspecialchars($curComponent->icon, ENT_QUOTES, 'UTF-8') ?>"></span>
                <? } ?>
                <?= $curComponent->title ?>
            </a>
        </li>
    <? } ?>
<? } ?>
