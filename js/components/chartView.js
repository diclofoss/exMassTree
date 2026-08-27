$().ready(function () {

    $(".chartCanvas").each(function () {
        let id = this.id;
        eval("var config = " + id + "Config");
        new Chart(
                document.getElementById(id),
                config
                );
    });
//    const myChart = new Chart(
//            document.getElementById('myChart'),
//            config
//            );

    $('input.daterangepickerFrontFilterEl').daterangepicker({
        "autoApply": true,
        ranges: {
            'Сегодня': [moment(), moment().add(1, 'days')],
            'Вчера': [moment().subtract(1, 'days'), moment()],
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