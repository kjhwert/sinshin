$(function(){
  $("#product_history").addClass("open");
  $("#injection").addClass("active");
  if($("#product_history").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
  if($("#injection").find("a").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }

  injection_defect_detail();
  injection_defect_detail2();
});


function injection_defect_detail(){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/qr/defect/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        id: getParam("id")
      }
  }).done(function (result, textStatus, xhr) {
    var text = '';
    if(result.status == 200){
      var jsonResult = result.data;
      console.log(jsonResult);
      $("#created_at").text(jsonResult[0].start_date.substr(0,10));
      $("#asset_name").text(jsonResult[0].display_name);
      $("#order_no").text(jsonResult[0].order_no);
      $("#barju_id").text(jsonResult[0].process_code);
      $("#product_name").text(jsonResult[0].product_name);
      $("#product_qty").text(jsonResult[0].product_qty);
      $("#defect_qty").text(jsonResult[0].defect_qty);
      $("#defect_percent").text(jsonResult[0].defect_percent+"%");
      $("#manager").text(jsonResult[0].manager);

    }else{
      alert(result.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}
function injection_defect_detail2(){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/qr/defect/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        id: getParam("id"),
        type: "defect"
      }
  }).done(function (result, textStatus, xhr) {
    var text = '';
        text +='<tr>';
        text +='  <th>전체</th>';
    var text2 = '';
        text2 +='<tr>';
        text2 +=' <td id="defect_total_qty"></td>';
    var defect_total_qty = 0;
    if(result.status == 200){
      var jsonResult = result.data;
      console.log(jsonResult);
      for(var i in jsonResult){
        text +='<th>'+jsonResult[i].defect_name+'</th>';
        text2 += '<td>'+comma(jsonResult[i].qty)+'</td>';
        defect_total_qty = Number(defect_total_qty+jsonResult[i].qty);
      }

      $("#defect_list_head").empty();
      $("#defect_list_head").append(text);
      $("#defect_list_body").empty();
      $("#defect_list_body").append(text2);
      $("#defect_total_qty").text(comma(defect_total_qty));
    }else{
      alert(result.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}
