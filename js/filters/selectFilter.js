$().ready(function () {
    $('.filterLink').click(function () {
        var element = $(this).attr("element");
        var parentElement = $(this).attr("parentElement");
        var filterCaption = $(this).attr("filterCaption");
        $('#modalFilterSelect').find(".modal-title").html("Фильтр");
        var options = "";
        for (var i = 0; i < selectData[element].length; i++) {
            options += "<option " + selectedData[element][i] + " value=\"" + i + "\">" + selectData[element][i] + "</option>";
        }
        $('#modalFilterSelect').find(".modal-body").html('\
<div class="form-group">\n\
<input type="hidden" name="parentElement" value="' + parentElement + '">\n\
<input type="hidden" name="element" value="' + element + '">\n\
<input type="hidden" name="component" value="' + component + '">\n\
<label for="filterSelect" class="col-form-label">' + filterCaption + ':</label>\n\
<select multiple="" width="100%" name="data[]" class="filterSelect">' + options + '</select>\n\
</div>\n\
');
        $('#modalFilterSelect').modal();
        $(".filterSelect").select2();
    });
    $('body').append('\
<form action="' + dirName + '/?filter" method="Post">\n\
<div id="modalFilterSelect" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">\n\
\n\
<div class="modal-dialog modal-sm">\n\
\n\
<div class="modal-content">\n\
<div class="modal-header">\n\
<h5 class="modal-title" id="exampleModalLabel">New message</h5>\n\
<button type="button" class="close" data-dismiss="modal" aria-label="Close">\n\
<span aria-hidden="true">&times;</span>\n\
</button>\n\
</div>\n\
<div class="modal-body">\n\
eee\n\
</div>\n\
<div class="modal-footer">\n\
<button type="submit" name="clearFilter" class="btn btn-danger">Очистить</button>\n\
<button type="submit" class="btn btn-primary">Найти</button>\n\
</div>\n\
</div>\n\
</div>\n\
</form>\n\
');
});