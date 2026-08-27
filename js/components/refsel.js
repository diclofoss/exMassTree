$(function () {
    var generateRefselDepends = function (refselDepends) {
        var res = "";
        for (var i = 0; i < refselDepends.length; i++) {
            res += refselDepends[i] + "=" + $('select#' + refselDepends[i] + 'Id').children("option:selected").val();
        }
        return res;
    };

    var renderRefsel = function ($this) {
        var id = $this.val();
        if (id) {
            value: [id];
        }
        var refselDepends = new Array();
        for (var i = 0; i < 100; i++) {
            if ($this.attr("refseldepends" + i) == undefined) {
                continue;
            }
            refselDepends.push($this.attr("refseldepends" + i));
        }
        var onchange = new Array();
        for (var i = 0; i < 100; i++) {
            if ($this.attr("onchange" + i) == undefined) {
                continue;
            }
            onchange.push($this.attr("onchange" + i));
        }
        var category = $this.attr("category");
        var component = $this.attr("component");
        var rootElement = $this.attr("rootElement") || "";
        var parentId = $this.attr("parentId");
        var element = $this.attr("element");
        var rootElementParam = rootElement ? "&rootElement=" + encodeURIComponent(rootElement) : "";
        $this.select2({
            ajax: {
                url: "?" + generateRefselDepends(refselDepends) + "&parentId=" + parentId + "&category=" + category + "&component=" + component + rootElementParam + "&id=" + id + "&actionElement=" + element + "&action=refselData&dataType=json",
                dataType: 'json'
            }
        });
        if (onchange.length > 0) {
            $this.change(function () {
                for (var i = 0; i < onchange.length; i++) {
                    renderRefsel($("select#" + onchange[i] + "Id"));
                    $("select#" + onchange[i] + "Id").val(null).trigger('change');
                }
            });
        }
    }

    $(".refsel").each(function () {
        renderRefsel($(this));
    });
});