var daily_total_data = [];
setDateBox();
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
  if(getParam("month") != ""){
    $("#monthly_select").val(getParam("month"));
  }else{
    $("#monthly_select").val(today_month);
  }
  daily_total();
}

$(function(){
  $("#quality_management").addClass("open");
  $("#statistics_analysis").addClass("active");
  if($("#quality_management").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
  if($("#statistics_analysis").find("a").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
});

function daily_total(){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/vision/statistics/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        year: $("#years_select").val(),
        month: $("#monthly_select").val(),
        date: "month",
        type: "average"
      }
  }).done(function (result, textStatus, xhr) {
    var jsonResult = result.data;
    console.log(jsonResult);
    daily_total_data = jsonResult.defects;
    if(jsonResult.input_qty == null){jsonResult.input_qty = 0;}
    if(jsonResult.production_qty == null){jsonResult.production_qty = 0;}
    if(jsonResult.defect_qty == null){jsonResult.defect_qty = 0;}
    if(jsonResult.assemble_qty == null){jsonResult.assemble_qty = 0;}
    if(jsonResult.assemble_defect_qty == null){jsonResult.assemble_defect_qty = 0;}
    $("#input_qty").text(comma(jsonResult.input_qty));
    $("#production_qty").text(comma(jsonResult.production_qty));
    $("#defect_qty").text(comma(jsonResult.defect_qty));
    $("#assemble_qty").text(comma(jsonResult.assemble_qty));
    $("#assemble_defect_qty").text(comma(jsonResult.assemble_defect_qty));
    $("#day_defect_percent").text(comma(jsonResult.defect_percent)+"%");
    $("#day_production_percent").text(comma(jsonResult.production_percent)+"%");
    $("#day_assemble_percent").text(comma(jsonResult.assemble_percent)+"%");
    $("#day_assemble_defect_qty").text(comma(jsonResult.assemble_defect_qty)+"%");
    daily_statistics();
  }).fail(function(result, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

function daily_statistics(){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/vision/statistics/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        year: $("#years_select").val(),
        month: $("#monthly_select").val(),
        date: "month",
        type: "statistic"
      }
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      var jsonResult = result.data;
      console.log(jsonResult);
      var thead = "<th class='text-center'>#</th>";
      var tbody = "";
      var ttotal = "<th class='text-center color-red'>평균</th>";
      for(var i in jsonResult){
        if(i == 0){
          for(var j in jsonResult[0].defects){
            thead +='<th class="text-center">'+jsonResult[0].defects[j].name+'</th>';
          }
        }

        tbody += "<tr>";
        tbody += "  <td class='text-center'>"+jsonResult[i].day+"일</td>";
        for(var k in jsonResult[0].defects){
          if(jsonResult[i].defects[k].qty == "0"){
            jsonResult[i].defects[k].qty = "";
          }
          tbody +="<th class='text-center'>"+comma(jsonResult[i].defects[k].qty)+"</th>";
        }
        tbody += "</tr>";

      }
      tbody += "</tr>";

      for(var g in daily_total_data){
        if(daily_total_data[g].qty == 0){
          daily_total_data[g].qty = "-";
        }
        ttotal += "<th class='text-center color-red'>"+comma(daily_total_data[g].qty)+"</th>";
      }
      $("#defect_thead").empty();
      $("#defect_thead").append(thead);
      $("#defect_total").empty();
      $("#defect_total").append(ttotal);
      $("#defect_tbody").empty();
      $("#defect_tbody").append(tbody);
    }else{
      alert(result.message);
      return;
    }
  }).fail(function(result, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

$("#search_btn").on("click", function(){
  var search_date = $("#search_date").val();
  location.href="./statistics_analysis.html?years="+$("#years_select").val()+"&month="+$("#monthly_select").val();
});
