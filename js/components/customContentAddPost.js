$().ready(function () {
    $("div .customContentAddPostDomain").select2().on('change', function (e) {
        $('.alertSpace').find(".alert").remove();
        $('.usersListDiv').hide();
        $('.getUsersListDiv').show();
        $('.customContentAddPostBtn').hide();
    });
    $('.usersListDiv').hide();
    $('.customContentAddPostBtn').hide();
    $('.customContentAddPostBtn').click(function () {
        if (!window.confirm('Вы действительно хотите создать новый пост')) {
            return false;
        }
        var domainId = $(".customContentAddPostDomain").val();
        var userId = $(".customContentAddPostUser").val();
        var publicDate = $(".customContentAddPostPublicDate").val();
        var editDate = $(".customContentAddPostEditDate").val();
        var postArray = {
            id: "",
            user_id: userId,
            publicDate: publicDate,
            editDate: editDate,
            domain_id: domainId
        };
        var category = location.search.match(/[?&]category=([^&]*)/)[1];
        var component = location.search.match(/[?&]component=([^&]*)/)[1];
        var $_this = $(this);
        var elementName = $(".customContentAddPostElementName").val();
        $('.alertSpace').find(".alert").remove();
        $.ajax({
            url: dirName + '/?category=' + category + "&component=" + component + "&element=" + elementName + "&action=addPost&dataType=json",
            type: 'POST',
            data: postArray,
            dataType: 'json',
            success: function (data, textStatus, jqXHR)
            {
                if (!data.result) {
                    $('.alertSpace').append('<div class="alert alert-danger" role="alert">Ошибка интеграции</div>');
                } else if (data.result == "noconnect") {
                    $('.alertSpace').append('<div class="alert alert-danger" role="alert">Нет соединения с CMS</div>');
                } else if (data.result == "internalerror") {
                    $('.alertSpace').append('<div class="alert alert-danger" role="alert">Ошибка интеграции</div>');
                } else if (data.result == "nodir") {
                    $('.alertSpace').append('<div class="alert alert-danger" role="alert">CMS не найдена</div>');
                } else if (data.result == "empty") {
                    $('.alertSpace').append('<div class="alert alert-warning" role="alert">Нет данных</div>');
                } else {
                    if (data.result == "success") {
                        document.location.href = data.url;
                    }
                }
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
                $('.alertSpace').append('<div class="alert alert-danger" role="alert">Ошибка интеграции</div>');
            }
        });
    });
    $('.customContentAddPostGetUsers').click(function () {
        var domainId = $(".customContentAddPostDomain").val();
        var postArray = {
            id: "",
            domain_id: domainId
        };
        var category = location.search.match(/[?&]category=([^&]*)/)[1];
        var component = location.search.match(/[?&]component=([^&]*)/)[1];
        var $_this = $(this);
        var elementName = $(".customContentAddPostElementName").val();
        $('.alertSpace').find(".alert").remove();
        $.ajax({
            url: dirName + '/?category=' + category + "&component=" + component + "&element=" + elementName + "&action=getUsers&dataType=json",
            type: 'POST',
            data: postArray,
            dataType: 'json',
            success: function (data, textStatus, jqXHR)
            {
                if (!data.result) {
                    $('.alertSpace').append('<div class="alert alert-danger" role="alert">Ошибка интеграции</div>');
                } else if (data.result == "noconnect") {
                    $('.alertSpace').append('<div class="alert alert-danger" role="alert">Нет соединения с CMS</div>');
                } else if (data.result == "internalerror") {
                    $('.alertSpace').append('<div class="alert alert-danger" role="alert">Ошибка интеграции</div>');
                } else if (data.result == "nodir") {
                    $('.alertSpace').append('<div class="alert alert-danger" role="alert">CMS не найдена</div>');
                } else if (data.result == "empty") {
                    $('.alertSpace').append('<div class="alert alert-warning" role="alert">Нет данных</div>');
                } else {
                    if (data.result == "success") {
                        $('.getUsersListDiv').hide();
                        var options = "";
                        for (var i = 0; i < data.data.length; i++) {
                            options += '<option value="' + data.data[i].id + '">' + data.data[i].name + "</option>";
                        }
                        $('.usersListDiv select').html(options);
                        $('.usersListDiv').show();
                        $('.customContentAddPostBtn').show();
                    }
                }
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
                $('.alertSpace').append('<div class="alert alert-danger" role="alert">Ошибка интеграции</div>');
            }
        });
    });
});