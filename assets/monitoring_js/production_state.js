$(function(){
  $("#monitoring").addClass("open");
  $("#production_state").addClass("active");
  if($("#monitoring").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
  if($("#production_state").find("a").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }

  setDateBox();
});
var stock_qty1 = "";
var start_qty1 = "";
var complete_qty1 = "";
var defect_qty1 = "";
var release_qty1 = "";
var stock_qty2 = "";
var start_qty2 = "";
var complete_qty2 = "";
var defect_qty2 = "";
var release_qty2 = "";
var stock_qty3 = "";
var start_qty3 = "";
var complete_qty3 = "";
var defect_qty3 = "";
var release_qty3 = "";

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

  if(getParam("years") != ""){
    $("#years_select").val(getParam("years"));
  }else{
    $("#years_select").val(today_year);
  }
  if(getParam("monthly") != ""){
    $("#monthly_select").val(getParam("monthly"));
  }else{
    $("#monthly_select").val(today_month);
  }
  injection_status_cnt();
}

function injection_status_cnt(){
  var years_select = $("#years_select").val();
  var monthly_select = $("#monthly_select").val();

  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/injection/main/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        type: "product",
        year: years_select,
        month: monthly_select
      }
  }).done(function (result, textStatus, xhr) {
    console.log(result);
    var jsonResult = result.data;
    if(result.status == 200){
      $("#stock_qty1").text(comma(jsonResult.put_qty));
      $("#start_qty1").text(comma(jsonResult.start_qty));
      $("#complete_qty1").text(comma(jsonResult.complete_qty));
      $("#defect_qty1").text(comma(jsonResult.defect_qty));
      $("#release_qty1").text(comma(jsonResult.release_qty));

      stock_qty1 = jsonResult.put_qty;
      start_qty1 = jsonResult.start_qty;
      complete_qty1 = jsonResult.complete_qty;
      defect_qty1 = jsonResult.defect_qty;
      release_qty1 = jsonResult.release_qty;

      painting_status_cnt();
    }else{
      alert(result.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });

}

function painting_status_cnt(){
  var years_select = $("#years_select").val();
  var monthly_select = $("#monthly_select").val();

  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/painting/main/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        type: "product",
        year: years_select,
        month: monthly_select
      }
  }).done(function (result, textStatus, xhr) {
    console.log(result);
    var jsonResult = result.data;
    if(result.status == 200){
      $("#stock_qty2").text(comma(jsonResult.put_qty));
      $("#start_qty2").text(comma(jsonResult.start_qty));
      $("#complete_qty2").text(comma(jsonResult.complete_qty));
      $("#defect_qty2").text(comma(jsonResult.defect_qty));
      $("#release_qty2").text(comma(jsonResult.release_qty));

      stock_qty2 = jsonResult.put_qty;
      start_qty2 = jsonResult.start_qty;
      complete_qty2 = jsonResult.complete_qty;
      defect_qty2 = jsonResult.defect_qty;
      release_qty2 = jsonResult.release_qty;

      assemble_status_cnt();
    }else{
      alert(result.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });

}

function assemble_status_cnt(){
  var years_select = $("#years_select").val();
  var monthly_select = $("#monthly_select").val();

  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/assemble/main/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        type: "product",
        year: years_select,
        month: monthly_select
      }
  }).done(function (result, textStatus, xhr) {
    console.log(result);
    var jsonResult = result.data;
    if(result.status == 200){
      $("#stock_qty3").text(comma(jsonResult.put_qty));
      $("#start_qty3").text(comma(jsonResult.start_qty));
      $("#complete_qty3").text(comma(jsonResult.complete_qty));
      $("#defect_qty3").text(comma(jsonResult.defect_qty));
      $("#release_qty3").text(comma(jsonResult.release_qty));

      stock_qty3 = jsonResult.put_qty;
      start_qty3 = jsonResult.start_qty;
      complete_qty3 = jsonResult.complete_qty;
      defect_qty3 = jsonResult.defect_qty;
      release_qty3 = jsonResult.release_qty;

      chart_start();
    }else{
      alert(result.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });

}

function chart_start(){
am4core.ready(function() {
  // Themes begin
  am4core.useTheme(am4themes_animated);
  // Themes end
  var chart = am4core.create('chartdiv', am4charts.XYChart)
  chart.colors.step = 2;
  chart.legend = new am4charts.Legend()
  chart.legend.position = 'top'
  chart.legend.paddingBottom = 20
  chart.legend.labels.template.maxWidth = 95

  var xAxis = chart.xAxes.push(new am4charts.CategoryAxis())
  xAxis.dataFields.category = 'category'
  xAxis.renderer.cellStartLocation = 0.1
  xAxis.renderer.cellEndLocation = 0.9
  xAxis.renderer.grid.template.location = 0;

  var yAxis = chart.yAxes.push(new am4charts.ValueAxis());
  yAxis.min = 0;

  function createSeries(value, name) {
      var series = chart.series.push(new am4charts.ColumnSeries())
      series.dataFields.valueY = value
      series.dataFields.categoryX = 'category'
      series.name = name

      series.events.on("hidden", arrangeColumns);
      series.events.on("shown", arrangeColumns);

      var bullet = series.bullets.push(new am4charts.LabelBullet())
      bullet.interactionsEnabled = false
      bullet.dy = -8;
      bullet.label.text = '{valueY}'
      bullet.label.fill = am4core.color('#000')

      return series;
  }

  chart.data = [
      {
          category: '입고',
          injection: stock_qty1,
          painting: stock_qty2,
          assemble: stock_qty3
      },
      {
          category: '공정시작',
          injection: start_qty1,
          painting: start_qty2,
          assemble: start_qty3
      },
      {
          category: '공정완료',
          injection: complete_qty1,
          painting: complete_qty2,
          assemble: complete_qty3
      },
      {
          category: '불량현황',
          injection: defect_qty1,
          painting: defect_qty2,
          assemble: defect_qty3
      },
      {
          category: '출고현황',
          injection: release_qty1,
          painting: release_qty2,
          assemble: release_qty3
      }
  ]

  createSeries('injection', '사출');
  createSeries('painting', '도장');
  createSeries('assemble', '조립');

  function arrangeColumns() {
      var series = chart.series.getIndex(0);
      var w = 1 - xAxis.renderer.cellStartLocation - (1 - xAxis.renderer.cellEndLocation);
      if (series.dataItems.length > 1) {
          var x0 = xAxis.getX(series.dataItems.getIndex(0), "categoryX");
          var x1 = xAxis.getX(series.dataItems.getIndex(1), "categoryX");
          var delta = ((x1 - x0) / chart.series.length) * w;
          if (am4core.isNumber(delta)) {
              var middle = chart.series.length / 2;

              var newIndex = 0;
              chart.series.each(function(series) {
                  if (!series.isHidden && !series.isHiding) {
                      series.dummyData = newIndex;
                      newIndex++;
                  }
                  else {
                      series.dummyData = chart.series.indexOf(series);
                  }
              })
              var visibleCount = newIndex;
              var newMiddle = visibleCount / 2;

              chart.series.each(function(series) {
                  var trueIndex = chart.series.indexOf(series);
                  var newIndex = series.dummyData;

                  var dx = (newIndex - trueIndex + middle - newMiddle) * delta

                  series.animate({ property: "dx", to: dx }, series.interpolationDuration, series.interpolationEasing);
                  series.bulletsContainer.animate({ property: "dx", to: dx }, series.interpolationDuration, series.interpolationEasing);
              })
          }
      }
  }
}); // end am4core.ready()
}

$("#search_btn").on("click", function(){
  location.href="./production_state.html?years="+$("#years_select").val()+"&monthly="+$("#monthly_select").val();
});
