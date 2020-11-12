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
  $("#machine_painting").addClass("active");
  if($("#quality_management").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
  if($("#machine_painting").find("a").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
  machine_painting();
});

function machine_painting(){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/vision/painting/count/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        year: year,
        month: month,
        day: date
      }
  }).done(function (result, textStatus, xhr) {
    var jsonResult = result.data;
    var text = "";
    console.log(jsonResult);
    for(var i in jsonResult){
      text +="<tr>";
      text +="  <td>"+jsonResult[i].RNUM+"</td>";
      text +="  <td>"+jsonResult[i].product_name+"</td>";
      text +="  <td>"+comma(jsonResult[i].input_qty)+"</td>";
      text +="  <td>"+comma(jsonResult[i].output_qty)+"</td>";
      text +="  <td>"+comma(jsonResult[i].loss_qty)+"</td>";
      text +="  <td>"+jsonResult[i].input_date+"</td>";
      text +="  <td>"+jsonResult[i].output_date+"</td>";
      text +="</tr>";
    }

    $("#painting_list").empty();
    $("#painting_list").append(text);
  }).fail(function(result, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

$("#search_btn").on("click", function(){
  location.href="./machine_painting.html?search_date="+$("#search_date").val();
});
