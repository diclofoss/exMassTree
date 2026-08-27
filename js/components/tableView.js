$().ready(function () {
    $('.massActionBtn').click(function () {
        var idDataArray = new Array();
        var goUrl = $(this).attr("goUrl");
        var name = $(this).attr("name");
        var refreshPage = $(this).attr("refreshPage");
        $('input').each(function (index) {
            var propName = $(this).attr("name");
            if (!propName.endsWith("_massAction[]")) {
                return;
            }
            if (!$(this).is(":checked")) {
                return;
            }
            idDataArray.push($(this).val());
        });
        if (idDataArray.length == 0) {
            return false;
        }
        var postArray = {
            idDataList: idDataArray,
            action: name
        }
        $.ajax({
            url: goUrl,
            type: 'POST',
            data: postArray,
            dataType: 'json',
            success: function (data, textStatus, jqXHR)
            {
                if (refreshPage) {
                    document.location.href = document.location.href;
                }
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
            }
        });

        return false;
    });
    $('.tableViewSortable tbody').sortable({
    }).bind('sortupdate', function (e, ui) {
        var idDataArray = new Array();
        var elementName = $(this).parent().attr("element");
        var component = $(this).parent().attr("component");
        var category = $(this).parent().attr("category");
        $(this).find("tr").each(function () {
            var id = $(this).attr("data");
            idDataArray.push(id);
        });
        var postArray = {
            idData: idDataArray
        }
        $.ajax({
            url: dirName + '/?category=' + category + "&component=" + component + "&element=" + elementName + "&action=updateSortOrder&dataType=json",
            type: 'POST',
            data: postArray,
            dataType: 'json',
            success: function (data, textStatus, jqXHR)
            {
            },
            error: function (jqXHR, textStatus, errorThrown)
            {
            }
        });

    });

    $('input.daterangepickerFrontFilterEl').daterangepicker({
        "showWeekNumbers": true,
        "autoApply": true,
        ranges: {
            'Сегодня': [moment().subtract(1, 'days'), moment()],
            'Вчера': [moment().subtract(2, 'days'), moment().subtract(1, 'days')],
            'За 7 дней': [moment().subtract(6, 'days'), moment()],
            'За 30 дней': [moment().subtract(29, 'days'), moment()],
            'В этом месяце': [moment().startOf('month'), moment().endOf('month')],
            'В прошлом месяце': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'Весь период': [moment().subtract(10, 'year'), moment()]
        },
        "locale": {
            "format": "YYYY-MM-DD",
            "separator": " - ",
            "applyLabel": "Применить",
            "cancelLabel": "Отмена",
            "fromLabel": "От",
            "toLabel": "До",
            "customRangeLabel": "Выбрать...",
            "weekLabel": "Нед",
            "daysOfWeek": [
                "Вс",
                "Пн",
                "Вт",
                "Ср",
                "Чт",
                "Пт",
                "Сб"
            ],
            "monthNames": [
                "Январь",
                "Февраль",
                "Март",
                "Апрель",
                "Май",
                "Июнь",
                "Июль",
                "Август",
                "Сентябрь",
                "Октябрь",
                "Ноябрь",
                "Декабрь"
            ],
            "firstDay": 1
        }
    }, function (start, end, label) {
    });

});