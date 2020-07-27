defect_list();


function defect_list(){
  $.ajax({
      type    : "GET",
      url        : "../api/defect/index.php",
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
        text3 += '<td id="defect_'+jsonResult[i].id+'"></td>';
      }else{
        text2 += '<td>'+jsonResult[i].name+'<br>'+jsonResult[i].name_en+'</td>';
        text4 += '<td id="defect_'+jsonResult[i].id+'"></td>';
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
      url        : "../api/automobile/process/index.php",
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
    if(jsonResult.type == "immutable"){
      $("#plating_update").css("display","none");
      $("#plating_delete").css("display","none");
    }

    $("#day_night").text(jsonResult.day_night);
    $("#package_manager").text(jsonResult.package_manager);
    $("#package_date").text(jsonResult.package_date);
    $("#output_count").text(jsonResult.output_count);
    $("#remain_count").text(jsonResult.remain_count);
    $("#as_part").text(jsonResult.as_part);
    $("#product_id").val(jsonResult.product_id);
    $("#product_name").text(jsonResult.product_name);
    $("#lot_no").text(jsonResult.lot_no);
    $("#customer").text(jsonResult.customer);
    $("#supplier").text(jsonResult.supplier);
    $("#input_date").text(jsonResult.input_date);
    $("#comp_date").text(jsonResult.comp_date);
    $("#carrier").text(jsonResult.carrier);
    $("#mfr_date").text(jsonResult.mfr_date);
    $("#rack").text(jsonResult.rack);
    $("#input").text(jsonResult.input);
    $("#output").text(jsonResult.output);
    $("#total_defect").text(jsonResult.total_defect);
    $("#charger").text(jsonResult.charger);
    $("#trust_loss").text(jsonResult.trust_loss);
    $("#size_loss").text(jsonResult.size_loss);

    if(jsonResult.type == "mutable"){
      $("#plating_delete").css("display", "inline-block");
    }else{
      $("#plating_delete").css("display", "none");
    }

    for(var i in jsonResult.defects){
      $("#defect_"+jsonResult.defects[i].id).text(jsonResult.defects[i].qty);
    }
  }).fail(function(data, textStatus, errorThrown){
      console.log("전송 실패");
  });
}

$("#plating_update").on("click", function(){
  location.href='../automotive_management/plating_update.html?id='+getParam("id");
})

$("#plating_delete").on("click", function(){
  var msg = confirm("정말 삭제하시겠습니까?");
  if(msg){
    $.ajax({
        type    : "DELETE",
        url        : "../api/automobile/process/index.php",
        headers : {
          "content-type": "application/json",
          Authorization : user_data.token,
        },
        dataType:"json",
        data     : JSON.stringify({
          id: getParam("id")
        })
    }).done(function (result, textStatus, xhr) {
      if(result.status == 200){
        alert(result.message);
        location.href="../automotive_management/plating_status.html";
      }else{
        alert(result.message);
      }
    }).fail(function(data, textStatus, errorThrown){
        console.log("전송 실패");
    });
  }
})
