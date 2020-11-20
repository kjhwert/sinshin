material_detail_list(1, 1);

function material_detail_list(page_no, per_page){
  $.ajax({
      type    : "GET",
      url        : "../api/automobile/stock/log/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data     : {
        id: getParam("id"),
        page: page_no,
        perPage: per_page,
      }
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      console.log(result);
      var jsonResult = result.data;
      $("#car_code").val(jsonResult[0].car_code);
      $("#customer").val(jsonResult[0].customer);
      $("#supplier").val(jsonResult[0].supplier);
      $("#remain_qty").val(jsonResult[0].remain_qty);
      $("#product_name").val(jsonResult[0].product_name);
      $("#customer_code").val(jsonResult[0].customer_code);
    }else{
      alert(result.message);
    }
  }).fail(function(data, textStatus, errorThrown){
      console.log("전송 실패");
  });
}

$("#warehousing_export").on("click", function(){
  $.ajax({
    type    : "PUT",
    url        : "../api/automobile/stock/log/index.php",
    headers : {
      "content-type": "application/json",
      Authorization : user_data.token,
    },
    dataType:"json",
    data     : JSON.stringify({
      id: getParam("id"),
      out_qty: $("#out_qty").val()
    })
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      alert("등록 되었습니다");
      location.href="./plating_material.html";
    }else{
      alert(result.message);
    }
  }).fail(function(data, textStatus, errorThrown){
      console.log("전송 실패");
  });
});
