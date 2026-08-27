$().ready(function () {
    $('.pictureDeleteBtn').click(function () {
        var id = $(this).attr("data");
        if (typeof dependedPictures !== 'undefined' && dependedPictures[id]) {
            for (var i = 0; i < dependedPictures[id].length; i++) {
                var curId = dependedPictures[id][i];
                $('.picture_' + curId).parent().css("display", "none");
                $('.picture_' + curId).parent().after("<div class=\"mt-3 alert alert-danger\">Готова к удалению</div>");
                $('input[name="picture_delete_' + curId + '"]').val("true");
            }
        }
        $('.picture_' + id).parent().css("display", "none");
        $('.picture_' + id).parent().after("<div class=\"mt-3 alert alert-danger\">Готова к удалению</div>");
        $('input[name="picture_delete_' + id + '"]').val("true");
    });

    var cropper;
    var cropBoxData;
    var canvasData;

    $('.pictureChangeSizeModal').on('shown.bs.modal', function () {
        var id = $(this).attr("data");
        var width = $(this).attr("data-width");
        var height = $(this).attr("data-height");
        var aspectRatio = width / height;
        var image = document.querySelector("#resizePicture_" + id);
        var preview = document.querySelector("#picture_" + id);
        cropper = new Cropper(image, {
            autoCropArea: 0.5,
            aspectRatio: aspectRatio,
            preview: "#picture_" + id,
            ready: function () {
                // Strict mode: set crop box data first
                cropper.setCropBoxData(cropBoxData).setCanvasData(canvasData);
            },
            crop: function (e) {
                var data = e.detail;
                $('input[name="picture_doResize_' + id).val("true");
                $('input[name="picture_x1_' + id).val(data.x);
                $('input[name="picture_x2_' + id).val(data.width);
                $('input[name="picture_y1_' + id).val(data.y);
                $('input[name="picture_y2_' + id).val(data.height);
            }
        });
    }).on('hidden.bs.modal', function () {
        cropBoxData = cropper.getCropBoxData();
        canvasData = cropper.getCanvasData();
        cropper.destroy();
    });

//
//    $(".pictureChangeSize").click(function () {
//        var id = $(this).attr("data");
//        var dependPicture = $(this).attr("dependPicture");
//        var image = document.querySelector("#picture_" + dependPicture);
//        var minAspectRatio = 0.5;
//        var maxAspectRatio = 1.5;
//    });
});