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

  injection_start_detail();
});

function injection_start_detail(){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/qr/start/index.php",
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
      var jsonResult = result.data[0];
      console.log(jsonResult);
      $("#process_date").text(jsonResult.process_date);
      $("#asset_name").text(jsonResult.display_name);
      $("#order_no").text(jsonResult.order_no);
      $("#barju_id").text(jsonResult.process_code);
      $("#product_name").text(jsonResult.product_name);
      $("#jaje_code").text(jsonResult.jaje_code);
      $("#material_name").text(jsonResult.material_name + " ("+ jsonResult.jaje_code +")");
      $("#qty").text(comma(jsonResult.qty) + " Kg");
      $("#start_end_date").text(jsonResult.start_date+" ~ "+jsonResult.end_date);
    }else{
      alert(result.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}
