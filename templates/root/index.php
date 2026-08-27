<?php
$currentCategoryName = isset($_GET['category']) ? $_GET['category'] : null;
$isAdminPage = isset($_GET['admin']);
$isPersonalPage = isset($_GET['personal']);
$showSecondarySidebar = (bool) $renderLeft;

$currentCategory = null;
if ($currentCategoryName) {
    foreach ($this->config->categories as $cat) {
        if ($cat->name == $currentCategoryName) {
            $currentCategory = $cat;
            break;
        }
    }
}

if (!function_exists('shellCategoryHasAccess')) {
    function shellCategoryHasAccess($category, $componentList) {
        foreach ($category->components as $comp) {
            if (in_array($comp->name, $componentList)) {
                return true;
            }
        }
        return false;
    }
}

$userName = $this->auth->getName();
$userInitials = mb_strtoupper(mb_substr($userName, 0, 1));
$userParts = preg_split('/\s+/u', trim($userName));
if (count($userParts) >= 2) {
    $userInitials = mb_strtoupper(mb_substr($userParts[0], 0, 1) . mb_substr($userParts[1], 0, 1));
}

$portalUrl = null;
if (isset($this->config->portal) && $this->config->portal) {
    $portalUrl = $this->config->portal;
} else if (isset($this->config->portalUrl) && $this->config->portalUrl) {
    $portalUrl = $this->config->portalUrl;
}

$secondaryTitle = 'Раздел';
if ($isAdminPage) {
    $secondaryTitle = 'Администрирование';
} else if ($isPersonalPage) {
    $secondaryTitle = 'Личный кабинет';
} else if ($currentCategory) {
    $secondaryTitle = $currentCategory->title;
}
?>
<!doctype html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title><?= $this->config->project ?> &mdash; ExMassTree v.<?= $EXMASSTREE_VERSION ?></title>
        <link href="<?= $this->dirName ?>/css/vendor.min.css" rel="stylesheet" />
        <link href="<?= $this->dirName ?>/css/style.css" rel="stylesheet" />
        <link href="<?= $this->dirName ?>/css/cropper.min.css" rel="stylesheet" />
        <link href="<?= $this->dirName ?>/css/daterangepicker.css" rel="stylesheet" />
        <link href="<?= $this->dirName ?>/css/widgets.css" rel="stylesheet" />
        <? if (isset($this->config->extraCss) && $this->config->extraCss) { ?>
            <link href="<?= $this->dirName . "/" . $this->config->extraCss ?>" rel="stylesheet">
        <? } ?>
        <? if (isset($cssInclude)) { ?>
            <? foreach ($cssInclude as $css) { ?>
                <? if (!$css) continue; ?>
                <link href="<?= $this->dirName ?>/<?= $css ?>" rel="stylesheet" />
            <? } ?>
        <? } ?>
    </head>

    <body class="app-body<?= $showSecondarySidebar ? ' has-secondary-sidebar' : '' ?>">
        <div class="app-shell">
            <aside class="sidebar-primary" aria-label="Категории">
                <div class="sidebar-primary-brand">
                    <? if (isset($this->config->logo)) { ?>
                        <a href="<?= $this->dirName ?>/" title="<?= $this->config->project ?>">
                            <img src="<?= $this->config->logo ?>" alt="<?= $this->config->project ?>">
                        </a>
                    <? } else { ?>
                        <a href="<?= $this->dirName ?>/" class="sidebar-primary-brand-text" title="<?= $this->config->project ?>">
                            <?= mb_strtoupper(mb_substr($this->config->project, 0, 1)) ?>
                        </a>
                    <? } ?>
                </div>

                <nav class="sidebar-primary-nav">
                    <? foreach ($this->config->categories as $category) { ?>
                        <? if (!shellCategoryHasAccess($category, $this->auth->componentList)) continue; ?>
                        <? $isActive = !$isAdminPage && !$isPersonalPage && $currentCategoryName === $category->name; ?>
                        <a class="sidebar-primary-item<?= $isActive ? ' active' : '' ?>"
                           href="<?= $this->dirName ?>/?category=<?= $category->name ?>"
                           title="<?= $category->title ?>">
                            <? if (!empty($category->icon)) { ?>
                                <span class="sidebar-primary-icon" data-feather="<?= htmlspecialchars($category->icon, ENT_QUOTES, 'UTF-8') ?>"></span>
                            <? } else { ?>
                                <span class="sidebar-primary-icon sidebar-primary-icon--empty" aria-hidden="true"></span>
                            <? } ?>
                            <span class="sidebar-primary-label"><?= $category->title ?></span>
                        </a>
                    <? } ?>

                    <? if ($this->auth->isAdmin()) { ?>
                        <? $adminMenu = isset($this->config->adminMenu) ? $this->config->adminMenu : null; ?>
                        <? $adminMenuTitle = ($adminMenu && isset($adminMenu->title)) ? $adminMenu->title : 'Админ'; ?>
                        <a class="sidebar-primary-item<?= $isAdminPage ? ' active' : '' ?>"
                           href="<?= $this->dirName ?>/?admin"
                           title="Администрирование">
                            <? if ($adminMenu && !empty($adminMenu->icon)) { ?>
                                <span class="sidebar-primary-icon" data-feather="<?= htmlspecialchars($adminMenu->icon, ENT_QUOTES, 'UTF-8') ?>"></span>
                            <? } else { ?>
                                <span class="sidebar-primary-icon sidebar-primary-icon--empty" aria-hidden="true"></span>
                            <? } ?>
                            <span class="sidebar-primary-label"><?= $adminMenuTitle ?></span>
                        </a>
                    <? } ?>
                </nav>
            </aside>

            <? if ($showSecondarySidebar) { ?>
                <aside class="sidebar-secondary" aria-label="Подразделы">
                    <div class="sidebar-secondary-header">
                        <h2 class="sidebar-secondary-title"><?= $secondaryTitle ?></h2>
                        <button type="button"
                                class="sidebar-secondary-collapse btn btn-link"
                                aria-label="Свернуть меню">
                            <span data-feather="chevrons-left"></span>
                        </button>
                    </div>
                    <div class="sidebar-secondary-body">
                        <ul class="sidebar-secondary-nav nav flex-column">
                            <?= $renderLeft ?>
                        </ul>
                    </div>
                </aside>
            <? } ?>

            <div class="app-main">
                <header class="app-header">
                    <div class="app-header-left">
                        <? if ($showSecondarySidebar) { ?>
                            <button type="button"
                                    class="sidebar-secondary-expand btn btn-link d-none"
                                    aria-label="Развернуть меню">
                                <span data-feather="chevrons-right"></span>
                            </button>
                        <? } ?>
                    </div>
                    <div class="app-header-right">
                        <div class="dropdown app-user-menu">
                            <button class="app-user-trigger dropdown-toggle"
                                    type="button"
                                    id="userMenuDropdown"
                                    data-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false">
                                <span class="app-user-avatar"><?= htmlspecialchars($userInitials, ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="app-user-info">
                                    <span class="app-user-name"><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="app-user-project"><?= htmlspecialchars($this->config->project, ENT_QUOTES, 'UTF-8') ?></span>
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right app-user-dropdown" aria-labelledby="userMenuDropdown">
                                <a class="dropdown-item" href="<?= $this->dirName ?>/?personal">
                                    <span data-feather="user"></span>
                                    Настройки
                                </a>
                                <? if ($portalUrl) { ?>
                                    <a class="dropdown-item" href="<?= $portalUrl ?>" target="_blank" rel="noopener">
                                        <span data-feather="external-link"></span>
                                        Открыть сайт
                                    </a>
                                <? } ?>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="<?= $this->dirName ?>/?logout">
                                    <span data-feather="log-out"></span>
                                    Выход
                                </a>
                            </div>
                        </div>
                    </div>
                </header>

                <main role="main" class="app-content custom-main">
                    <? if ($this->path) { ?>
                        <nav aria-label="breadcrumb" class="app-breadcrumb">
                            <ol class="breadcrumb bg-transparent px-0 mb-0">
                                <? $total = count($this->path) ?>
                                <? $i = 1; ?>
                                <? foreach ($this->path as $path) { ?>
                                    <? if ($total == $i) { ?>
                                        <li class="breadcrumb-item active"><?= $path[0] ?></li>
                                    <? } else { ?>
                                        <li class="breadcrumb-item"><a href="<?= $path[1] ?>"><?= $path[0] ?></a></li>
                                    <? } ?>
                                    <? $i++ ?>
                                <? } ?>
                            </ol>
                        </nav>
                    <? } ?>
                    <div class="row app-content-body">
                        <?= $renderData ?>
                    </div>
                </main>
            </div>
        </div>

        <script>
            var dirName = "<?= $this->dirName ?>";
        </script>
        <script src="<?= $this->dirName ?>/js/vendor.min.js"></script>
        <script src="<?= $this->dirName ?>/js/widgetUtils.js"></script>
        <script src="<?= $this->dirName ?>/js/widgetManager.js"></script>
        <script src="<?= $this->dirName ?>/js/core.js"></script>
        <script src="<?= $this->dirName ?>/js/shell.js"></script>
        <? if (isset($jsInclude)) { ?>
            <? foreach ($jsInclude as $js) { ?>
                <? if (!$js) continue; ?>
                <? if (is_array($js)) { ?>
                    <? foreach ($js as $jsItem) { ?>
                        <script src="<?= $this->dirName ?>/<?= $jsItem ?>"></script>
                    <? } ?>
                <? } else { ?>
                    <script src="<?= $this->dirName ?>/<?= $js ?>"></script>
                <? } ?>
            <? } ?>
        <? } ?>
        <script src="<?= $this->dirName ?>/js/feather-icons.min.js"></script>
        <script>
            feather.replace()
        </script>
    </body>
</html>
