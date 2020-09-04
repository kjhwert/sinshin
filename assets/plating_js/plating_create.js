$(document).ready(function(){
  $(".time_element").timepicki();
  $("#automotive_management").addClass("open");
  $("#plating").addClass("active");
});

$("#product_num").on("click", function(){
    $("#search_modal").fadeIn(300);
    $("#modal_back").fadeIn(300);
    $("#search_text").val("");
    $("#search_table").empty();
    $(".search_result_box").css("display","none");
});

$("#modal_back").on("click", function(){
  modal_off();
});
function modal_off(){
  $("#search_modal").fadeOut(300);
  $("#cbarno_modal").fadeOut(300);
  $("#modal_back").fadeOut(300);
}
$("#search_btn").on("click", function(){
  var search_text = $("#search_text").val();
  if(search_text == ""){
    alert("품명을 입력해주세요");
    return;
  }else{
    auto_search(search_text);
  }
});

$("#cbarno").on("click", function(){
  var text = "";
  for(var i=1; i<=48; i++){
    text +="<span class='tag btn-light' name="+i+">"+i+"</span>";
  }

  $("#cbarno_list").empty();
  $("#cbarno_list").append(text);
  $("#cbarno_modal").fadeIn(300);
  $("#modal_back").fadeIn(300);

  $("#cbarno_list span").on("click", function(){
    if($(this).hasClass("btn-light") == true){
      $(this).removeClass("btn-light");
      $(this).addClass("btn-info");
    }else{
      $(this).removeClass("btn-info");
      $(this).addClass("btn-light");
    }
  })
});

$("#plating_insert").on("click", function(){

});

function cbarno_insert(){
  var activeNum = "";
  $("span.btn-info").each(function() {
    var id = $(this).attr('name')+ ",";
    activeNum += id;
  });
  activeNum = activeNum.substr(0, activeNum.length -1);
  $("#cbarno").val(activeNum);
  modal_off();
}

function auto_search(search){
  $.ajax({
      type    : "GET",
      url        : "../api/automobile/master/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data     : {
        paging: "false",
        search: search
      }
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      console.log(result);
      var jsonResult = result.data;
      $(".search_result_box").css("display","block");
      var text = "<tr>";
          text += "<th>품번</th>";
          text += "<th>차종</th>";
          text += "</tr>";

      for(var i in jsonResult){
        text +='<tr data-customer='+jsonResult[i].customer+' data-supplier='+jsonResult[i].supplier+' data-product_name='+jsonResult[i].name.replace(/\s/gi, "_")+' data-product_id='+jsonResult[i].id+' data-customer_code='+jsonResult[i].supply_code+'>';
        text +="<td>"+jsonResult[i].customer_code+"</td>";
        text +="<td>"+jsonResult[i].car_code+"</td>";
        text +="</tr>";
      }

      $("#search_table").empty();
      $("#search_table").append(text);
      $("#search_table tr").on("click", function(){
        var customer_data = $(this).data("customer");
        var supplier_data = $(this).data("supplier");
        var product_name = $(this).data("product_name");
        var product_id = $(this).data("product_id");
        var customer_code = $(this).data("customer_code");

        $("#customer").val(customer_data);
        $("#supplier").val(supplier_data);
        $("#product_name").val(product_name);
        $("#product_id").val(product_id);
        $("#search_modal").fadeOut("300");
        $("#modal_back").fadeOut("300");
        $("#product_num").val(customer_code);
      })
    }else{
      alert(result.message);
    }
  }).fail(function(data, textStatus, errorThrown){
      console.log("전송 실패");
  });
}

$("#plating_insert").on("click", function(){
  var input = $("#input_cnt").val();
  var rack = $("#rack").val();
  var product_id = $("#product_id").val();
  var carrier = $("#cbarno").val();
  var lot_no = $("#lot_no").val();
  var day_night = $("#day_night").val();
  var input_date = $("#input_date").val();
  var comp_date = $("#comp_date").val();
  var mfr_date = $("#mfr_date").val();
  var charger = $("#charger").val();

  // if(input == ""){alert("투입수량을 입력해주세요");return;};
  // if(rack == ""){alert("투입랙수량을 입력해주세요");return;};
  // if(product_id == ""){alert("품명을 검색해주세요");return;};
  // if(carrier == ""){alert("C.bar.No를 입력해주세요");return;};
  // if(lot_no == ""){alert("LOT 번호를 입력해주세요");return;};
  // if(day_night == ""){alert("주/야간을 선택해주세요");return;};
  // if(input_date == ""){alert("투입시간을 선택해주세요");return;};
  // if(comp_date == ""){alert("완료시간을 선택해주세요");return;};
  // if(mfr_date == ""){alert("제조일자를 선택해주세요");return;};

  var input_hour = $("#input_date").data("timepicki-tim");
  var input_minute = $("#input_date").data("timepicki-mini");
  var input_night = $("#input_date").val().substr(6,2);
  var comp_hour = $("#comp_date").data("timepicki-tim");
  var comp_minute = $("#comp_date").data("timepicki-mini");
  var comp_night = $("#comp_date").val().substr(6,2);

  if(input_night == "PM"){
    input_date = Number(input_hour)+Number(12) +":"+ input_minute;
  }else{
    input_date = input_hour+":"+input_minute;
  }

  if(comp_night == "PM"){
    comp_date = Number(comp_hour)+Number(12) +":"+ comp_minute;
  }else{
    comp_date = comp_hour+":"+comp_minute;
  }

  $.ajax({
    type    : "POST",
    url        : "../api/automobile/process/index.php",
    headers : {
      "content-type": "application/json",
      Authorization : user_data.token,
    },
    dataType:"json",
    data     : JSON.stringify({
      input: input,
      rack: rack,
      product_id: product_id,
      carrier: carrier,
      charger: charger,
      lot_no: lot_no,
      day_night: day_night,
      input_date: input_date,
      comp_date: comp_date,
      mfr_date: mfr_date
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
})
