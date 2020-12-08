var daily_total_data = [];

if(getParam("search_date") == ""){
  var now = new Date();
  var year = now.getFullYear();
  var month = now.getMonth() + 1;    //1월이 0으로 되기때문에 +1을 함.
  var date = now.getDate();

  if((month + "").length < 2){        //2자리가 아니면 0을 붙여줌.
      month = "0" + month;
  }
  if((date + "").length < 2){        //2자리가 아니면 0을 붙여줌.
      date = "0" + date;
  }

  $("#search_date").val(year+"-"+month+"-"+date);
}else{
  var year = getParam("search_date").substr(0,4);
  var month = getParam("search_date").substr(5,2);
  var date = getParam("search_date").substr(8,2);

  $("#search_date").val(getParam("search_date"));
}

$(function(){
  $("#quality_management").addClass("open");
  $("#daily_statistics").addClass("active");
  if($("#quality_management").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
  if($("#daily_statistics").find("a").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }

  daily_total();
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
        year: year,
        month: month,
        day: date,
        date: "day",
        type: "average"
      }
  }).done(function (result, textStatus, xhr) {
    var jsonResult = result.data;
    console.log(jsonResult);
    daily_total_data = jsonResult.defects;
    $("#input_qty").text(comma(jsonResult.input_qty));
    $("#production_qty").text(comma(jsonResult.production_qty));
    $("#defect_qty").text(comma(jsonResult.defect_qty));
    $("#assemble_qty").text(comma(jsonResult.assemble_qty));
    $("#assemble_defect_qty").text(comma(jsonResult.assemble_defect_qty));

    $("#day_defect_percent").text(comma(jsonResult.day_defect_percent)+" %");
    $("#day_production_percent").text(comma(jsonResult.day_production_percent)+" %");
    $("#day_assemble_percent").text(comma(jsonResult.day_assemble_percent)+" %");
    $("#day_assemble_defect_qty").text(comma(jsonResult.day_assemble_defect_percent)+" %");

    $("#month_defect_percent").text(comma(jsonResult.month_defect_percent)+" %");
    $("#month_production_percent").text(comma(jsonResult.month_production_percent)+" %");
    $("#month_assemble_percent").text(comma(jsonResult.month_assemble_percent)+" %");
    $("#month_assemble_defect_qty").text(comma(jsonResult.month_assemble_defect_percent)+" %");

    $("#defect_percent_point").text(comma(jsonResult.defect_percent_point)+" %");
    $("#production_percent_point").text(comma(jsonResult.production_percent_point)+" %");
    $("#assemble_percent_point").text(comma(jsonResult.assemble_percent_point)+" %");
    $("#assemble_defect_percent_point").text(comma(jsonResult.assemble_defect_percent_point)+" %");

    if($("#defect_percent_point").text().substr(0,1) != "-"){
      $("#defect_percent_point").css("color","#ff0000");
    }
    if($("#production_percent_point").text().substr(0,1) == "-"){
      $("#production_percent_point").css("color","#ff0000");
    }
    if($("#assemble_percent_point").text().substr(0,1) != "-"){
      $("#assemble_percent_point").css("color","#ff0000");
    }
    if($("#assemble_defect_percent_point").text().substr(0,1) != "-"){
      $("#assemble_defect_percent_point").css("color","#ff0000");
    }

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
        year: year,
        month: month,
        day: date,
        date: "day",
        type: "statistic"
      }
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      var jsonResult = result.data;
      console.log(jsonResult.length);
      var thead = "<th class='text-center'>#</th>";
      var tbody = "";
      var ttotal = "<th class='text-center color-red'>합계</th>";
      if(jsonResult.length == 0){
        thead +='<th class="text-center" colspan="19">데이터 없음</th>';
      }
      for(var i in jsonResult){
        if(i == 0){
          for(var j in jsonResult[0].defects){
            thead +='<th class="text-center">'+jsonResult[0].defects[j].name+'</th>';
          }
        }

        tbody += "<tr>";
        tbody += "  <td>"+jsonResult[i].created_at.substr(10,9)+"</td>";
        for(var k in jsonResult[0].defects){
          if(jsonResult[i].defects[k].qty == "0"){
            jsonResult[i].defects[k].qty = "";
          }
          tbody +="<th class='text-center'>"+jsonResult[i].defects[k].qty+"</th>";
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
  location.href="./daily_statistics.html?search_date="+search_date;
});
