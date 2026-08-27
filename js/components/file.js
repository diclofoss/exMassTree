$().ready(function () {
    $('.fileDeleteBtn').click(function () {
        var id = $(this).attr("data");
        $('.file_' + id).removeClass("alert-info");
        $('.file_' + id).addClass("alert-danger");
        $('.file_' + id).html("Готов к удалению");
        $('input[name="file_delete_' + id).val("true");
    });
});