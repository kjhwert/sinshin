am4core.ready(function() {

var chart_data = [];
$.ajax({
    type    : "GET",
    url        : "../api/automobile/stock/log/index.php",
    headers : {
      "content-type": "application/json",
      Authorization : user_data.token,
    },
    dataType:"json",
    data:{
      type: "main"
    }
}).done(function (result, textStatus, xhr) {
  var jsonResult = result.data;
  console.log(jsonResult);
  var no_name = ["　","　　","　　　","　　　　","　　　　　","　　　　　　","　　　　　　　","　　　　　　　　","　　　　　　　　　","　　　　　　　　　　"];
  for(var i in jsonResult){
    if(jsonResult[i].product_name == ""){
      jsonResult[i].product_name = no_name[i];
    }
    chart_data.push({country: jsonResult[i].product_name, visits: jsonResult[i].remain_qty});
  };
  am4core.useTheme(am4themes_animated);
  // Themes end

  var chart = am4core.create("chartdiv", am4charts.XYChart);
  chart.hiddenState.properties.opacity = 0; // this creates initial fade-in

  chart.data = chart_data;

  var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
  categoryAxis.renderer.grid.template.location = 0;
  categoryAxis.dataFields.category = "country";
  categoryAxis.renderer.minGridDistance = 40;
  categoryAxis.fontSize = 11;
  categoryAxis.stacked = false;

  var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
  valueAxis.min = 0;
  valueAxis.stacked = false;

  //valueAxis.max = 10000;
  valueAxis.strictMinMax = true;
  valueAxis.renderer.minGridDistance = 30;
  // axis break
  var axisBreak = valueAxis.axisBreaks.create();
  // axisBreak.startValue = 2100;
  // axisBreak.endValue = 22900;
  // axisBreak.breakSize = 0.005;

  // fixed axis break
  var d = (axisBreak.endValue - axisBreak.startValue) / (valueAxis.max - valueAxis.min);
  axisBreak.breakSize = 0.05 * (1 - d) / d; // 0.05 means that the break will take 5% of the total value axis height

  // make break expand on hover
  var hoverState = axisBreak.states.create("hover");
  hoverState.properties.breakSize = 1;
  hoverState.properties.opacity = 0.1;
  hoverState.transitionDuration = 1500;

  axisBreak.defaultState.transitionDuration = 1000;

  var series = chart.series.push(new am4charts.ColumnSeries());
  series.dataFields.categoryX = "country";
  series.dataFields.valueY = "visits";
  series.stacked = false;
  series.columns.template.tooltipText = "{valueY.value}";
  series.columns.template.tooltipY = 0;
  series.columns.template.strokeOpacity = 0;

  // as by default columns of the same series are of the same color, we add adapter which takes colors from chart.colors color set
  series.columns.template.adapter.add("fill", function(fill, target) {
    return chart.colors.getIndex(target.dataItem.index);
  });
  // chart.events.on("ready", function(ev){
  //   var tsapn = document.querySelectorAll('#chartdiv text');
  //   alert(tspan.length);
  // });

}).fail(function(data, textStatus, errorThrown){
  console.log("전송 실패");
});

// Themes begin


}); // end am4core.ready()
