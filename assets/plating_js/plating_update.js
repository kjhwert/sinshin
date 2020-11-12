$(function(){
  $("#automotive_management").addClass("open");
  $("#plating").addClass("active");

  if($("#automotive_management").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
  if($("#plating").find("a").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
});
var defect_cnt = "";

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
    defect_cnt = jsonResult.length;
    var text1 = '<tr>';
    var text2 = '<tr>';
    var text3 = '<tr>';
    var text4 = '<tr>';

    for(var i in jsonResult){
      if(jsonResult[i].group_id == 1){
        text1 += '<td>'+jsonResult[i].name+'<br>'+jsonResult[i].name_en+'</td>';
        text3 += '<td><input id="defect_'+jsonResult[i].id+'" type="number" class="form-control"></td>';
      }else{
        text2 += '<td>'+jsonResult[i].name+'<br>'+jsonResult[i].name_en+'</td>';
        text4 += '<td><input id="defect_'+jsonResult[i].id+'" type="number" class="form-control"></td>';
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
    $("#product_id").val(jsonResult.product_id);
    $("#product_name").text(jsonResult.product_name);
    $("#customer_code").text(jsonResult.customer_code);
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
$("#plating_update").on("click", function(){
  var result = confirm('등록하신 후에는 수정할 수 없습니다. \n계속 하시겠습니까?');
  if(result) {
    var defect = [];

    for(var i=1;i<=defect_cnt;i++){
      if($("#defect_"+i).val() != ""){
        defect.push({"id" : i, "qty" : $("#defect_"+i).val()});
      }
    }


    $.ajax({
        type    : "PUT",
        url        : "../api/automobile/process/index.php",
        headers : {
          "content-type": "application/json",
          Authorization : user_data.token,
        },
        dataType:"json",
        data     : JSON.stringify({
          id: getParam("id"),
          day_night: $("#day_night").val(),
          product_id: $("#product_id").val(),
          lot_no: $("#lot_no").val(),
          input_date: $("#input_date").val(),
          comp_date: $("#comp_date").val(),
          carrier: $("#carrier").val(),
          charger: $("#charger").val(),
          package_manager: $("#package_manager").val(),
          output_count: $("#output_count").val(),
          remain_count: $("#remain_count").val(),
          as_part: $("#as_part").val(),
          input: $("#input").val(),
          output: $("#output").val(),
          trust_loss: $("#trust_loss").val(),
          size_loss: $("#size_loss").val(),
          defect: JSON.stringify(defect)
        })
    }).done(function (result, textStatus, xhr) {
      if(result.status == 200){
        alert("등록 되었습니다");
        location.href="../automotive_management/plating_status.html";
      }else{
        alert(result.message);
      }
    }).fail(function(data, textStatus, errorThrown){
        console.log("전송 실패");
    });
  } else {
    return;
  }
})
