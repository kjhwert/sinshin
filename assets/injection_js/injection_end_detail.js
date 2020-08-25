injection_end_detail();

function injection_end_detail(){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/qr/complete/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        id: getParam("id")
      }
  }).done(function (result, textStatus, xhr) {
    var jsonResult = result.data;
    console.log(jsonResult);
    var text = '';
    var qty = 0;
    var asset_name = "";
    for(var i in jsonResult){
      qty = Number(qty)+Number(jsonResult[i].qty);

      if(asset_name != jsonResult[i].asset_name){
        if(asset_name == ""){
          asset_name += jsonResult[i].asset_name;
        }else{
          asset_name += ", "+jsonResult[i].asset_name;
        }
      }

      text+='<tr>';
      text+='  <td>'+jsonResult[i].RNUM+'</td>';
      text+='  <td>'+jsonResult[i].qty+'</td>';
      text+='  <td>'+jsonResult[i].process_date+'</td>';
      text+='  <td>'+jsonResult[i].RNUM+'</td>';
      text+='</tr>';
    }
    console.log(asset_name);
    $("#injection_end_list").empty();
    $("#injection_end_list").append(text);
    $("#process_date").text(jsonResult[0].process_date);
    $("#order_no").text(jsonResult[0].order_no);
    $("#barju_id").text(jsonResult[0].id);
    $("#product_name").text(jsonResult[0].product_name);
    $("#total_row").text(jsonResult.length + "박스");
    $("#total_qty").text(qty + "ea");
    $("#asset_name").text(asset_name);

  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}