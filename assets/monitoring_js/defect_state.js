$(function(){
  $("#monitoring").addClass("open");
  $("#defect_state").addClass("active");
  if($("#monitoring").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
  if($("#defect_state").find("a").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }

  setDateBox();
});


var defect_data1 = "";
var defect_data2 = "";
var defect_data3 = "";

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

  if(getParam("month") == ""){
    $("#monthly_select").val(today_month);
  }else{
    $("#monthly_select").val(getParam("month"));
  }
  data1();
  month_chart();
}

function data1(){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/statistic/defect/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        year: $("#years_select").val(),
        month: $("#monthly_select").val()
      }
  }).done(function (result, textStatus, xhr) {
    var jsonResult = result.data;
    console.log(jsonResult);
    var injection_data = "<tr><th>전체</th><td id='total_color1'><b><span id='total_qty1' class='fs-18'></span></b> &nbsp;<span></span><span id='pre_total_qty1'></span></td></tr>";
    var painting_data =  "<tr><th>전체</th><td id='total_color2'><b><span id='total_qty2' class='fs-18'></span></b> &nbsp;<span id='pre_total_qty2'></span></td></tr>";
    var assemble_data =  "<tr><th>전체</th><td id='total_color3'><b><span id='total_qty3' class='fs-18'></span></b> &nbsp;<span id='pre_total_qty3'></span></td></tr>";
    var total_qty1 = Number(0);//사출
    var total_qty2 = Number(0);//도장
    var total_qty3 = Number(0);//조립

    var pre_total_qty1 = Number(0);//사출
    var pre_total_qty2 = Number(0);//도장
    var pre_total_qty3 = Number(0);//조립

    for(var i in jsonResult.injection){
      var sum_qty1 = comma(Number(jsonResult.injection[i].this_month_qty) - Number(jsonResult.injection[i].pre_month_qty));
      total_qty1 = total_qty1 + Number(jsonResult.injection[i].this_month_qty);
      pre_total_qty1 = pre_total_qty1 + Number(jsonResult.injection[i].pre_month_qty);
      injection_data +='<tr>';
      injection_data +='  <th>'+jsonResult.injection[i].defect_name+'</th>';
      if(sum_qty1.substr(0,1) == "-"){
        injection_data +='  <td><span class="fs-18">'+comma(jsonResult.injection[i].this_month_qty)+'</span><span class="color-blue"> &nbsp;▼ '+sum_qty1+' ('+jsonResult.injection[i].percent+'%)</span></td>';
      }else{
        injection_data +='  <td><span class="fs-18">'+comma(jsonResult.injection[i].this_month_qty)+'</span><span class="color-red"> &nbsp;▲ '+sum_qty1+' ('+jsonResult.injection[i].percent+'%)</span></td>';
      }
      injection_data +='</tr>';
    }
    for(var i in jsonResult.painting){
      var sum_qty2 = comma(Number(jsonResult.painting[i].this_month_qty) - Number(jsonResult.painting[i].pre_month_qty));
      total_qty2 = total_qty2 + Number(jsonResult.painting[i].this_month_qty);
      pre_total_qty2 = pre_total_qty2 + Number(jsonResult.painting[i].pre_month_qty);
      painting_data +='<tr>';
      painting_data +='  <th>'+jsonResult.painting[i].defect_name+'</th>';
      if(sum_qty2.substr(0,1) == "-"){
        painting_data +='  <td><span class="fs-18">'+comma(jsonResult.painting[i].this_month_qty)+'</span><span class="color-blue"> &nbsp;▼ '+sum_qty2+' ('+jsonResult.painting[i].percent+'%)</span></td>';
      }else{
        painting_data +='  <td><span class="fs-18">'+comma(jsonResult.painting[i].this_month_qty)+'</span><span class="color-red"> &nbsp;▲ '+sum_qty2+' ('+jsonResult.painting[i].percent+'%)</span></td>';
      }
      painting_data +='</tr>';
    }
    for(var i in jsonResult.assemble){
      var sum_qty3 = comma(Number(jsonResult.assemble[i].this_month_qty) - Number(jsonResult.assemble[i].pre_month_qty));
      total_qty3 = total_qty3 + Number(jsonResult.assemble[i].this_month_qty);
      pre_total_qty3 = pre_total_qty3 + Number(jsonResult.assemble[i].pre_month_qty);
      assemble_data +='<tr>';
      assemble_data +='  <th>'+jsonResult.assemble[i].defect_name+'</th>';
      if(sum_qty3.substr(0,1) == "-"){
        assemble_data +='  <td><span class="fs-18">'+comma(jsonResult.assemble[i].this_month_qty)+'</span><span class="color-blue"> &nbsp;▼ '+sum_qty3+' ('+jsonResult.painting[i].percent+'%)</span></span></td>';
      }else{
        assemble_data +='  <td><span class="fs-18">'+comma(jsonResult.assemble[i].this_month_qty)+'</span><span class="color-red"> &nbsp;▲ '+sum_qty3+' ('+jsonResult.painting[i].percent+'%)</span></span></td>';
      }
      assemble_data +='</tr>';
    }
    $("#defect_table1").empty();
    $("#defect_table1").append(injection_data);
    $("#defect_table2").empty();
    $("#defect_table2").append(painting_data);
    $("#defect_table3").empty();
    $("#defect_table3").append(assemble_data);
    $("#total_qty1").text(comma(total_qty1));
    $("#total_qty2").text(comma(total_qty2));
    $("#total_qty3").text(comma(total_qty3));

    var total_sum1 = comma(total_qty1 - pre_total_qty1);
    var total_sum2 = comma(total_qty2 - pre_total_qty2);
    var total_sum3 = comma(total_qty3 - pre_total_qty3);
    var total_percent1 = total_sum1 * 100 / total_qty1;
    var total_percent2 = total_sum2 * 100 / total_qty2;
    var total_percent3 = total_sum3 * 100 / total_qty3;
    if(isNaN(total_percent1)) {total_percent1 = 0;}
    if(isNaN(total_percent2)) {total_percent2 = 0;}
    if(isNaN(total_percent3)) {total_percent3 = 0;}

    if(total_sum1.substr(0,1) == "-"){
      $("#pre_total_qty1").text("▼ "+total_sum1+" ("+total_percent1.toFixed(1)+"%)");
      $("#total_color1").addClass("color-blue");
    }else{
      $("#pre_total_qty1").text("▲ "+total_sum1+" ("+total_percent1.toFixed(1)+"%)");
      $("#total_color1").addClass("color-red");
    }
    if(total_sum2.substr(0,1) == "-"){
      $("#pre_total_qty2").text("▼ "+total_sum1+" ("+total_percent2.toFixed(1)+"%)");
      $("#total_color2").addClass("color-blue");
    }else{
      $("#pre_total_qty2").text("▲ "+total_sum1+" ("+total_percent2.toFixed(1)+"%)");
      $("#total_color2").addClass("color-red");
    }
    if(total_sum2.substr(0,1) == "-"){
      $("#pre_total_qty3").text("▼ "+total_sum3+" ("+total_percent3.toFixed(1)+"%)");
      $("#total_color3").addClass("color-blue");
    }else{
      $("#pre_total_qty3").text("▲ "+total_sum3+" ("+total_percent3.toFixed(1)+"%)");
      $("#total_color3").addClass("color-red");
    }

    defect_data1 = result.data.injection;
    defect_data2 = result.data.painting;
    defect_data3 = result.data.assemble;
    chart_start();

  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

function chart_start(){

  am4core.ready(function() {
  // Themes begin
  am4core.useTheme(am4themes_animated);
  // Themes end
  var chart1 = am4core.create("chartdiv1", am4charts.PieChart3D);
  var chart2 = am4core.create("chartdiv2", am4charts.PieChart3D);
  var chart3 = am4core.create("chartdiv3", am4charts.PieChart3D);
  chart1.hiddenState.properties.opacity = 0; // this creates initial fade-in
  chart2.hiddenState.properties.opacity = 0; // this creates initial fade-in
  chart3.hiddenState.properties.opacity = 0; // this creates initial fade-in

  chart1.data = defect_data1;
  chart2.data = defect_data2;
  chart3.data = defect_data3;

  chart1.innerRadius = am4core.percent(40);
  chart2.innerRadius = am4core.percent(40);
  chart3.innerRadius = am4core.percent(40);
  chart1.depth = 120;
  chart2.depth = 120;
  chart3.depth = 120;

  chart1.legend = new am4charts.Legend();
  chart2.legend = new am4charts.Legend();
  chart3.legend = new am4charts.Legend();

  var series1 = chart1.series.push(new am4charts.PieSeries3D());
  series1.dataFields.value = "this_month_qty";
  series1.dataFields.depthValue = "this_month_qty";
  series1.dataFields.category = "defect_name";
  series1.slices.template.cornerRadius = 5;
  series1.colors.step = 3;
  series1.ticks.template.disabled = true;
  series1.alignLabels = false;
  series1.labels.template.text = "{value.percent.formatNumber('#.0')}%";
  series1.labels.template.radius = am4core.percent(-25);
  series1.labels.template.fill = am4core.color("white");

  var series2 = chart2.series.push(new am4charts.PieSeries3D());
  series2.dataFields.value = "this_month_qty";
  series2.dataFields.depthValue = "this_month_qty";
  series2.dataFields.category = "defect_name";
  series2.slices.template.cornerRadius = 5;
  series2.colors.step = 3;
  series2.ticks.template.disabled = true;
  series2.alignLabels = false;
  series2.labels.template.text = "{value.percent.formatNumber('#.0')}%";
  series2.labels.template.radius = am4core.percent(-25);
  series2.labels.template.fill = am4core.color("white");

  var series3 = chart3.series.push(new am4charts.PieSeries3D());
  series3.dataFields.value = "this_month_qty";
  series3.dataFields.depthValue = "this_month_qty";
  series3.dataFields.category = "defect_name";
  series3.slices.template.cornerRadius = 5;
  series3.colors.step = 3;
  series3.ticks.template.disabled = true;
  series3.alignLabels = false;
  series3.labels.template.text = "{value.percent.formatNumber('#.0')}%";
  series3.labels.template.radius = am4core.percent(-25);
  series3.labels.template.fill = am4core.color("white");

  }); // end am4core.ready()
}

function month_chart(){

  var month_data = [];
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/statistic/defect/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        year: $("#years_select").val()
      }
  }).done(function (result, textStatus, xhr) {
    month_data.push(result.data);
    console.log(month_data);
    am4core.ready(function() {

      // Themes begin
      am4core.useTheme(am4themes_animated);
      // Themes end

      // Create chart instance
      var chart = am4core.create("month_chart", am4charts.XYChart);

      // Add data
      chart.data = month_data[0];

      // Create category axis
      var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
      categoryAxis.dataFields.category = "month";
      categoryAxis.renderer.opposite = true;

      // Create value axis
      var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
      valueAxis.renderer.inversed = false;
      valueAxis.title.text = "불량수";
      valueAxis.renderer.minLabelPosition = 0.01;

      // Create series
      var series1 = chart.series.push(new am4charts.LineSeries());
      series1.dataFields.valueY = "injection_qty";
      series1.dataFields.categoryX = "month";
      series1.name = "사출";
      series1.bullets.push(new am4charts.CircleBullet());
      series1.tooltipText = "{categoryX} {name} 불량수 {valueY} 건";
      series1.legendSettings.valueText = "{valueY}";
      series1.visible  = false;

      var series2 = chart.series.push(new am4charts.LineSeries());
      series2.dataFields.valueY = "painting_qty";
      series2.dataFields.categoryX = "month";
      series2.name = '도장';
      series2.bullets.push(new am4charts.CircleBullet());
      series2.tooltipText = "{categoryX} {name} 불량수 {valueY} 건";
      series2.legendSettings.valueText = "{valueY}";

      var series3 = chart.series.push(new am4charts.LineSeries());
      series3.dataFields.valueY = "assemble_qty";
      series3.dataFields.categoryX = "month";
      series3.name = '조립';
      series3.bullets.push(new am4charts.CircleBullet());
      series3.tooltipText = "{categoryX} {name} 불량수 {valueY} 건";
      series3.legendSettings.valueText = "{valueY}";

      // Add chart cursor
      chart.cursor = new am4charts.XYCursor();
      chart.cursor.behavior = "zoomY";


      let hs1 = series1.segments.template.states.create("hover")
      hs1.properties.strokeWidth = 5;
      series1.segments.template.strokeWidth = 1;

      let hs2 = series2.segments.template.states.create("hover")
      hs2.properties.strokeWidth = 5;
      series2.segments.template.strokeWidth = 1;

      let hs3 = series3.segments.template.states.create("hover")
      hs3.properties.strokeWidth = 5;
      series3.segments.template.strokeWidth = 1;

      // Add legend
      chart.legend = new am4charts.Legend();
      chart.legend.itemContainers.template.events.on("over", function(event){
        var segments = event.target.dataItem.dataContext.segments;
        segments.each(function(segment){
          segment.isHover = true;
        })
      })

      chart.legend.itemContainers.template.events.on("out", function(event){
        var segments = event.target.dataItem.dataContext.segments;
        segments.each(function(segment){
          segment.isHover = false;
        })
      })

      }); // end am4core.ready()
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });


}

$("#search_btn").on("click", function(){
  location.href="./defect_state.html?years="+$("#years_select").val()+"&month="+$("#monthly_select").val();
});
