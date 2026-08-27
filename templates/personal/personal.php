<div class="col-md-8">
    <h1 class="pt-3 pb-2 mb-3 h2">Личные данные</h1>
    <div class="row">
        <div class="col-md-4">
            <form action="" method="post">
                <div class="form-group">
                    <label for="login">Логин</label>
                    <input type="text" name="login" class="form-control" id="login" disabled="" placeholder="<?= $this->auth->getLogin() ?>" />
                </div>
                <div class="form-group">
                    <label for="name">Имя</label>
                    <input type="text" name="name" class="form-control" id="name" placeholder="" value="<?= $this->auth->getName() ?>" required="" />
                </div>
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" name="password" class="form-control" id="password" placeholder="" />
                </div>
                <div class="form-group">
                    <label for="password2">Повторить пароль</label>
                    <input type="password" name="password2" class="form-control" id="password2" placeholder="" />
                </div>
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </form>
        </div>
    </div>
    <? if (isset($otpUser) && $otpUser && !empty($this->config->auth->otp)) { ?>
        <hr>
        <h2 class="pt-3 pb-2 mb-3 h3">Двухфакторная аутентификация (OTP)</h2>
        <? if ($this->errorMessage) { ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="alert alert-danger" role="alert">
                        <?= $this->errorMessage ?>
                    </div>
                </div>
            </div>
        <? } ?>
        <? if ($otpUser->otp_enabled) { ?>
            <div class="row">
                <div class="col-md-6">
                    <p><span class="badge badge-success">Включена</span> Вход подтверждается кодом из приложения-аутентификатора.</p>
                    <form action="" method="post" class="form-inline">
                        <input type="hidden" name="otpAction" value="disable" />
                        <input type="text" name="otp" class="form-control mr-2" placeholder="Код из приложения" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required />
                        <button type="submit" class="btn btn-danger" onclick="return window.confirm('Отключить двухфакторную аутентификацию?');">Отключить</button>
                    </form>
                </div>
            </div>
        <? } else if (isset($otpSetupSecret) && $otpSetupSecret) { ?>
            <div class="row">
                <div class="col-md-6">
                    <p>1. Установите <b>Microsoft Authenticator</b> (или Google Authenticator, FreeOTP и т.п.).</p>
                    <p>2. В приложении добавьте аккаунт: <i>Добавить аккаунт &rarr; Другая учётная запись &rarr; сканировать QR-код</i>.</p>
                    <div id="otpQr" class="mb-3"></div>
                    <p class="text-muted">Если сканировать не получается, введите ключ вручную:<br><code><?= totp::formatSecret($otpSetupSecret) ?></code></p>
                    <p>3. Введите 6-значный код из приложения для подтверждения:</p>
                    <form action="" method="post" class="form-inline">
                        <input type="hidden" name="otpAction" value="confirm" />
                        <input type="text" name="otp" class="form-control mr-2" placeholder="000000" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required autofocus />
                        <button type="submit" class="btn btn-primary mr-2">Подтвердить и включить</button>
                    </form>
                    <form action="" method="post" class="mt-2">
                        <input type="hidden" name="otpAction" value="cancel" />
                        <button type="submit" class="btn btn-link p-0">Отменить настройку</button>
                    </form>
                    <script>
                        document.addEventListener("DOMContentLoaded", function () {
                            var qr = qrcode(0, 'M');
                            qr.addData(<?= json_encode($otpSetupUri) ?>);
                            qr.make();
                            document.getElementById("otpQr").innerHTML = qr.createImgTag(5, 8);
                        });
                    </script>
                </div>
            </div>
        <? } else { ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="alert alert-warning" role="alert">
                        Доступ к панели закрыт, пока не настроена двухфакторная аутентификация. Настройте OTP, чтобы продолжить работу.
                    </div>
                    <p><span class="badge badge-secondary">Выключена</span></p>
                    <p class="text-muted">Настройте одноразовые коды через Microsoft Authenticator, чтобы защитить аккаунт даже при утечке пароля.</p>
                    <form action="" method="post">
                        <input type="hidden" name="otpAction" value="start" />
                        <button type="submit" class="btn btn-primary">Настроить OTP</button>
                    </form>
                </div>
            </div>
        <? } ?>
    <? } ?>
</div>
