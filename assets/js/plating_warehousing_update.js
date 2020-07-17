warehousing_detail();

function warehousing_detail(){
  $.ajax({
      type    : "GET",
      url        : "../api/automobile/stock/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        id: getParam("id")
      }
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      console.log(result.data);
      $("#product_name").val(result.data[0].product_name);
      $("#product_id").val(result.data[0].product_id);
      $("#customer").val(result.data[0].customer);
      $("#supplier").val(result.data[0].supplier);
      $("#customer_code").val(result.data[0].customer_code);
      $("#car_code").val(result.data[0].car_code);
      $("#mfr_date").val(result.data[0].mfr_date);
      $("#store_qty").val(comma(result.data[0].store_qty));
      $("#type").val(result.data[0].type);
      $("#bing_defect").val(comma(result.data[0].bing_defect));
      $("#visual_defect").val(result.data[0].visual_defect);
    }else{
      alert(result.message);
    }
  }).fail(function(data, textStatus, errorThrown){
      console.log("전송 실패");
  });
}

$("#warehousing_update").on("click", function(){
  var product_id = $("#product_id").val();
  var mfr_date = $("#mfr_date").val();
  var store_qty = $("#store_qty").val();
  var visual_defect = $("#visual_defect").val();
  var bing_defect = $("#bing_defect").val();
  var type = $("#type").val();

  $.ajax({
      type    : "PUT",
      url        : "../api/automobile/stock/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:JSON.stringify({
        id : getParam("id"),
        product_id : product_id,
        mfr_date : mfr_date,
        store_qty : uncomma(store_qty),
        visual_defect : visual_defect,
        bing_defect : bing_defect,
        type : type
      })
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      alert("수정 되었습니다");
      location.href="../automotive_management/plating_warehousing.html";
    }else{
      alert(result.message);
    }
  }).fail(function(data, textStatus, errorThrown){
      console.log("전송 실패");
  });
});
