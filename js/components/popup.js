$().ready(function () {
    $('.modalOpenButtonAction').click(function () {
        var elementName = $(this).attr("element");
        var rowId = $(this).attr("data");
        var modalId = rowId ? elementName + "Modal_" + rowId : elementName + "Modal";
        var $modal = $("#" + modalId);
        if ($modal.length > 0) {
            $modal.modal();
        }
    });
});