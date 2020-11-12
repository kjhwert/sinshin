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

$("#search_btn_cnt").on("click", function(){
    $("#search_modal").fadeIn(300);
    $("#modal_back").fadeIn(300);
    $("#search_text").val("");
    $("#search_table").empty();
    $(".search_result_box").css("display","none");
});
function modal_off(){
  $("#search_modal").fadeOut(300);
  $("#modal_back").fadeOut(300);
}

$("#modal_back").on("click", function(){
  modal_off();
});

$("#search_text").keydown(function(key) {
  if (key.keyCode == 13) {
    var search_text = $("#search_text").val();

    if(search_text == ""){
      alert("제품명을 입력해주세요");
      return;
    }
    if(search_text.length < 2){
      alert("두글자 이상 입력해주세요");
      retrun;
    }
    $.ajax({
        type    : "GET",
        url        : "../api/cosmetics/master/product/index.php",
        headers : {
          "content-type": "application/json",
          Authorization : user_data.token,
        },
        dataType:"json",
        data:{
          search: search_text,
          type: "search"
        }
    }).done(function (result, textStatus, xhr) {
      if(result.status == 200){
        console.log(result);
        var jsonResult = result.data;
        $(".search_result_box").css("display","block");

        var text  = "<tr>";
            text += " <th>제품명</th>";
            text += " <th>유형</th>";
            text += "</tr>";

        if(jsonResult.length == 0){
          text += '<tr><td>검색결과가 없습니다</td></tr>';
        }else{
          for(var i in jsonResult){
            text +='<tr data-id='+jsonResult[i].id+'>';
            text +=" <td>"+jsonResult[i].name+"</td>";
            text +=" <td>"+jsonResult[i].process_type+"</td>";
            text +="</tr>";
          }
        }

        $("#search_table").empty();
        $("#search_table").append(text);
        $("#search_table tr").on("click", function(){
          var code = $(this).data("id");

          $("#product_id").val(code);
          $("#product_name").val($(this).children("td").text());

          modal_off();
          data1();
        })
      }else{
        alert(result.message);
      }
    }).fail(function(data, textStatus, errorThrown){
      console.log("전송 실패");
    });
  }
});

$("#search_btn").on("click", function(){
  var search_text = $("#search_text").val();

  if(search_text == ""){
    alert("제품명을 입력해주세요");
    return;
  }
  if(search_text.length < 2){
    alert("두글자 이상 입력해주세요");
    retrun;
  }
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/master/product/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        search: search_text,
        type: "search"
      }
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      console.log(result);
      var jsonResult = result.data;
      $(".search_result_box").css("display","block");

      var text = "<tr>";
          text += "<th>제품명</th>";
          text += "</tr>";

      if(jsonResult.length == 0){
        text += '<tr><td>검색결과가 없습니다</td></tr>';
      }else{
        for(var i in jsonResult){
          text +='<tr data-id='+jsonResult[i].id+'>';
          text +="  <td>"+jsonResult[i].name+"</td>";
          text +="</tr>";
        }
      }

      $("#search_table").empty();
      $("#search_table").append(text);
      $("#search_table tr").on("click", function(){
        var code = $(this).data("id");

        $("#product_id").val(code);
        $("#product_name").val($(this).children("td").text());

        modal_off();
        data1();
      })
    }else{
      alert(result.message);
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
});

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
}

function data1(){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/statistic/history/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        year: $("#years_select").val(),
        id: $("#product_id").val(),
        type: "product"
      }
  }).done(function (result, textStatus, xhr) {
    var jsonResult = result.data;

    chart_data1 = jsonResult;

    data2();
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

function data2(){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/statistic/history/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        year: $("#years_select").val(),
        id: $("#product_id").val(),
        type: "defect"
      }
  }).done(function (result, textStatus, xhr) {
    var jsonResult = result.data;

    chart_data2 = jsonResult;

    chart_start();
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

function chart_start(){
  console.log(chart_data1);
  console.log(chart_data2);

  am4core.ready(function() {
  am4core.useTheme(am4themes_animated);
  var chart = am4core.create("chartdiv1", am4charts.XYChart);
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

  createSeries("qty", $("#product_name").val());

  createSeries2("qty", $("#product_name").val());
  // Legend
  chart.legend = new am4charts.Legend();
  chart2.legend = new am4charts.Legend();
  }); // end am4core.ready()
}
