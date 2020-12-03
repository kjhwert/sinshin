var weekly_injection = [];
var weekly_painting = [];
var weekly_assemble = [];
var weekly_date = [];
var injection_defect_data = [];
var painting_defect_data = [];
var assemble_defect_data = [];
let today = new Date();

let today_year = today.getFullYear(); // 년도
let today_month = today.getMonth() + 1;  // 월
let today_date = today.getDate();  // 날짜
let today_day = today.getDay();  // 요일

injection_status();
painting_status();
assemble_status();
machine_data(); //사출기현황
weekly_data(); //주간생산현황
defect_data(); //공정별 불량률
data1(); //유형별 불량률





function weekly_data(){
  $.ajax({
    type    : "GET",
    url        : "../api/cosmetics/index.php",
    headers : {
      "content-type": "application/json",
      Authorization : user_data.token,
    },
    dataType:"json",
    data:{
      type: "week"
    }
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      var jsonResult = result.data;
      console.log(jsonResult);
      weekly_injection.push(jsonResult.injection);
      weekly_painting.push(jsonResult.painting);
      weekly_assemble.push(jsonResult.assemble);
      weekly_date.push(jsonResult.date);

      weekly_graph();
    }else{
      alert(result.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

function defect_data(){
  $.ajax({
    type    : "GET",
    url        : "../api/cosmetics/index.php",
    headers : {
      "content-type": "application/json",
      Authorization : user_data.token,
    },
    dataType:"json",
    data:{
      type: "defect"
    }
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      var jsonResult = result.data;
      console.log(result);
      injection_defect_data.push(jsonResult.injection);
      painting_defect_data.push(jsonResult.painting);
      assemble_defect_data.push(jsonResult.assemble);

        injection_defect_graph();
        painting_defect_graph();
        assemble_defect_graph();

      $("#injection_total_qty1").text(comma(Number(injection_defect_data[0][0].litres)+Number(injection_defect_data[0][1].litres)));
      $("#injection_total_qty2").text(comma(Number(injection_defect_data[0][0].litres)));
      $("#injection_total_qty3").text(comma(Number(injection_defect_data[0][1].litres)));

      $("#painting_total_qty1").text(comma(Number(painting_defect_data[0][0].litres)+Number(painting_defect_data[0][1].litres)));
      $("#painting_total_qty2").text(comma(Number(painting_defect_data[0][0].litres)));
      $("#painting_total_qty3").text(comma(Number(painting_defect_data[0][1].litres)));

      $("#assemble_total_qty1").text(comma(Number(assemble_defect_data[0][0].litres)+Number(assemble_defect_data[0][1].litres)));
      $("#assemble_total_qty2").text(comma(Number(assemble_defect_data[0][0].litres)));
      $("#assemble_total_qty3").text(comma(Number(assemble_defect_data[0][1].litres)));

    }else{
      alert(result.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}





function weekly_graph(){
  am4core.ready(function() {
  // Themes begin
  am4core.useTheme(am4themes_animated);
  // Themes end
  // Create chart instance
  var chart = am4core.create("weekly_div1", am4charts.XYChart);
  // Create axes
  var xAxis = chart.xAxes.push(new am4charts.ValueAxis());
  xAxis.min = 0;
  xAxis.strictMinMax = true;
  xAxis.renderer.grid.template.disabled = true;
  xAxis.renderer.labels.template.disabled = true;

  var yAxis = chart.yAxes.push(new am4charts.ValueAxis());
  yAxis.min = 0;
  // Create series
  function createSeries(name, data) {
    // Create series itself
    var series = chart.series.push(new am4charts.StepLineSeries());
    series.dataFields.valueX = "ax";
    series.dataFields.valueY = "ay";
    series.strokeWidth = 3;
    series.fillOpacity = 0.2;
    series.stacked = true;
    series.name = name;
    series.data = data;

    // Create series for bullets
    var bulletSeries = chart.series.push(new am4charts.ColumnSeries());
    bulletSeries.dataFields.valueX = "ax";
    bulletSeries.dataFields.valueY = "ay";
    bulletSeries.stacked = true;
    bulletSeries.fillOpacity = 0;
    bulletSeries.hiddenInLegend = true;

    var bullet = bulletSeries.bullets.push(new am4charts.LabelBullet);
    bullet.label.text = "{valueY}";
    bullet.label.truncate = false;
    bullet.label.background.fill = am4core.color("#fff");
    bullet.label.background.fillOpacity = 0.5;
    bullet.label.padding(3, 6, 3, 6);
    bullet.locationY = 0.5;

    var bulletSeriesData = [];
    for(var i = 1; i < data.length; i++) {
      bulletSeriesData.push({
        "ax": data[i].ax - (data[i].ax - data[i-1].ax) / 2,
        "ay": data[i-1].ay
      })
    }
    bulletSeries.data = bulletSeriesData;

    // Save reference to related bullet series
    series.dummyData = {
      bulletSeries: bulletSeries
    };

    // Set up events to hide/show related bullet series when series is toggled
    series.events.on("hidden", function(ev) {
      ev.target.dummyData.bulletSeries.hide();
    });

    series.events.on("shown", function(ev) {
      ev.target.dummyData.bulletSeries.show();
    });

    return series;
  }

  var series1 = createSeries(
    "사출",
    [
      { "ax": 0, "ay": weekly_injection[0][0].qty },
      { "ax": 14, "ay": weekly_injection[0][1].qty },
      { "ax": 28, "ay": weekly_injection[0][2].qty },
      { "ax": 42, "ay": weekly_injection[0][3].qty },
      { "ax": 56, "ay": weekly_injection[0][4].qty },
      { "ax": 70, "ay": weekly_injection[0][5].qty },
      { "ax": 84, "ay": weekly_injection[0][6].qty },
      { "ax": 100, "ay": weekly_injection[0][7].qty }
    ]
  );

  var series2 = createSeries(
    "도장",
    [
      { "ax": 0, "ay": weekly_painting[0][0].qty },
      { "ax": 14, "ay": weekly_painting[0][1].qty },
      { "ax": 28, "ay": weekly_painting[0][2].qty },
      { "ax": 42, "ay": weekly_painting[0][3].qty },
      { "ax": 56, "ay": weekly_painting[0][4].qty },
      { "ax": 70, "ay": weekly_painting[0][5].qty },
      { "ax": 84, "ay": weekly_painting[0][6].qty },
      { "ax": 100, "ay": weekly_painting[0][7].qty }
    ]
  );

  var series3 = createSeries(
    "조립",
    [
      { "ax": 0, "ay": weekly_assemble[0][1].qty },
      { "ax": 14, "ay": weekly_assemble[0][1].qty },
      { "ax": 28, "ay": weekly_assemble[0][2].qty },
      { "ax": 42, "ay": weekly_assemble[0][3].qty },
      { "ax": 56, "ay": weekly_assemble[0][4].qty },
      { "ax": 70, "ay": weekly_assemble[0][5].qty },
      { "ax": 84, "ay": weekly_assemble[0][6].qty },
      { "ax": 100, "ay": weekly_assemble[0][7].qty }
    ]
  );

  // Create labels
  function createLabel(from, to, text) {
    var range = xAxis.axisRanges.create();
    range.value = from;
    range.endValue = to;
    range.label.text = text;
    range.grid.location = 1;
  }

  createLabel(14, 0, weekly_date[0][0].day+"일");
  createLabel(28, 14, weekly_date[0][1].day+"일");
  createLabel(42, 28, weekly_date[0][2].day+"일");
  createLabel(56, 42, weekly_date[0][3].day+"일");
  createLabel(70, 56, weekly_date[0][4].day+"일");
  createLabel(84, 70, weekly_date[0][5].day+"일");
  createLabel(100, 84, weekly_date[0][6].day+"일");

  // Scrollbar
  chart.scrollbarX = new am4core.Scrollbar();

  // Legend
  chart.legend = new am4charts.Legend();


  }); // end am4core.ready()
}

function injection_defect_graph(){
  am4core.ready(function() {
  // Themes begin
  am4core.useTheme(am4themes_animated);
  // Themes end

  // Create chart instance
  var chart = am4core.create("pie1", am4charts.PieChart);

  // Add and configure Series
  var pieSeries = chart.series.push(new am4charts.PieSeries());
  pieSeries.dataFields.value = "litres";
  pieSeries.dataFields.category = "country";

  // Let's cut a hole in our Pie chart the size of 30% the radius
  chart.innerRadius = am4core.percent(30);

  // Put a thick white border around each Slice
  pieSeries.slices.template.stroke = am4core.color("#fff");
  pieSeries.slices.template.strokeWidth = 2;
  pieSeries.slices.template.strokeOpacity = 1;

  pieSeries.slices.template
    // change the cursor on hover to make it apparent the object can be interacted with
    .cursorOverStyle = [
      {
        "property": "cursor",
        "value": "pointer"
      }
    ];
  pieSeries.alignLabels = false;
  pieSeries.labels.template.bent = true;
  pieSeries.labels.template.radius = 3;
  pieSeries.labels.template.padding(0,0,0,0);
  pieSeries.ticks.template.disabled = true;
  pieSeries.labels.template.disabled = true;
  // Create a base filter effect (as if it's not there) for the hover to return to
  var shadow = pieSeries.slices.template.filters.push(new am4core.DropShadowFilter);
  shadow.opacity = 0;
  // Create hover state
  var hoverState = pieSeries.slices.template.states.getKey("hover"); // normally we have to create the hover state, in this case it already exists
  // Slightly shift the shadow and make it more prominent on hover
  var hoverShadow = hoverState.filters.push(new am4core.DropShadowFilter);
  hoverShadow.opacity = 0.7;
  hoverShadow.blur = 5;
  // Add a legend
  chart.legend = new am4charts.Legend();
  chart.data = injection_defect_data[0];
  }); // end am4core.ready()
}
function painting_defect_graph(){
  am4core.ready(function() {
  // Themes begin
  am4core.useTheme(am4themes_animated);
  // Themes end

  // Create chart instance
  var chart = am4core.create("pie2", am4charts.PieChart);

  // Add and configure Series
  var pieSeries = chart.series.push(new am4charts.PieSeries());
  pieSeries.dataFields.value = "litres";
  pieSeries.dataFields.category = "country";

  // Let's cut a hole in our Pie chart the size of 30% the radius
  chart.innerRadius = am4core.percent(30);

  // Put a thick white border around each Slice
  pieSeries.slices.template.stroke = am4core.color("#fff");
  pieSeries.slices.template.strokeWidth = 2;
  pieSeries.slices.template.strokeOpacity = 1;
  pieSeries.slices.template
    // change the cursor on hover to make it apparent the object can be interacted with
    .cursorOverStyle = [
      {
        "property": "cursor",
        "value": "pointer"
      }
    ];
  pieSeries.alignLabels = false;
  pieSeries.labels.template.bent = true;
  pieSeries.labels.template.radius = 3;
  pieSeries.labels.template.padding(0,0,0,0);
  pieSeries.ticks.template.disabled = true;
  pieSeries.labels.template.disabled = true;
  // Create a base filter effect (as if it's not there) for the hover to return to
  var shadow = pieSeries.slices.template.filters.push(new am4core.DropShadowFilter);
  shadow.opacity = 0;
  // Create hover state
  var hoverState = pieSeries.slices.template.states.getKey("hover"); // normally we have to create the hover state, in this case it already exists
  // Slightly shift the shadow and make it more prominent on hover
  var hoverShadow = hoverState.filters.push(new am4core.DropShadowFilter);
  hoverShadow.opacity = 0.7;
  hoverShadow.blur = 5;
  // Add a legend
  chart.legend = new am4charts.Legend();
  chart.data = painting_defect_data[0];
  }); // end am4core.ready()
}
//조립 불량률
function assemble_defect_graph(){
  am4core.ready(function() {
  // Themes begin
  am4core.useTheme(am4themes_animated);
  // Themes end
  // Create chart instance
  var chart = am4core.create("pie3", am4charts.PieChart);
  // Add and configure Series
  var pieSeries = chart.series.push(new am4charts.PieSeries());
  pieSeries.dataFields.value = "litres";
  pieSeries.dataFields.category = "country";
  // Let's cut a hole in our Pie chart the size of 30% the radius
  chart.innerRadius = am4core.percent(30);
  // Put a thick white border around each Slice
  pieSeries.slices.template.stroke = am4core.color("#fff");
  pieSeries.slices.template.strokeWidth = 2;
  pieSeries.slices.template.strokeOpacity = 1;
  pieSeries.slices.template
    // change the cursor on hover to make it apparent the object can be interacted with
    .cursorOverStyle = [
      {
        "property": "cursor",
        "value": "pointer"
      }
    ];
  pieSeries.alignLabels = false;
  pieSeries.labels.template.bent = true;
  pieSeries.labels.template.radius = 3;
  pieSeries.labels.template.padding(0,0,0,0);
  pieSeries.ticks.template.disabled = true;
  pieSeries.labels.template.disabled = true;
  // Create a base filter effect (as if it's not there) for the hover to return to
  var shadow = pieSeries.slices.template.filters.push(new am4core.DropShadowFilter);
  shadow.opacity = 0;
  // Create hover state
  var hoverState = pieSeries.slices.template.states.getKey("hover"); // normally we have to create the hover state, in this case it already exists
  // Slightly shift the shadow and make it more prominent on hover
  var hoverShadow = hoverState.filters.push(new am4core.DropShadowFilter);
  hoverShadow.opacity = 0.7;
  hoverShadow.blur = 5;
  // Add a legend
  chart.legend = new am4charts.Legend();
  chart.data = assemble_defect_data[0];
  }); // end am4core.ready()
}
//유형별 불량률 그래프
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
      year: today_year,
      month: today_month,
      day: today_date
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

  if(defect_data1[0].defect_name == "없음"){
    $("#chartdiv1").css("height","100px");
  }
  if(defect_data2[0].defect_name == "없음"){
    $("#chartdiv2").css("height","100px");
  }
  if(defect_data3[0].defect_name == "없음"){
    $("#chartdiv3").css("height","100px");
  }
}


function injection_status(){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/injection/main/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        page: 1,
        perPage: 10000,
        sort: "order_date",
        order: "DESC"
      }
  }).done(function (result, textStatus, xhr) {
    var text = '';
    if(result.status == 200){
      var jsonResult = result.data;
      console.log(jsonResult);
      if(jsonResult.length > 0){
        for(var i in jsonResult){
          text +='<tr>';
          text +='  <th>'+jsonResult[i].RNUM+'</th>';
          text +='  <td>'+jsonResult[i].product_name+'</td>';
          text +='  <td>'+comma(jsonResult[i].process_qty)+'</td>';
          text +='  <td>'+comma(jsonResult[i].product_qty)+'</td>';
          text +='  <td>';
          text +='    <div class="progress progress-sm mb-0 box-shadow-2">';
          text +='        <div class="progress-bar bg-gradient-x-success" role="progressbar" style="width: '+jsonResult[i].process_percent+'%" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>';
          text +='    </div>'+jsonResult[i].process_percent+'%';
          text +='  </td>';
          text +='</tr>';
        }
      }else{
        text+='<tr class="text-center">';
        text+='  <td colspan="5">데이터가 없습니다</td>';
        text+='</tr>';
      }
      $("#injection_status_list").empty();
      $("#injection_status_list").append(text);

    }else{
      alert(result.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

function painting_status(){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/painting/main/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        page: 1,
        perPage: 10000,
        sort: "order_date",
        order: "DESC"
      }
  }).done(function (result, textStatus, xhr) {
    var text = '';
    if(result.status == 200){
      var jsonResult = result.data;
      console.log(jsonResult);
      if(jsonResult.length > 0){
        for(var i in jsonResult){
          text+='<tr>';
          text+='  <th>'+jsonResult[i].RNUM+'</th>';
          text+='  <td>'+jsonResult[i].product_name+'</td>';
          text+='  <td>'+comma(jsonResult[i].process_qty)+'</td>';
          text+='  <td>'+comma(jsonResult[i].product_qty)+'</td>';
          text+='  <td>';
          text+='    <div class="progress progress-sm mb-0 box-shadow-2">';
          text+='        <div class="progress-bar bg-gradient-x-success" role="progressbar" style="width: '+jsonResult[i].process_percent+'%" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>';
          text+='    </div>'+jsonResult[i].process_percent+'%';
          text+='  </td>';
          text+='</tr>';
        }
      }else{
        text+='<tr class="text-center">';
        text+='  <td colspan="5">데이터가 없습니다</td>';
        text+='</tr>';
      }
      $("#painting_status_list").empty();
      $("#painting_status_list").append(text);

    }else{
      alert(result.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

function assemble_status(){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/assemble/main/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        page: 1,
        perPage: 10000,
        sort: "order_date",
        order: "DESC"
      }
  }).done(function (result, textStatus, xhr) {
    var text = '';
    if(result.status == 200){
      var jsonResult = result.data;
      console.log(jsonResult);

      if(jsonResult.length > 0){
        for(var i in jsonResult){
          text+='<tr>';
          text+='  <th>'+jsonResult[i].RNUM+'</th>';
          text+='  <td>'+jsonResult[i].product_name+'</td>';
          text+='  <td>'+comma(jsonResult[i].process_qty)+'</td>';
          text+='  <td>'+comma(jsonResult[i].product_qty)+'</td>';
          text+='  <td>';
          text+='    <div class="progress progress-sm mb-0 box-shadow-2">';
          text+='        <div class="progress-bar bg-gradient-x-success" role="progressbar" style="width: '+jsonResult[i].process_percent+'%" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>';
          text+='    </div>'+jsonResult[i].process_percent+'%';
          text+='  </td>';
          text+='</tr>';
        }
      }else{
        text+='<tr class="text-center">';
        text+='  <td colspan="5">데이터가 없습니다</td>';
        text+='</tr>';
      }
      $("#assemble_status_list").empty();
      $("#assemble_status_list").append(text);

    }else{
      alert(result.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

//사출기현황
function machine_data(){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/master/asset/repair/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json"
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      var text = "";
      var jsonResult = result.data;
      console.log(jsonResult);
      for(var i in jsonResult){
        text +="<tr>";
        if(jsonResult[i].is_processing == "1"){
          text +="  <th class='text-center' style='background-color:#00ff00;width:60px;'>"+jsonResult[i].asset_no+"</th>";
        }else{
          text +="  <th class='text-center' style='background-color:#ff0000;color:#fff;width:60px;'>"+jsonResult[i].asset_no+"</th>";
        }
        text +="  <td>"+jsonResult[i].product_name+"</td>";
        text +="  <td class='text-right'>"+comma(jsonResult[i].complete_qty)+"</td>";
        text +="  <td>"+jsonResult[i].mold_code+"</td>";
        text +="  <td class='text-right'>"+jsonResult[i].cycle_time+"</td>";
        text +="  <td class='text-right'>"+jsonResult[i].cavity+"</td>";
        text +="  <td class='text-right'>"+comma(jsonResult[i].shot_cnt)+"</td>";
        text +="</tr>";
      }
      $("#injection_list").empty();
      $("#injection_list").append(text);
    }else{
      alert(result.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}
