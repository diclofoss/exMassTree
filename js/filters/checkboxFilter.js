(function ($) {
    var modalReady = false;

    function ensureFilterModal() {
        if (modalReady || $('#modalFilterSelect').length) {
            modalReady = true;
            return;
        }

        $('body').append(
            '<form action="' + dirName + '/?filter" method="post" id="modalFilterSelectForm">' +
            '<div id="modalFilterSelect" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-hidden="true">' +
            '<div class="modal-dialog modal-sm">' +
            '<div class="modal-content">' +
            '<div class="modal-header">' +
            '<h5 class="modal-title">Фильтр</h5>' +
            '<button type="button" class="close" data-dismiss="modal" aria-label="Close">' +
            '<span aria-hidden="true">&times;</span>' +
            '</button>' +
            '</div>' +
            '<div class="modal-body"></div>' +
            '<div class="modal-footer">' +
            '<button type="submit" name="clearFilter" value="1" class="btn btn-danger">Очистить</button>' +
            '<button type="submit" class="btn btn-primary">Найти</button>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</form>'
        );

        $('#modalFilterSelectForm').on('submit', function (e) {
            e.preventDefault();

            var $modal = $('#modalFilterSelect');
            var $trigger = $modal.data('triggerLink');
            var widgetId = $trigger ? $trigger.closest('[data-widget-id]').attr('data-widget-id') : null;
            var formData = new FormData(this);
            var filterName = formData.get('element');
            var clearFilter = formData.get('clearFilter');

            $modal.modal('hide');

            if (widgetId && window.widgetManagers && window.widgetManagers[widgetId]) {
                if (clearFilter) {
                    window.widgetManagers[widgetId].setFilter(filterName, null);
                } else {
                    var values = formData.getAll('data[]');
                    if (!values.length) {
                        var singleValue = formData.get('data');
                        values = singleValue !== null ? [singleValue] : [];
                    }
                    window.widgetManagers[widgetId].setFilter(filterName, values);
                }
                return;
            }

            HTMLFormElement.prototype.submit.call(this);
        });

        modalReady = true;
    }

    function getCheckboxFilterConfig(elementName, $scope) {
        var $src = $scope.find('.filter-data-source[data-filter-type="checkbox"][data-element="' + elementName + '"]');
        if (!$src.length) {
            $src = $('.filter-data-source[data-filter-type="checkbox"][data-element="' + elementName + '"]');
        }

        if ($src.length) {
            var options = JSON.parse($src.first().attr('data-options') || '[]');
            var selected = JSON.parse($src.first().attr('data-selected') || '[]');
            var selectedMap = {};
            for (var i = 0; i < options.length; i++) {
                selectedMap[i] = selected.indexOf(i) >= 0 ? 'selected' : '';
            }
            return {
                component: $src.first().attr('data-component') || component,
                options: options,
                selected: selectedMap
            };
        }

        if (typeof selectData !== 'undefined' && selectData[elementName]) {
            return {
                component: component,
                options: selectData[elementName],
                selected: (typeof selectedData !== 'undefined' && selectedData[elementName]) ? selectedData[elementName] : {}
            };
        }

        return null;
    }

    function openCheckboxFilterModal($link) {
        var element = $link.attr('element');
        var parentElement = $link.attr('parentElement');
        var filterCaption = $link.attr('filterCaption');
        var $scope = $link.closest('[data-widget-id]');
        var config = getCheckboxFilterConfig(element, $scope);

        if (!config) {
            return;
        }

        ensureFilterModal();

        var options = '';
        for (var i = 0; i < config.options.length; i++) {
            options += '<option ' + (config.selected[i] || '') + ' value="' + i + '">' + config.options[i] + '</option>';
        }

        $('#modalFilterSelect').data('triggerLink', $link);
        $('#modalFilterSelect').find('.modal-title').html('Фильтр');
        $('#modalFilterSelect').find('.modal-body').html(
            '<div class="form-group">' +
            '<input type="hidden" name="parentElement" value="' + parentElement + '">' +
            '<input type="hidden" name="element" value="' + element + '">' +
            '<input type="hidden" name="component" value="' + config.component + '">' +
            '<label for="filterSelect" class="col-form-label">' + filterCaption + ':</label>' +
            '<select width="100%" name="data[]" class="filterSelect">' + options + '</select>' +
            '</div>'
        );
        $('#modalFilterSelect').modal('show');
        $('.filterSelect').select2();
    }

    $(document).on('click', '.filterLink', function (e) {
        var element = $(this).attr('element');
        var $scope = $(this).closest('[data-widget-id]');
        var config = getCheckboxFilterConfig(element, $scope);

        if (!config) {
            return;
        }

        e.preventDefault();
        e.stopImmediatePropagation();
        openCheckboxFilterModal($(this));
    });

    $(function () {
        ensureFilterModal();
    });
})(jQuery);
