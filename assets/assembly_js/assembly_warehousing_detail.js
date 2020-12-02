
$(function(){
  $("#product_history").addClass("open");
  $("#assembly").addClass("active");
  if($("#product_history").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
  if($("#assembly").find("a").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }

  painting_warehousing_detail();
});


function painting_warehousing_detail(){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/assemble/put/index.php",
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
      var total_box = 0;
      for(var i in jsonResult){
        total_box = total_box + Number(jsonResult[i].qty);
        text +='<tr>';
        text +='  <td class="text-center">'+jsonResult[i].RNUM+'</td>';
        text +='  <td class="text-center">'+jsonResult[i].from_name+'</td>';
        text +='  <td>'+jsonResult[i].product_name+'</td>';
        text +='  <td class="text-center">'+jsonResult[i].type+'</td>';
        text +='  <td class="text-right">'+comma(jsonResult[i].qty)+'</td>';
        text +='  <td class="text-center">'+jsonResult[i].process_date.substr(0,10)+'</td>';
        text +='  <td class="text-center">'+jsonResult[i].put_date.substr(0,10)+'</td>';
        text +='  <td class="text-center">'+jsonResult[i].manager+'</td>';
        text +='</tr>';
      }
      $("#painting_detail_list").empty();
      $("#painting_detail_list").append(text);

      $("#total_put_date").text(jsonResult[0].put_date);
      $("#total_asset_no").text(jsonResult[0].asset_no);
      $("#total_order_no").text(jsonResult[0].order_no);
      $("#process_code").text(jsonResult[0].process_code);
      $("#total_product_name").text(jsonResult[0].product_name);
      $("#total_box").text(comma(jsonResult.length)+" box");
      $("#total_qty").text(comma(total_box)+" ea");
      $("#total_from_name").text(jsonResult[0].from_name);
      $("total_type").text(jsonResult[0].type);
    }else{
      alert(result.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}
