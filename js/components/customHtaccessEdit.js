$().ready(function () {
    var dataBefore = "";
    $("div .customHtaccessEditDomain").select2().on('change', function (e) {
        $(".alert").remove();
        $(".htaccessDiv").remove();
        $("div .customHtaccessEditSubmitBtn").hide();
    });
    $("div .customHtaccessEditSubmitBtn").hide();
    $('.customHtaccessEditSubmitBtn').click(function () {
        var domain_id = $(this).parent().find("div .customHtaccessEditDomain").val();
        var data = $("div.htaccessDiv textarea").val();
        $(this).parent().css("opacity", "0.1");
        $(this).attr("disabled", "");
        $(this).parent().find("div .customHtaccessEditDomain").attr("disabled", "");
        var postArray = {
            id: "",
            domain_id: domain_id,
            data: data,
            dataBefore: dataBefore
        };
        var category = location.search.match(/[?&]category=([^&]*)/)[1];
        var component = location.search.match(/[?&]component=([^&]*)/)[1];
        var $_this = $(this);
        var elementName = $(this).parent().find("div .customHtaccessEditName").val();
        $(this).parent().find(".alert").remove();
        $(this).parent().find(".htaccessDiv").remove();
        $("div .customHtaccessEditSubmitBtn").hide();
        $.ajax({
            url: dirName + '/?category=' + category + "&component=" + component + "&element=" + elementName + "&action=submitData&dataType=json",
            type: 'POST',
            data: postArray,
            dataType: 'json',
            success: function (data, textStatus, jqXHR)
            {
                if (!data.result) {
                    $('.customHtaccessEditBtn').before('<div class="col-md-9 alert alert-danger" role="alert">Ошибка интеграции</div>');
                } else if (data.result == "noconnect") {
                    $('.customHtaccessEditBtn').before('<div class="col-md-9 alert alert-danger" role="alert">Нет соединения с CMS</div>');
                } else if (data.result == "internalerror") {
                    $('.customHtaccessEditBtn').before('<div class="col-md-9 alert alert-danger" role="alert">Ошибка интеграции</div>');
                } else if (data.result == "nodir") {
                    $('.customHtaccessEditBtn').before('<div class="col-md-9 alert alert-danger" role="alert">CMS не найдена</div>');
                } else if (data.result == "empty") {
                    $('.customHtaccessEditBtn').before('<div class="col-md-9 alert alert-warning" role="alert">Нет данных</div>');
                } else {
                    if (data.result == "success") {
                        $('.customHtaccessEditBtn').before('<div class="col-md-9 alert alert-success" role="alert">Данные сохранены</div>');
                        $('.customHtaccessEditBtn').before('<div class="form-row htaccessDiv"><div class="form-group col-md-9"><label for="htaccessId">Данные</label>\n\<textarea name="htaccessData" class="form-control" id="htaccessId" rows="20">' + data.data + '</textarea><input type="hidden" name="dataBefore" value="' + data.data + '" /></div></div>');
                        $("div .customHtaccessEditSubmitBtn").show();
                    }
                }
                dataBefore = data.data;
                $_this.parent().css("opacity", "1");
                $_this.removeAttr("disabled");
                $_this.parent().find("div .customHtaccessEditDomain").removeAttr("disabled");
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
                $('.customHtaccessEditBtn').before('<div class="col-md-9 alert alert-danger" role="alert">Ошибка интеграции</div>');
                $_this.parent().css("opacity", "1");
                $_this.removeAttr("disabled");
                $_this.parent().find("div .customHtaccessEditDomain").removeAttr("disabled");
            }
        });
    });
    $('.customHtaccessEditBtn').click(function () {
        var domain_id = $(this).parent().find("div .customHtaccessEditDomain").val();
        $(this).parent().css("opacity", "0.1");
        $(this).attr("disabled", "");
        $(this).parent().find("div .customHtaccessEditDomain").attr("disabled", "");
        var postArray = {
            id: "",
            domain_id: domain_id,
            dataBefore: dataBefore
        };
        var category = location.search.match(/[?&]category=([^&]*)/)[1];
        var component = location.search.match(/[?&]component=([^&]*)/)[1];
        var $_this = $(this);
        var elementName = $(this).parent().find("div .customHtaccessEditName").val();
        $(this).parent().find(".alert").remove();
        $(this).parent().find(".htaccessDiv").remove();
        $("div .customHtaccessEditSubmitBtn").hide();
        $.ajax({
            url: dirName + '/?category=' + category + "&component=" + component + "&element=" + elementName + "&action=findData&dataType=json",
            type: 'POST',
            data: postArray,
            dataType: 'json',
            success: function (data, textStatus, jqXHR)
            {
                if (!data.result) {
                    $('.customHtaccessEditBtn').before('<div class="col-md-9 alert alert-danger" role="alert">Ошибка интеграции</div>');
                } else if (data.result == "noconnect") {
                    $('.customHtaccessEditBtn').before('<div class="col-md-9 alert alert-danger" role="alert">Нет соединения с CMS</div>');
                } else if (data.result == "internalerror") {
                    $('.customHtaccessEditBtn').before('<div class="col-md-9 alert alert-danger" role="alert">Ошибка интеграции</div>');
                } else if (data.result == "nodir") {
                    $('.customHtaccessEditBtn').before('<div class="col-md-9 alert alert-danger" role="alert">CMS не найдена</div>');
                } else if (data.result == "empty") {
                    $('.customHtaccessEditBtn').before('<div class="col-md-9 alert alert-warning" role="alert">Нет данных</div>');
                } else {
                    if (data.result == "success") {
                        $('.customHtaccessEditBtn').before('<div class="form-row htaccessDiv"><div class="form-group col-md-9"><label for="htaccessId">Данные</label>\n\<textarea name="htaccessData" class="form-control" id="htaccessId" rows="20">' + data.data + '</textarea></textarea><input type="hidden" name="dataBefore" value="' + data.data + '" /></div></div>');
                        $("div .customHtaccessEditSubmitBtn").show();
                    }
                }
                dataBefore = data.data;
                $_this.parent().css("opacity", "1");
                $_this.removeAttr("disabled");
                $_this.parent().find("div .customHtaccessEditDomain").removeAttr("disabled");

            },
            error: function (jqXHR, textStatus, errorThrown)
            {
                $('.customHtaccessEditBtn').before('<div class="col-md-9 alert alert-danger" role="alert">Ошибка интеграции</div>');
                $_this.parent().css("opacity", "1");
                $_this.removeAttr("disabled");
                $_this.parent().find("div .customHtaccessEditDomain").removeAttr("disabled");
            }
        });

    });
});