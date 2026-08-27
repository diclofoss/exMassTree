$().ready(function () {
    $("div .customContentFinderDomain").select2();
    $('.customContentFinderBtn').click(function () {
        var text = $(this).parent().find("div .customContentFinderInput").val();
        if (!text) {
            return;
        }
        var domain = $(this).parent().find("div .customContentFinderDomain").val();
        $(this).parent().css("opacity", "0.1");
        $(this).attr("disabled", "");
        $(this).parent().find("div .customContentFinderDomain").attr("disabled", "");
        $(this).parent().find("div .customContentFinderInput").attr("disabled", "");
        var postArray = {
            id: "",
            text: text,
            domain: domain
        };
        var category = location.search.match(/[?&]category=([^&]*)/)[1];
        var component = location.search.match(/[?&]component=([^&]*)/)[1];
        var $_this = $(this);
        var elementName = $(this).parent().find("div .customContentFinderElementName").val();
        $(this).parent().find(".alert").remove();
        $.ajax({
            url: dirName + '/?category=' + category + "&component=" + component + "&element=" + elementName + "&action=findData&dataType=json",
            type: 'POST',
            data: postArray,
            dataType: 'json',
            success: function (data, textStatus, jqXHR)
            {
                if (!data.result) {
                    $('.customContentFinderBtn').before('<div class="alert alert-danger" role="alert">Ошибка интеграции</div>');
                } else if (data.result == "noconnect") {
                    $('.customContentFinderBtn').before('<div class="alert alert-danger" role="alert">Нет соединения с CMS</div>');
                } else if (data.result == "internalerror") {
                    $('.customContentFinderBtn').before('<div class="alert alert-danger" role="alert">Ошибка интеграции</div>');
                } else if (data.result == "nodir") {
                    $('.customContentFinderBtn').before('<div class="alert alert-danger" role="alert">CMS не найдена</div>');
                } else if (data.result == "empty") {
                    $('.customContentFinderBtn').before('<div class="alert alert-warning" role="alert">Нет данных</div>');
                } else {
                    if (data.result == "success") {
                        for (var i = 0; i < data.data.length; i++) {
                            $('.customContentFinderBtn').before('<div class="alert alert-primary" role="alert">\n\
\n\<a href="' + data.data[i].url + '"><img src="img/edit.svg" style="position: absolute;right: 10px;top: 10px;" /></a>\n\
\n\Заголовок: <a href="' + data.data[i].url + '">' + data.data[i].title + '</a><br/>\n\
\n\Внешнаяя ссылка: <a target="_blank" href="' + data.data[i].extUrl + '">' + data.data[i].extUrl + '</a><br/>\n\
\n\Дата поста: ' + data.data[i].publicDate + '<br/>\n\
\n\Автор: ' + data.data[i].author + '<br/>\n\
\n\Найденный текст: ' + data.data[i].findText + '</div>');
                        }
                    }
                }
                $_this.parent().css("opacity", "1");
                $_this.removeAttr("disabled");
                $_this.parent().find("div .customContentFinderDomain").removeAttr("disabled");
                $_this.parent().find("div .customContentFinderInput").removeAttr("disabled");

            },
            error: function (jqXHR, textStatus, errorThrown)
            {
                $('.customContentFinderBtn').before('<div class="alert alert-danger" role="alert">Ошибка интеграции</div>');
                $_this.parent().css("opacity", "1");
                $_this.removeAttr("disabled");
                $_this.parent().find("div .customContentFinderDomain").removeAttr("disabled");
                $_this.parent().find("div .customContentFinderInput").removeAttr("disabled");
            }
        });

    });
});