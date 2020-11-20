var date = new Date();
var year = date.getFullYear();
var month = new String(date.getMonth()+1);
var day = new String(date.getDate());

// 한자리수일 경우 0을 채워준다.
if(month.length == 1){
  month = "0" + month;
}
if(day.length == 1){
  day = "0" + day;
}

$("#stock_date").val(year + "-" + month + "-" + day);

$(document).ready(function(){
  $("#product_history").addClass("open");
  $("#material").addClass("active");

  if($("#product_history").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
  if($("#material").find("a").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
});

$("#material_code").on("click", function(){
    if($("#search_type").val() == null){
      alert("분류를 먼저 선택해주세요");
      return;
    }
    $("#search_modal").fadeIn(300);
    $("#modal_back").fadeIn(300);
    $("#search_text").val("");
    $("#search_table").empty();
    $(".search_result_box").css("display","none");
});

$("#modal_back").on("click", function(){
  modal_off();
});

function modal_off(){
  $("#search_modal").fadeOut(300);
  $("#modal_back").fadeOut(300);
}

$("#search_text").keydown(function(key) {
  if (key.keyCode == 13) {
    var search_text = $("#search_text").val();
    if(search_text == ""){
      alert("자재코드를 입력해주세요");
      return;
    }else{
      auto_search(search_text);
    }
  }
});

$("#search_btn").on("click", function(){
  var search_text = $("#search_text").val();
  if(search_text == ""){
    alert("자재코드를 입력해주세요");
    return;
  }else{
    auto_search(search_text);
  }
});

function auto_search(search){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/master/material/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data     : {
        type: $("#search_type").val(),
        search: search
      }
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      console.log(result);
      var jsonResult = result.data;
      $(".search_result_box").css("display","block");
      var text = "<tr>";
          text += "<th>자재코드</th>";
          text += "<th>원자재명</th>";
          text += "</tr>";

      for(var i in jsonResult){
        text +='<tr data-code='+jsonResult[i].code+' data-name='+jsonResult[i].name+' data-material-id='+jsonResult[i].id+' data-material-unit='+jsonResult[i].unit+'>';
        text +="<td>"+jsonResult[i].code+"</td>";
        text +="<td>"+jsonResult[i].name+"</td>";
        text +="</tr>";
      }

      $("#search_table").empty();
      $("#search_table").append(text);
      $("#search_table tr").on("click", function(){
        var material_code = $(this).data("code");
        var material_name = $(this).data("name");
        var material_id = $(this).data("material-id");
        var material_unit = $(this).data("material-unit");
        $("#material_code").val(material_code);
        $("#material_name").val(material_name);
        $("#material_id").val(material_id);
        $("#material_unit").val(material_unit);
        modal_off();
      })
    }else{
      alert(result.message);
    }
  }).fail(function(data, textStatus, errorThrown){
      console.log("전송 실패");
  });
}


$("#stock_insert").on("click", function(){
  if($("#material_code").val() == ""){alert("자재코드를 선택해주세요");return;};
  if($("#stock_date").val() == ""){alert("날짜를 선택해주세요");return;};
  if($("#total_qty").val() == ""){alert("총량을 선택해주세요");return;};

  $.ajax({
      type    : "POST",
      url        : "../api/cosmetics/stock/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data     : JSON.stringify({
        material_id: $("#material_id").val(),
        stock_date: $("#stock_date").val(),
        qty: $("#total_qty").val(),
        type: "CO"
      })
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      alert("등록 되었습니다");
      location.href="../product_history/painting_material_status.html";
    }else{
      alert(result.message);
    }
  }).fail(function(data, textStatus, errorThrown){
      console.log("전송 실패");
  });
});

$("#material_qty").keydown(function(key) {
  if (key.keyCode == 13) {
    $("#stock_insert").click();
  }
});
