if(JSON.parse(getCookie("user_data")).dept_id != 6 && JSON.parse(getCookie("user_data")).dept_id != 4){
  alert("페이지 접근 권한이 없습니다");
  history.back();
}
$(document).ready(function(){
  $("#product_history").addClass("open");
  $("#material").addClass("active");
  if($("#product_history").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
  if($("#material").find("a").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
});

var search_type = "";
var material_id = "";
material_data();

$("#order_no").on("click", function(){
    search_type = "order_no";
    $("#search_modal").fadeIn(300);
    $("#modal_back").fadeIn(300);
    $("#search_text").val("");
    $("#search_table").empty();
    $(".search_result_box").css("display","none");
});
$("#lot_no").on("click", function(){
  if(material_id == ""){
    alert("발주번호를 먼저 선택하세요");
    return;
  }else{
    search_type = "lot_no";
    $("#search_modal").fadeIn(300);
    $("#modal_back").fadeIn(300);
    $("#search_text").val("");
    $("#search_table").empty();
    $(".search_result_box").css("display","none");
  }
});

$("#search_text").keydown(function(key) {
  if (key.keyCode == 13) {
    var search_text = $("#search_text").val();
    if(search_type == "order_no"){
      if(search_text == ""){
        alert("수주번호를 입력해주세요");
        return;
      }else{
        order_search(search_text);
      }
    }else if(search_type == "lot_no"){
      if(search_text == ""){
        alert("LOT번호를 입력해주세요");
        return;
      }else{
        lot_search(search_text);
      }
    }
  }
});

$("#search_btn").on("click", function(){
  var search_text = $("#search_text").val();
  if(search_type == "order_no"){
    if(search_text == ""){
      alert("수주번호를 입력해주세요");
      return;
    }else{
      order_search(search_text);
    }
  }else if(search_type == "lot_no"){
    if(search_text == ""){
      alert("LOT번호를 입력해주세요");
      return;
    }else{
      lot_search(search_text);
    }
  }
});
$("#modal_back").on("click", function(){
  modal_off();
});

function modal_off(){
  $("#search_modal").fadeOut(300);
  $("#modal_back").fadeOut(300);
}

function material_data(){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/master/material/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        id: getParam('material_id')
      }
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      var jsonResult = result.data;
      console.log(jsonResult);
      $("#material_name").val(jsonResult[0].name);
      $("#jaje_code").val(jsonResult[0].code);
    }else{
      alert(result.message);
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

function order_search(search){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/master/order/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        search: search
      }
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      console.log(result);
      var jsonResult = result.data;
      $(".search_result_box").css("display","block");

      var text = "<tr>";
          text += "<th>수주번호</th>";
          text += "</tr>";

      if(jsonResult.length == 0){
        text += '<tr><td>검색결과가 없습니다</td></tr>';
      }else{
        for(var i in jsonResult){
          text +='<tr data-code='+jsonResult[i].id+' data-order_no='+jsonResult[i].order_no+'>';
          text +="  <td>"+jsonResult[i].order_no+"</td>";
          text +="</tr>";
        }
      }

      $("#search_table").empty();
      $("#search_table").append(text);
      $("#search_table tr").on("click", function(){
        var code = $(this).data("code");
        var order_no = $(this).data("order_no");
        $("#order_id").val(code);
        $("#order_no").val(order_no);
        balju_select(code);
        modal_off();
      })
    }else{
      alert(result.message);
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
};

function balju_select(order_no){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/master/process-order/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        order_id: order_no,
        material_id: getParam("material_id"),
        type: "M"
      }
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      var jsonResult = result.data;
      console.log(jsonResult);
      var text = '';
      for(var i in jsonResult){
        text +='<option selected hidden disabled>발주번호를 선택하세요</option>';
        text +='<option value='+jsonResult[i].id+' data-material_id='+jsonResult[i].material_id+'>'+jsonResult[i].code+'</option>'
      }
      $("#barju_no").empty();
      $("#barju_no").append(text);

      $("#barju_no").on("change", function(){
        var barju_no = $(this).val();
        balju_data(barju_no);
      });
    }else{
      alert(result.message);
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}
function balju_data(barju_no){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/master/process-order/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        id: barju_no
      }
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      var jsonResult = result.data;
      console.log(jsonResult);
      //$("#material_name").val(jsonResult[0].material_name);
      //$("#jaje_code").val(jsonResult[0].jaje_code);
      $("#product_name").val(jsonResult[0].product_name);
      $("#asset_name").val(jsonResult[0].asset_no);
      $("#asset_id").val(jsonResult[0].asset_id);
      material_id = jsonResult[0].material_id;
    }else{
      alert(result.message);
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

function lot_search(search){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/master/material-lot/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        id: getParam("material_id"),
        search: search
      }
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      console.log(result);
      var jsonResult = result.data;
      $(".search_result_box").css("display","block");

      var text = "<tr>";
          text += "<th>LOT번호</th>";
          text += "</tr>";

      if(jsonResult.length == 0){
        text += '<tr><td>검색결과가 없습니다</td></tr>';
      }else{
        for(var i in jsonResult){
          text +='<tr data-code='+jsonResult[i].id+' data-lot_no='+jsonResult[i].lot_no+'>';
          text +="  <td>"+jsonResult[i].lot_no+"</td>";
          text +="</tr>";
        }
      }

      $("#search_table").empty();
      $("#search_table").append(text);
      $("#search_table tr").on("click", function(){
        var code = $(this).data("code");
        var lot_no = $(this).data("lot_no");
        $("#lot_no").val(lot_no);
        modal_off();
      })
    }else{
      alert(result.message);
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
};

// var qrcode = new QRCode(document.getElementById("qrcode"+i), {
//     text: "abc123"+i,
//     width: 113,
//     height: 113,
//     colorDark : "#000000",
//     colorLight : "#ffffff",
//     correctLevel : QRCode.CorrectLevel.H
// });

function print(){
    var order_no = $("#order_no").val();
    var order_id = $("#order_id").val();
    var barju_no = $("#barju_no").val();
    var print_cnt = $("#print_cnt").val();
    var product_cnt = $("#product_cnt").val();
    var lot_no = $("#lot_no").val();
    if(order_no == ""){
      alert("수주번호를 선택해주세요");
      return;
    }
    if(barju_no == ""){
      alert("발주번호를 선택해주세요");
      return;
    }
    if(print_cnt == ""){
      alert("출력건수를 입력해주세요");
      return;
    }
    if(product_cnt == ""){
      alert("제품수량을 입력해주세요");
      return;
    }
    if(lot_no == ""){
      alert("LOT번호를 검색해주세요");
      return;
    }

    for (const node of $("style")) {
        cssText += node.innerHTML
    }
    $.ajax({
        type    : "POST",
        url        : "../api/cosmetics/qr/start/index.php",
        headers : {
          "content-type": "application/json",
          Authorization : user_data.token,
        },
        dataType:"json",
        data:JSON.stringify({
          order_id : order_id,
          process_order_id : barju_no,
          qty : product_cnt,
          print_qty : print_cnt,
          material_id : getParam("material_id"),
          asset_id : $("#asset_id").val(),
          lot_no: $("#lot_no").val()
        })
    }).done(function (result, textStatus, xhr) {
      if(result.status == 200){
        var jsonResult = result.data;
        console.log(result);
        sessionStorage.setItem("jsonResult", JSON.stringify(jsonResult));
        /** 팝업 */
        var innerHtml = "";
        for(var i in jsonResult){
          //var num = parseInt(i)+1;
          innerHtml +='<div id="print_box">';
          innerHtml +='  <div class="print_box">';
          innerHtml +='    <div class="print_inner_line">';
          innerHtml +='      <div id="qrcode'+i+'" class="qrcode"></div>';
          innerHtml +='      <div id="qr_id" class="qr_id">No: '+jsonResult[i].qr_id+'</div> ';
          innerHtml +='      <div id="injection_id" class="injection_id">'+jsonResult[i].asset_no.substring(3,5)+'</div> ';
          innerHtml +='      <table cellpadding="0" cellspacing="0" class="print_table" border="1" width="100%">';
          innerHtml +='        <tr>';
          innerHtml +='          <th>수주번호</th>';
          innerHtml +='          <td>'+jsonResult[i].order_no+'</td>';
          innerHtml +='        </tr>';
          innerHtml +='        <tr>';
          innerHtml +='          <th>원자재명</th>';
          innerHtml +='          <td>'+jsonResult[i].material_name+'</td>';
          innerHtml +='        </tr>';
          innerHtml +='        <tr>';
          innerHtml +='          <th>자재코드</th>';
          innerHtml +='          <td>'+jsonResult[i].jaje_code+'</td>';
          innerHtml +='        </tr>';
          innerHtml +='        <tr>';
          innerHtml +='          <th>용량</th>';
          innerHtml +='          <td>'+jsonResult[i].qty+' '+jsonResult[i].unit+'</td>';
          innerHtml +='        </tr>';
          innerHtml +='        <tr>';
          innerHtml +='          <th>제품명</th>';
          innerHtml +='          <td>'+jsonResult[i].product_name+'</td>';
          innerHtml +='        </tr>';
          innerHtml +='        <tr>';
          innerHtml +='        <th>설비번호</th>';
          innerHtml +='          <td>'+jsonResult[i].asset_no+'</td>';
          innerHtml +='        </tr>';
          innerHtml +='      </table>';
          innerHtml +='    </div>';
          innerHtml +='  </div>';
          innerHtml +='</div>';
        }
        let popupWindow = window.open("", "_blank", "width=500,height=800")
        popupWindow.document.write("<!DOCTYPE html>"+
          "<html>"+
            "<head>"+
            "<script src='../assets/js/jquery.js'></script>"+
            "<script type='text/javascript' src='../assets/js/qrcode.js'></script>"+
            "<link rel='stylesheet' type='text/css' href='../assets/css/style.css'>"+
            "</head>"+
            "<body style='margin:0'>"+innerHtml+"</body>"+
            "<script>"+
              "var qr_data = JSON.parse(sessionStorage.getItem('jsonResult'));"+
              "for(var i in qr_data){"+
              "var qrcode = new QRCode(document.getElementById('qrcode'+i), {"+
              "    text: ''+qr_data[i].qr_id,"+//qr_data[i].qr_id
              "    width: 113,"+
              "    height: 113,"+
              "    colorDark : '#000000',"+
              "    colorLight : '#ffffff',"+
              "    correctLevel : QRCode.CorrectLevel.H"+

              "});"+

              "console.log(qr_data[i].qr_id);"+
              "}"+
            "</script>"+
          "</html>")

        popupWindow.document.close()
        popupWindow.focus()

        /** 1초 지연 */
        setTimeout(() => {
            popupWindow.print()         // 팝업의 프린트 도구 시작
            // popupWindow.close()         // 프린트 도구 닫혔을 경우 팝업 닫기
        }, 1000)
      }else{
        alert(result.message);
      }
    }).fail(function(data, textStatus, errorThrown){
      console.log("전송 실패");
    });

}
