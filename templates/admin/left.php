<li class="nav-item">
    <a class="nav-link <?= ($view == "users") ? "active" : "" ?>" href="<?= $this->dirName ?>/?admin&view=users">
        <span data-feather="users"></span>
        Пользователи
    </a>
</li>
<li class="nav-item">
    <a class="nav-link <?= ($view == "groups") ? "active" : "" ?>" href="<?= $this->dirName ?>/?admin&view=groups">
        <span data-feather="unlock"></span>
        Группы и доступы
    </a>
</li>
<li class="nav-item">
    <a class="nav-link <?= ($view == "history") ? "active" : "" ?>" href="<?= $this->dirName ?>/?admin&view=history">
        <span data-feather="clock"></span>
        История действий
    </a>
</li>
<li class="nav-item">
    <a class="nav-link <?= ($view == "database") ? "active" : "" ?>" href="<?= $this->dirName ?>/?admin&view=database">
        <span data-feather="database"></span>
        База данных
    </a>
</li>
<li class="nav-item">
    <a class="nav-link <?= ($view == "databaseCleanup") ? "active" : "" ?>" href="<?= $this->dirName ?>/?admin&view=databaseCleanup">
        <span data-feather="eye-off"></span>
        База данных (чистка)
    </a>
</li>
