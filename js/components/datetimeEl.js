$(function () {
    $('input.daterangepickerEl').daterangepicker({
        "timePicker": true,
        "autoApply": true,
        "timePicker24Hour": true,
        "singleDatePicker": true,
        "showDropdowns": true,
        "locale": {
            "format": 'YYYY-MM-DD HH:mm:ss',
            "separator": " - ",
            "applyLabel": "Apply",
            "cancelLabel": "Cancel",
            "fromLabel": "From",
            "toLabel": "To",
            "customRangeLabel": "Выбрать",
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
    });
});