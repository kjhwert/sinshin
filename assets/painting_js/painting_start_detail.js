painting_start_detail();

function painting_start_detail(){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/painting/start/index.php",
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
      var total_qty = 0;
      for(var i in jsonResult){
        total_qty = total_qty + Number(jsonResult[i].qty);
        text +='<tr>';
        text +='  <td>'+jsonResult[i].RNUM+'</td>';
        text +='  <td>'+jsonResult[i].qty+'</td>';
        text +='  <td>'+jsonResult[i].process_date+'</td>';
        text +='  <td>'+jsonResult[i].manager+'</td>';
        text +='</tr>';
      }
      $("#painting_start_detail_list").empty();
      $("#painting_start_detail_list").append(text);

      $("#total_process_date").text(jsonResult[0].process_date);
      $("#total_order_no").text(jsonResult[0].order_no);
      $("#total_type").text(jsonResult[0].type);
      $("#total_product_name").text(jsonResult[0].product_name);
      $("#total_box").text(comma(jsonResult.length));
      $("#total_qty").text(comma(total_qty));
    }else{
      alert(result.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}
