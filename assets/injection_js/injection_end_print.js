if(JSON.parse(getCookie("user_data")).dept_id != 6){
  alert("페이지 접근 권한이 없습니다");
  history.back();
}
$(function(){
  $("#product_history").addClass("open");
  $("#injection").addClass("active");
  if($("#product_history").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
  if($("#injection").find("a").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }

  injection_end_detail();
});

var date = new Date();
var year = date.getFullYear();
var month = new String(date.getMonth()+1);
var day = new String(date.getDate());

// 한자리수일 경우 0을 채워준다.
if(month.length == 1){
  month = "0" + month;
}
if(day.length == 1){
  day = "0" + day;
}

$("#create_at").val(year + "-" + month + "-" + day);



function injection_end_detail(){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/qr/start/index.php",
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
      $("#order_no").val(jsonResult[0].order_no);
      $("#order_id").val(jsonResult[0].order_id);
      $("#asset_name").val(jsonResult[0].asset_no);
      $("#asset_id").val(jsonResult[0].asset_id);
      $("#material_id").val(jsonResult[0].material_id);
      product_balju(jsonResult[0].order_id);
    }else{
      alert(result.message);
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

function product_balju(order_id){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/master/process-order/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        order_id: order_id
      }
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      var jsonResult = result.data;
      console.log(jsonResult);
      var text = '<option disabled selected hidden>제품명(발주번호)를 선택하세요</option>';
      for(var i in jsonResult){
        text += '<option value="'+jsonResult[i].id+'" data-product_id="'+jsonResult[i].product_id+'"  data-product_code="'+jsonResult[i].product_code+'">'+jsonResult[i].product_name+' ('+jsonResult[i].code+')</option>';
      }
      $("#product_balju").empty();
      $("#product_balju").append(text);
    }else{
      alert(result.message);
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

$("#product_balju").on("change", function(){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/master/package/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        id: $(this).find("option:selected").data("product_code")
      }
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      var jsonResult = result.data;
      console.log(jsonResult);
      $("#product_cnt").empty();
      $("#product_cnt").val(jsonResult.boxspec5);
    }else{
      alert(result.message);
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
});

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
    var barju_no = $("#product_balju").val();
    var print_cnt = $("#print_cnt").val();
    var product_cnt = $("#product_cnt").val();
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
    if(create_at == ""){
      alert("생산일자를 선택해주세요");
      return;
    }

    for (const node of $("style")) {
        cssText += node.innerHTML
    }
    $.ajax({
        type    : "POST",
        url        : "../api/cosmetics/qr/complete/index.php",
        headers : {
          "content-type": "application/json",
          Authorization : user_data.token,
        },
        dataType:"json",
        data:JSON.stringify({
          order_id : order_id,
          process_order_id : barju_no,
          qty : product_cnt,
          product_id: $("#product_balju").find("option:selected").data("product_id"),
          print_qty : print_cnt,
          created_at : $("#create_at").val(),
          asset_id : $("#asset_id").val()
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
          innerHtml +='          <th>제품명</th>';
          innerHtml +='          <td>'+jsonResult[i].product_name+'</td>';
          innerHtml +='        </tr>';
          innerHtml +='        <tr>';
          innerHtml +='          <th>수량</th>';
          innerHtml +='          <td>'+jsonResult[i].qty+'</td>';
          innerHtml +='        </tr>';
          innerHtml +='        <tr>';
          innerHtml +='          <th>설비번호</th>';
          innerHtml +='          <td>'+jsonResult[i].asset_no+'</td>';
          innerHtml +='        </tr>';
          innerHtml +='        <tr>';
          innerHtml +='          <th>생산일자</th>';
          innerHtml +='          <td>'+jsonResult[i].created_at.substr(0,10)+'</td>';
          innerHtml +='        </tr>';
          innerHtml +='        <tr>';
          innerHtml +='          <th>부서</th>';
          innerHtml +='          <td>'+jsonResult[i].dept_name+'</td>';
          innerHtml +='        </tr>';
          innerHtml +='        <tr>';
          innerHtml +='          <td colspan="2" align="center" style="font-weight:bold;">(주)신신화학공업</td>';
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
