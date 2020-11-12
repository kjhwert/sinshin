$(function(){
  $("#product_history").addClass("open");
  $("#produce_chart").addClass("active");
  if($("#product_history").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
  if($("#produce_chart").find("a").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }

  setDateBox();
});

var chart_data1 = "";
var chart_data2 = "";

function setDateBox(){
  var dt = new Date();
  var year = "";
  var com_year = dt.getFullYear();
  // 발행 뿌려주기
  $("#years_select").append("<option value=''>년도</option>");
  // 올해 기준으로 -1년부터 +5년을 보여준다.
  for(var y = (com_year); y >= (com_year-15); y--){
      $("#years_select").append("<option value='"+ y +"'>"+ y + " 년" +"</option>");
  }
  // 월 뿌려주기(1월부터 12월)
  var month;
  $("#monthly_select").append("<option value=''>월</option>");
  for(var i = 1; i <= 12; i++){
      $("#monthly_select").append("<option value='"+ i +"'>"+ i + " 월" +"</option>");
  }

  let today = new Date();

  let today_year = today.getFullYear(); // 년도
  let today_month = today.getMonth() + 1;  // 월
  let today_date = today.getDate();  // 날짜
  let today_day = today.getDay();  // 요일

  if(getParam("years") == ""){
    $("#years_select").val(today_year);
  }else{
    $("#years_select").val(getParam("years"));
  }
  //$("#monthly_select").val(today_month);

  monthly_data();
}

function monthly_data(){
  $.ajax({
    type    : "GET",
    url        : "../api/cosmetics/statistic/history/index.php",
    contentType: "appxlication/json",
    headers : {
      "content-type": "application/json",
      Authorization : user_data.token,
    },
    dataType:"json",
    data     : {
      year: $("#years_select").val(),
      type: "product"
    }
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      chart_data1 = result.data;

      defect_data();
    }else{
      alert(result.message);
    }
  }).fail(function(data, textStatus, errorThrown){
      console.log("전송 실패");
  });
}

function defect_data(){
  $.ajax({
    type    : "GET",
    url        : "../api/cosmetics/statistic/history/index.php",
    contentType: "appxlication/json",
    headers : {
      "content-type": "application/json",
      Authorization : user_data.token,
    },
    dataType:"json",
    data     : {
      year: $("#years_select").val(),
      type: "defect"
    }
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      chart_data2 = result.data;
      console.log(chart_data2);

      am4core.ready(function() {
      am4core.useTheme(am4themes_animated);
      var chart = am4core.create("chartdiv", am4charts.XYChart);
      var chart2 = am4core.create("chartdiv2", am4charts.XYChart);
      // Add data
      chart.data = chart_data1;

      chart2.data = chart_data2;

      // Create axes
      var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
      categoryAxis.dataFields.category = "month";
      categoryAxis.renderer.grid.template.location = 0;


      var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
      valueAxis.renderer.inside = true;
      valueAxis.renderer.labels.template.disabled = true;
      valueAxis.min = 0;

      var categoryAxis2 = chart2.xAxes.push(new am4charts.CategoryAxis());
      categoryAxis2.dataFields.category = "month";
      categoryAxis2.renderer.grid.template.location = 0;


      var valueAxis2 = chart2.yAxes.push(new am4charts.ValueAxis());
      valueAxis2.renderer.inside = true;
      valueAxis2.renderer.labels.template.disabled = true;
      valueAxis2.min = 0;

      // Create series
      function createSeries(field, name) {

        // Set up series
        var series = chart.series.push(new am4charts.ColumnSeries());
        series.name = name;
        series.dataFields.valueY = field;
        series.dataFields.categoryX = "month";
        series.sequencedInterpolation = true;

        // Make it stacked
        series.stacked = true;

        // Configure columns
        series.columns.template.width = am4core.percent(60);
        series.columns.template.tooltipText = "[bold]{name}[/]\n[font-size:14px]{categoryX}: {valueY}";

        // Add label
        var labelBullet = series.bullets.push(new am4charts.LabelBullet());
        labelBullet.label.text = "{valueY}";
        labelBullet.locationY = 0.5;
        labelBullet.label.hideOversized = true;

        return series;
      }

      // Create series
      function createSeries2(field, name) {

        // Set up series
        var series = chart2.series.push(new am4charts.ColumnSeries());
        series.name = name;
        series.dataFields.valueY = field;
        series.dataFields.categoryX = "month";
        series.sequencedInterpolation = true;

        // Make it stacked
        series.stacked = true;

        // Configure columns
        series.columns.template.width = am4core.percent(60);
        series.columns.template.tooltipText = "[bold]{name}[/]\n[font-size:14px]{categoryX}: {valueY}";

        // Add label
        var labelBullet = series.bullets.push(new am4charts.LabelBullet());
        labelBullet.label.text = "{valueY}";
        labelBullet.locationY = 0.5;
        labelBullet.label.hideOversized = true;

        return series;
      }

      createSeries("injection", "사출");
      createSeries("painting", "도장");
      createSeries("assemble", "조립");

      createSeries2("injection", "사출");
      createSeries2("painting", "도장");
      createSeries2("assemble", "조립");
      // Legend
      chart.legend = new am4charts.Legend();
      chart2.legend = new am4charts.Legend();
      }); // end am4core.ready()

    }else{
      alert(result.message);
    }
  }).fail(function(data, textStatus, errorThrown){
      console.log("전송 실패");
  });
}

$("#search_btn").on("click", function(){
  location.href="../product_history/chart.html?years="+$("#years_select").val();
});
