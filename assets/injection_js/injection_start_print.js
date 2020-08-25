injection_start_detail();

function injection_start_detail(){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/master/order/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        material_id: getParam("material_id")
      }
  }).done(function (result, textStatus, xhr) {
    if(result.status == 200){
      var jsonResult = result.data;
      console.log(jsonResult);
      var text = '';
      for(var i in jsonResult){
        text +='<option selected hidden disabled>수주번호를 선택하세요</option>';
        text +='<option value='+jsonResult[i].id+'>'+jsonResult[i].order_no+'</option>'
      }
      $("#order_no").empty();
      $("#order_no").append(text);

      $("#order_no").on("change", function(){
        var order_no = $(this).val();
        balju_select(order_no);
      });
    }else{
      alert(result.message);
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

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
        text +='<option id='+jsonResult[i].id+'>'+jsonResult[i].id+'</option>'
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
      $("#material_name").val(jsonResult[0].material_name);
      $("#jaje_code").val(jsonResult[0].jaje_code);
      $("#product_name").val(jsonResult[0].product_name);
    }else{
      alert(result.message);
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}


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
    var barju_no = $("#barju_no").val();
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
          order_id : order_no,
          process_order_id : barju_no,
          qty : product_cnt,
          print_qty : print_cnt,
          material_id : getParam("material_id"),
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
          innerHtml +='      <div id="injection_id" class="injection_id">'+jsonResult[i].asset_id+'</div> ';
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
          innerHtml +='          <td>'+jsonResult[i].asset_name+'</td>';
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
