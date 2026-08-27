$(document).ready(function () {
    var ctrlDown = false,
            ctrlKey = 17,
            cmdKey = 91,
            vKey = 86,
            cKey = 67;
    $(document).keydown(function (e) {
        if (e.keyCode == ctrlKey || e.keyCode == cmdKey)
            ctrlDown = true;
    }).keyup(function (e) {
        if (e.keyCode == ctrlKey || e.keyCode == cmdKey)
            ctrlDown = false;
    });
    $(".cordParcer").keydown(function (e) {
        var _this = $(this);
        if (ctrlDown && (e.keyCode == vKey)) {
            setTimeout(function () {
                let found = false;
                if (!found) {
                    var tdmxEx = /\d+, [^,]+, \d+, ([-+]?[0-9]*\.?[0-9]+), ([-+]?[0-9]*\.?[0-9]+), ([-+]?[0-9]*\.?[0-9]+), ([-+]?[0-9]*\.?[0-9]+), -?\d+/;
                    var tdmxValues = _this.val().match(tdmxEx);
                    if (tdmxValues) {
                        var dataList = _this.attr('data').split(':');
                        for (var i = 0; i < dataList.length; i++) {
                            if (tdmxValues.length < i) {
                                continue;
                            }
                            $("input[name=\"" + dataList[i] + "\"]").val(tdmxValues[i + 1]);
                        }
                        found = true;
                    }
                }
                if (!found) {
                    var tdmxEx = /([-+]?[0-9]*\.?[0-9]+)\s+([-+]?[0-9]*\.?[0-9]+)\s+([-+]?[0-9]*\.?[0-9]+)\s+([-+]?[0-9]*\.?[0-9]+)\s+([-+]?[0-9]*\.?[0-9]+)\s+([-+]?[0-9]*\.?[0-9]+)/;
                    var tdmxValues = _this.val().match(tdmxEx);
                    if (tdmxValues) {
                        var dataList = _this.attr('data').split(':');
                        for (var i = 0; i < dataList.length; i++) {
                            if (tdmxValues.length < i) {
                                continue;
                            }
                            $("input[name=\"" + dataList[i] + "\"]").val(tdmxValues[i + 1]);
                        }
                        found = true;
                    }
                }
                if (!found && (_this.val().startsWith("AddStaticVehicle") || _this.val().startsWith("AddPlayerClass"))) {
                    var regex1 = /[-+]?[0-9]*\.?[0-9]+/g;
                    var values = _this.val().match(regex1);
                    if (!values) {
                        return;
                    }
                    var dataList = _this.attr('data').split(':');
                    for (var i = 0; i < dataList.length; i++) {
                        if (values.length < i) {
                            continue;
                        }
                        $("input[name=\"" + dataList[i] + "\"]").val(values[i + 1]);
                    }
                }
            }, 100);
        }
    });
});