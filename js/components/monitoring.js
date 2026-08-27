$(function() {
    timezoneJS.timezone.zoneFileBasePath = "/integration/javascripts/flot/tz";
    timezoneJS.timezone.defaultZoneFile = [];
    timezoneJS.timezone.init({async: false});
    $.plot("#daily", [{data: daily, label: "&nbsp; - Товаров на складе"}], {
        xaxis: {
            mode: "time",
            minTickSize: [1, "day"],
            timeformat: "%d.%m",
            timezone: "browser"
        },
        series: {
            lines: {
                show: true
            },
            points: {
                show: true
            }
        },
        grid: {
            hoverable: true,
            clickable: true
        }
    });
    $("<div id='tooltip'></div>").css({
        position: "absolute",
        display: "none",
        border: "1px solid #fdd",
        padding: "2px",
        "background-color": "#fee",
        opacity: 0.80
    }).appendTo("body");
    $("#daily").bind("plotclick", function(event, pos, item) {
        if (item) {
            var x = item.datapoint[0].toFixed(2),
                    y = item.datapoint[1].toFixed(2);
            var date = new Date(parseInt(x));
            var day = date.getDate();
            var month = date.getMonth() + 1;
            if (day < 10)
                day = "0" + day;
            if (month < 10)
                month = "0" + month;
            $("#tooltip").html(day + "." + month + " - <b>" + Math.round(y) + "</b>")
                    .css({top: item.pageY + 5, left: item.pageX + 5})
                    .fadeIn(200);
        } else {
            $("#tooltip").hide();
        }
    });
});
