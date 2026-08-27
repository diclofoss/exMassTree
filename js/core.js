$().ready(function () {
    // Обработчик пагинации - только для обратной совместимости
    // Виджетная система обрабатывает пагинацию через bindEvents в widgetManager.js
    // Этот обработчик срабатывает только если виджет не найден (для старых элементов без виджетов)
    $(document).off('click', '.pager').on('click', '.pager', function (e) {
        // Проверяем, есть ли родительский виджет
        var $widgetContainer = $(this).closest('[data-widget-id]');
        if ($widgetContainer.length > 0) {
            // Виджет найден - виджетная система обработает это (через bindEvents)
            // Не обрабатываем здесь, чтобы избежать двойного вызова
            return;
        }
        
        // Старый способ - только для элементов БЕЗ виджетов (обратная совместимость)
        // Делаем AJAX запрос без перезагрузки страницы
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        
        var parentElement = $(this).attr("parentElement") || $(this).attr("parentelement");
        var element = $(this).attr("element");
        var page = $(this).attr("page");
        
        if (!parentElement || !element || !page) {
            console.warn('[core.js] Недостаточно данных для пагинации:', {parentElement, element, page});
            return false;
        }
        
        // AJAX запрос для пагинации без перезагрузки страницы
        $.ajax({
            url: dirName + '/?pagination=' + page + "&parentElement=" + parentElement + "&element=" + element + "&dataType=json",
            type: 'POST',
            dataType: 'json',
            success: function (data, textStatus, jqXHR) {
                // Обновляем только таблицу, не перезагружая страницу
                if (data && data.html) {
                    // Находим таблицу по element
                    var $table = $('table[element="' + element + '"]');
                    if ($table.length > 0) {
                        // Обновляем содержимое таблицы
                        var $container = $table.closest('.table-responsive').parent();
                        // Заменяем весь контент элемента
                        var $temp = $('<div>').html(data.html);
                        var $newContent = $temp.find('table[element="' + element + '"]').closest('.col-md-12, .col-md-6, .col-md-4, .col-md-3').first();
                        if ($newContent.length > 0) {
                            $container.replaceWith($newContent);
                            // Переинициализируем feather icons
                            if (typeof feather !== 'undefined') {
                                feather.replace();
                            }
                        }
                    }
                } else {
                    // Если нет HTML, просто обновляем страницу (fallback)
                    document.location.href = "";
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('[core.js] Ошибка пагинации:', errorThrown);
            }
        });
        return false;
    });

    $(".expandHistory").click(function () {
        $(this).parent().parent().find(".pretext").css("display", "none");
        $(this).parent().parent().find(".fulltext").css("display", "block");
        return false;
    });
    
    $(".inpandHistory").click(function () {
        $(this).parent().parent().find(".fulltext").css("display", "none");
        $(this).parent().parent().find(".pretext").css("display", "block");
        return false;
    });
});