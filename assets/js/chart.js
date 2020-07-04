/*=========================================================================================
    File Name: dashboard-ecommerce.js
    Description: dashboard-ecommerce
    ----------------------------------------------------------------------------------------
    Item Name: Chameleon Admin - Modern Bootstrap 4 WebApp & Dashboard HTML Template + UI Kit
    Version: 1.0
    Author: ThemeSelection
    Author URL: https://themeselection.com/
==========================================================================================*/


(function (window, document, $) {

    /*************************************************
  *               Project Stats               *
  *************************************************/

    var barOptions = {
        axisY: {
            low: 0,
            high: 20,
            showGrid: false,
            showLabel: false,
            offset: 0
        },
        axisX: {
            showLabel: true,
            showGrid: false,
        },
        fullWidth: true,
    };


    var lineOptions = {
        axisX: {
            showLabel: false,
            showGrid: false,

        },
        axisY: {
            showLabel: false,
            showGrid: false,
            low: 0,
            high: 20,
            offset: 0
        },
        lineSmooth: Chartist.Interpolation.simple({
            divisor: 2
        }),
        fullWidth: true
    };

    var ProjectStatsBar = new Chartist.Bar('#progress-stats-bar-chart', {
        labels: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '7월', '7월', '7월', '7월', '7월'],
        series: [
            [18, 20, 14, 18, 20, 15, 18, 0, 0, 0, 0, 0]
        ]
    }, barOptions);

    ProjectStatsBar.on('draw', function (data) {
        if (data.type === 'bar') {
            data.element.attr({
                style: 'stroke-width: 25px'
            });

        }
    });


    var ProjectStatsLine = new Chartist.Line('#progress-stats-line-chart', {
        series: [
            [10, 15, 7, 12, 3, 16, 0, 0, 0, 0, 0, 0]
        ]
    }, lineOptions);

    ProjectStatsLine.on('created', function (data) {
        var defs = data.svg.querySelector('defs') || data.svg.elem('defs');
        defs.elem('linearGradient', {
            id: 'lineLinearStats',
            x1: 0,
            y1: 0,
            x2: 1,
            y2: 0
        }).elem('stop', {
            offset: '0%',
            'stop-color': 'rgba(252,98,107,0.1)'
        }).parent().elem('stop', {
            offset: '10%',
            'stop-color': 'rgba(252,98,107,1)'
        }).parent().elem('stop', {
            offset: '80%',
            'stop-color': 'rgba(252,98,107, 1)'
        }).parent().elem('stop', {
            offset: '98%',
            'stop-color': 'rgba(252,98,107, 0.1)'
        });

        return defs;


    }).on('draw', function (data) {
        var circleRadius = 5;
        if (data.type === 'point') {
            var circle = new Chartist.Svg('circle', {
                cx: data.x,
                cy: data.y,
                'ct:value': data.y,
                r: circleRadius,
                class: data.value.y === 15 ? 'ct-point ct-point-circle' : 'ct-point ct-point-circle-transperent'
            });
            data.element.replace(circle);
        }
    });


    ////////////////////////////////////////////////////////////////////////////////


})(window, document, jQuery);
