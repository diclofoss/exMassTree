<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="">
        <meta name="author" content="">

        <title><?= $this->config->project ?> &mdash; ExMassTree v.<?= $EXMASSTREE_VERSION ?></title>

        <!-- Bootstrap core CSS -->
        <link href="<?= $this->dirName ?>/css/vendor.min.css" rel="stylesheet">
        <link href="<?= $this->dirName ?>/css/login.css" rel="stylesheet">
    </head>
    <body class="text-center">
        <form class="form-signin" method="post" action="">
            <? if (isset($this->config->logo)) { ?>
                <div class="logoSpace mb-4">
                    <img src="<?= $this->config->logo ?>" alt="">
                </div>            
            <? } else { ?>
                <span data-feather="bar-chart-2"></span>
            <? } ?>
            <? if (isset($this->otpStep) && $this->otpStep) { ?>
                <h1 class="h3 mb-3 font-weight-normal">Подтверждение входа</h1>
                <p class="text-muted">Введите код из приложения-аутентификатора</p>
                <label for="inputOtp" class="sr-only">Код</label>
                <input type="text" name="otp" id="inputOtp" class="form-control" placeholder="000000" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required autofocus>
                <? if ($this->errorMessage) { ?>
                    <div class="alert alert-danger" role="alert">
                        <?= $this->errorMessage ?>
                    </div>
                <? } ?>
                <button class="btn btn-lg btn-dark btn-block" type="submit">Подтвердить</button>
            <? } else { ?>
                <h1 class="h3 mb-3 font-weight-normal">Авторизация</h1>
                <label for="inputLogin" class="sr-only">Логин</label>
                <input type="text" name="login" id="inputLogin" class="form-control" placeholder="Логин" required autofocus>
                <label for="inputPassword" class="sr-only">Пароль</label>
                <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Пароль" required>
                <? if ($this->errorMessage) { ?>
                    <div class="alert alert-danger" role="alert">
                        <?= $this->errorMessage ?>
                    </div>
                <? } ?>
                <button class="btn btn-lg btn-dark btn-block" type="submit">Вход</button>
            <? } ?>
            <p class="mt-5 mb-3 text-muted">&copy; DigitalInk <?= date("Y") ?></p>
        </form>
        <script src="https://unpkg.com/feather-icons/dist/feather.min.js"></script>
        <script>
            feather.replace()
        </script>
    </body>
</html>
