var active_id = "";
var active_asset = "";
var machineData = [];
setInterval(function(){
  machine_data();
}, 30000);

$(function(){
  $("#production_management").addClass("open");
  $("#injection_machine_status").addClass("active");
  if($("#production_management").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
  if($("#injection_machine_status").find("a").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }

  machine_data();
});

function machine_management(){
  if(active_id == ""){
    alert("조회하실 사출기를 선택하세요");
    return;
  }else{
    location.href="./injection_machine_management.html?id="+active_id+"&asset="+active_asset;
  }
}

function machine_data(){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/master/asset/repair/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json"
  }).done(function (result, textStatus, xhr) {
    var text = '<tr>';
    if(result.status == 200){
      var jsonResult = result.data;
      console.log(jsonResult);
      machineData.push(jsonResult);

      for(var i in jsonResult){
        text+='<td id="machine'+i+'" data-id='+jsonResult[i].id+' data-asset_no='+jsonResult[i].asset_no+' data-is_processing='+jsonResult[i].is_processing+' data-product_name='+jsonResult[i].product_name.replace(/ /g,"")+' data-process_percent='+jsonResult[i].process_percent+' data-process_qty='+jsonResult[i].process_qty+' data-complete_qty='+jsonResult[i].complete_qty+' data-repair_date='+jsonResult[i].repair_date+' data-hydraulic_date='+jsonResult[i].hydraulic_date+' data-lubricant_date='+jsonResult[i].lubricant_date+' data-filter_date='+jsonResult[i].filter_date+'>';

        if(jsonResult[i].is_processing == 0){
          text+='  <div class="machine_num">'+jsonResult[i].asset_no.substr(3,2)+'</div>';
        }else{
          text+='  <div class="machine_num machine_active">'+jsonResult[i].asset_no.substr(3,2)+'</div>';
        }
        text+='  <p class="text-center"><img src="../assets/images/injection_machine.png"></p>';
        text+='  <div class="progress progress-sm mb-0 box-shadow-2">';
        text+='      <div class="progress-bar bg-gradient-x-success" role="progressbar" style="width: '+jsonResult[i].process_percent+'%" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>';
        text+='  </div>';
        text+='  <p class="text-center">'+jsonResult[i].process_percent+'%</p>';
        text+='  <p class="text-center machine_product_name">'+jsonResult[i].product_name+'</p>';
        text+='</td>';

        var tr_cnt = Number(i+1);
        if(tr_cnt%3 == 0){
          if(i != 0){
            text += '</tr><tr>';
          }
        }
      }
      $("#injection_list").empty();
      $("#injection_list").append(text);

      $("#injection_list td").on("click", function(){
        var clickNum = $(this).attr("id").substr(7,1);

        $("#injection_list td").css("background-color", "#fff");
        $(this).css("background-color", "#eee");
        active_id = $(this).data("id");
        active_asset = $(this).data("asset_no");

        var is_processing = $(this).data("is_processing");
        var asset_no = $(this).data("asset_no").substr(3,2);
        var repair_date = $(this).data("repair_date");
        var hydraulic_date = $(this).data("hydraulic_date");
        var lubricant_date = $(this).data("lubricant_date");
        var filter_date = $(this).data("filter_date");
        var process_qty = $(this).data("process_qty");
        var complete_qty = $(this).data("complete_qty");
        var process_percent = $(this).data("process_percent");
        var product_name = $(this).data("product_name");
        var process_percent = $(this).data("process_percent");

        var progress = '<div class="progress-bar bg-gradient-x-success" role="progressbar" style="width: '+process_percent+'%" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>';

        $("#asset_no").text(asset_no);
        $("#repair_date").text(repair_date);
        $("#hydraulic_date").text(hydraulic_date);
        $("#lubricant_date").text(lubricant_date);
        $("#filter_date").text(filter_date);
        $("#process_qty").text(comma(process_qty));
        $("#complete_qty").text(comma(complete_qty));
        $("#process_percent").text(process_percent+"%");
        $("#product_name").text(product_name);

        $("#mold_code").text(machineData[0][clickNum].mold_code);
        $("#cycle_time").text(comma(machineData[0][clickNum].cycle_time));
        $("#cavity").text(comma(machineData[0][clickNum].cavity));
        $("#shot_cnt").text(comma(machineData[0][clickNum].shot_cnt));

        $("#progress").empty();
        $("#progress").append(progress);
        if(is_processing == 0){
          $("#is_processing").text("가동정지");
        }else{
          $("#is_processing").text("가동중");
        }
      });
    }else{
      alert(result.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}
