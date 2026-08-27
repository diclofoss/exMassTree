$().ready(function () {
    $('.buttonPostAction').click(function () {
        $_this = $(this);
        var elementName = $(this).attr("element");
        var component = $(this).attr("component");
        var category = $(this).attr("category");
        var id = $(this).attr("data");
        $(this).attr("disabled", "");
        var postArray = {
            id: id
        };
        $.ajax({
            url: dirName + '/?category=' + category + "&component=" + component + "&element=" + elementName + "&action=acyncExec&dataType=json",
            type: 'POST',
            data: postArray,
            dataType: 'json',
            success: function (data, textStatus, jqXHR)
            {
                if (data.response) {
                    alert(data.response);
                    $_this.removeAttr("disabled");
                } else if (data.url) {
                    document.location.href = data.url;
                    $_this.removeAttr("disabled");
                }
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
                alert("Внутренняя ошибка сервера");
                $_this.removeAttr("disabled");
            }
        });
    });

    $('.buttonAction').click(function () {
        $_this = $(this);
        var elementName = $(this).attr("element");
        var component = $(this).attr("component");
        var category = $(this).attr("category");
        var id = $(this).attr("data");
        $(this).attr("disabled", "");
        var postArray = {
            id: id
        };
        $.ajax({
            url: dirName + '/?category=' + category + "&component=" + component + "&element=" + elementName + "&action=acyncExec&dataType=json",
            type: 'POST',
            data: postArray,
            dataType: 'json',
            success: function (data, textStatus, jqXHR)
            {
                if (data.response) {
                    alert(data.response);
                    $_this.removeAttr("disabled");
                } else if (data.url) {
                    document.location.href = data.url;
                    $_this.removeAttr("disabled");
                }
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
                alert("Внутренняя ошибка сервера");
                $_this.removeAttr("disabled");
            }
        });
    });

    $('.modalOpenButtonAction').click(function () {
        var elementName = $(this).attr("element");
        var rowId = $(this).attr("data");
        // Если есть data (ID строки), используем уникальный ID модального окна для списка
        var modalId = rowId ? elementName + "Modal_" + rowId : elementName + "Modal";
        var $modal = $("#" + modalId);
        if ($modal.length > 0) {
            $modal.modal();
        } else {
            alert('Модальное окно не найдено: ' + modalId);
        }
    });

    $('.modalButtonAction').click(function (e) {
        e.preventDefault();
        var elementName = $(this).attr("element");
        var component = $(this).attr("component");
        var category = $(this).attr("category");
        var id = $(this).attr("data");
        // Если есть data (ID строки), используем уникальный ID модального окна для списка
        var modalId = id ? elementName + "Modal_" + id : elementName + "Modal";
        $(this).attr("disabled", "");
        var formData = new FormData();
        $(this).parent().parent().find("input").each(function () {
            formData.append($(this).attr("name"), $(this).val());
        });
        if ($(this).parent().parent().find("input[type=\"file\"]")) {
            for (var i = 0; i < $(this).parent().parent().find("input[type=\"file\"]").length; i++) {
                var d = $(this).parent().parent().find("input[type=\"file\"]").prop("files")[i];
                var dName = $(this).parent().parent().find("input[type=\"file\"]").prop("name");
                formData.append(dName, d);
            }
        }
        formData.append("id", id);
        $.ajax({
            url: dirName + '/?category=' + category + "&component=" + component + "&element=" + elementName + "&action=acyncExec&dataType=json",
            type: 'POST',
            data: formData,
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            success: function (data, textStatus, jqXHR)
            {
                $("#" + modalId).modal('toggle');
                if (data.response) {
                    alert(data.response);
                } else if (data.url) {
                    document.location.href = data.url;
                }
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
                $("#" + modalId).modal('toggle');
                alert("Внутренняя ошибка сервера");
            }
        });
    });
    
    // Делегирование событий для динамически добавляемых кнопок (через AJAX виджеты)
    // Используем $(document) для обработки событий на элементах, добавленных позже
    $(document).on('click', '.buttonAction', function() {
        $_this = $(this);
        var elementName = $(this).attr("element");
        var component = $(this).attr("component");
        var category = $(this).attr("category");
        var id = $(this).attr("data");
        $(this).attr("disabled", "");
        var postArray = {
            id: id
        };
        $.ajax({
            url: dirName + '/?category=' + category + "&component=" + component + "&element=" + elementName + "&action=acyncExec&dataType=json",
            type: 'POST',
            data: postArray,
            dataType: 'json',
            success: function (data, textStatus, jqXHR)
            {
                if (data.response) {
                    alert(data.response);
                    $_this.removeAttr("disabled");
                } else if (data.url) {
                    document.location.href = data.url;
                    $_this.removeAttr("disabled");
                }
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
                alert("Внутренняя ошибка сервера");
                $_this.removeAttr("disabled");
            }
        });
    });
    
    $(document).on('click', '.modalOpenButtonAction', function() {
        var elementName = $(this).attr("element");
        var rowId = $(this).attr("data");
        // Если есть data (ID строки), используем уникальный ID модального окна для списка
        var modalId = rowId ? elementName + "Modal_" + rowId : elementName + "Modal";
        var $modal = $("#" + modalId);
        if ($modal.length > 0) {
            $modal.modal();
        } else {
            alert('Модальное окно не найдено: ' + modalId);
        }
    });
});