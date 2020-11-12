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
  for(var y = (com_year); y <= (com_year+15); y++){
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
    var injection_data = "<tr><th class='color-red'>전체</th><td id='total_qty1' class='color-red'></td></tr>";
    var painting_data =  "<tr><th class='color-red'>전체</th><td id='total_qty2' class='color-red'></td></tr>";
    var assemble_data =  "<tr><th class='color-red'>전체</th><td id='total_qty3' class='color-red'></td></tr>";
    var total_qty1 = Number(0);
    var total_qty2 = Number(0);
    var total_qty3 = Number(0);

    for(var i in jsonResult.injection){
      total_qty1 = total_qty1 + Number(jsonResult.injection[i].qty);
      injection_data +='<tr>';
      injection_data +='  <th>'+jsonResult.injection[i].defect_name+'</th>';
      injection_data +='  <td>'+comma(jsonResult.injection[i].qty)+'</td>';
      injection_data +='</tr>';
    }
    for(var i in jsonResult.painting){
      total_qty2 = total_qty2 + Number(jsonResult.painting[i].qty);
      painting_data +='<tr>';
      painting_data +='  <th>'+jsonResult.painting[i].defect_name+'</th>';
      painting_data +='  <td>'+comma(jsonResult.painting[i].qty)+'</td>';
      painting_data +='</tr>';
    }
    for(var i in jsonResult.assemble){
      total_qty3 = total_qty3 + Number(jsonResult.assemble[i].qty);
      assemble_data +='<tr>';
      assemble_data +='  <th>'+jsonResult.assemble[i].defect_name+'</th>';
      assemble_data +='  <td>'+comma(jsonResult.assemble[i].qty)+'</td>';
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
  series1.dataFields.value = "qty";
  series1.dataFields.depthValue = "qty";
  series1.dataFields.category = "defect_name";
  series1.slices.template.cornerRadius = 5;
  series1.colors.step = 3;
  series1.ticks.template.disabled = true;
  series1.alignLabels = false;
  series1.labels.template.text = "{value.percent.formatNumber('#.0')}%";
  series1.labels.template.radius = am4core.percent(-25);
  series1.labels.template.fill = am4core.color("white");

  var series2 = chart2.series.push(new am4charts.PieSeries3D());
  series2.dataFields.value = "qty";
  series2.dataFields.depthValue = "qty";
  series2.dataFields.category = "defect_name";
  series2.slices.template.cornerRadius = 5;
  series2.colors.step = 3;
  series2.ticks.template.disabled = true;
  series2.alignLabels = false;
  series2.labels.template.text = "{value.percent.formatNumber('#.0')}%";
  series2.labels.template.radius = am4core.percent(-25);
  series2.labels.template.fill = am4core.color("white");

  var series3 = chart3.series.push(new am4charts.PieSeries3D());
  series3.dataFields.value = "qty";
  series3.dataFields.depthValue = "qty";
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

$("#search_btn").on("click", function(){
  location.href="./defect_state.html?years="+$("#years_select").val()+"&month="+$("#monthly_select").val();
});
