painting_data();

function painting_data(){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/painting/main/index.php",
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
      $("#total_order_no").text(jsonResult.order_no);
      $("#total_type").text(jsonResult.type);
      $("#total_product_name").text(jsonResult.product_name);
      $("#total_process_code").text(jsonResult.process_code);
      $("#total_request_date").text(jsonResult.request_date);
      $("#total_order_date").text(jsonResult.order_date);
      $("#total_process_qty").text(comma(jsonResult.process_qty));
      $("#total_product_qty").text(comma(jsonResult.product_qty));
      $("#total_process_percent").text(jsonResult.process_percent+"%");
      $("#work_qty").val(jsonResult.work_qty);
      $("#humidity_max").val(jsonResult.humidity_max);
      $("#humidity_min").val(jsonResult.humidity_min);
      $("#humidity_average").val(jsonResult.humidity_average);
      $("#conveyor_speed").val(jsonResult.conveyor_speed);
    }else{
      alert(result.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

function confirm(){
  $.ajax({
      type    : "POST",
      url        : "../api/cosmetics/painting/process/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data: JSON.stringify({
        process_order_id: getParam("id"),
        work_qty: $("#work_qty").val(),
        humidity_max: $("#humidity_max").val(),
        humidity_min: $("#humidity_min").val(),
        humidity_average: $("#humidity_average").val(),
        conveyor_speed: $("#conveyor_speed").val()
      })
  }).done(function (result, textStatus, xhr) {
    var text = '';
    if(result.status == 200){
      alert("저장 되었습니다");
      location.href="../product_history/painting_status.html";
    }else{
      alert(result.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}
