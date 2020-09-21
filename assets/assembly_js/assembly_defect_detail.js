painting_defect_detail();
painting_defect_list();

function painting_defect_detail(){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/painting/defect/index.php",
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
      var jsonResult = result.data;
      console.log(jsonResult);

      $("#total_start_date").text(jsonResult[0].start_date.substr(0,10));
      $("#total_order_no").text(jsonResult[0].order_no);
      $("#total_type").text(jsonResult[0].type);
      $("#total_product_name").text(jsonResult[0].product_name);
      $("#defect_qty").text(comma(jsonResult[0].defect_qty));
      $("#defect_percent").text('('+jsonResult[0].defect_percent+'%)');
      $("#total_qty").text(comma(jsonResult[0].product_qty));
      $("#total_manager").text(comma(jsonResult[0].manager));
      $("#total_time").text(jsonResult[0].start_date.substr(11,8)+'~'+jsonResult[0].end_date.substr(11,8));
    }else{
      alert(result.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

function painting_defect_list(){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/painting/defect/index.php",
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
    if(result.status == 200){
      var jsonResult = result.data;
      var text  ='<tr>';
          text +='  <th>전체</th>';
      var text2 ='<tr>';
          text2+='  <td id="total_defect_qty"></td>';
      console.log(jsonResult);
      var total_defect_qty = 0;
      for(var i in jsonResult){
        total_defect_qty = total_defect_qty + Number(jsonResult[i].qty);
        text +='  <th>'+jsonResult[i].defect_name+'</th>';
        text2+='  <td>'+comma(jsonResult[i].qty)+'</td>';
      }
      text +='</tr>';
      text2+='</tr>';

      $("#defect_th").empty();
      $("#defect_th").append(text);
      $("#defect_td").empty();
      $("#defect_td").append(text2);
      $("#total_defect_qty").text(comma(total_defect_qty));
    }else{
      alert(result.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}
