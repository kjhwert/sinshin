defect_list();

function defect_list(){
  $.ajax({
      type    : "GET",
      url        : "http://sinshin.hlabpartner.com/api/defect/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data     : {
        type: "car"
      }
  }).done(function (result, textStatus, xhr) {
    var jsonResult = result.data;
    var text1 = '<tr>';
    var text2 = '<tr>';
    var text3 = '<tr>';
    var text4 = '<tr>';

    for(var i in jsonResult){
      if(jsonResult[i].group_id == 1){
        text1 += '<td>'+jsonResult[i].name+'<br>'+jsonResult[i].name_en+'</td>';
        text3 += '<td><input id="defect_'+jsonResult[i].id+'" type="text" class="form-control"></td>';
      }else{
        text2 += '<td>'+jsonResult[i].name+'<br>'+jsonResult[i].name_en+'</td>';
        text4 += '<td><input id="defect_'+jsonResult[i].id+'" type="text" class="form-control"></td>';
      }
    }
    text1 +='</tr>';
    text2 +='</tr>';
    text3 +='</tr>';
    text4 +='</tr>';

    $("#defect_name_list1").empty();
    $("#defect_name_list2").empty();
    $("#defect_id_list1").empty();
    $("#defect_id_list2").empty();
    $("#defect_name_list1").append(text1);
    $("#defect_name_list2").append(text2);
    $("#defect_id_list1").append(text3);
    $("#defect_id_list2").append(text4);

    plating_read();
  }).fail(function(data, textStatus, errorThrown){
      console.log("전송 실패");
  });
}

function plating_read(){
  $.ajax({
      type    : "GET",
      url        : "http://sinshin.hlabpartner.com/api/automobile/process/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data     : {
        id: getParam("id")
      }
  }).done(function (result, textStatus, xhr) {
    var jsonResult = result.data;
    console.log(jsonResult);
    $("#product_id").val(jsonResult.product_id);
    $("#product_name").val(jsonResult.product_name);
    $("#charger").val(jsonResult.charger);
    $("#lot_no").val(jsonResult.lot_no);
    $("#customer").val(jsonResult.customer);
    $("#supplier").val(jsonResult.supplier);
    $("#input_date").val(jsonResult.input_date);
    $("#comp_date").val(jsonResult.comp_date);
    $("#carrier").val(jsonResult.carrier);
    $("#mfr_date").val(jsonResult.mfr_date);
    $("#rack").val(jsonResult.rack);
    $("#input").val(jsonResult.input);
    $("#output").val(jsonResult.output);
    $("#day_night").val(jsonResult.day_night);
    $("#total_defect").val(jsonResult.total_defect);

    for(var i in jsonResult.defects){
      $("#defect_"+jsonResult.defects[i].id).val(jsonResult.defects[i].qty);
    }
  }).fail(function(data, textStatus, errorThrown){
      console.log("전송 실패");
  });
}
