if(JSON.parse(getCookie("user_data")).dept_id != 5){
  alert("페이지 접근 권한이 없습니다");
  history.back();
}

let today = new Date();

let year = today.getFullYear(); // 년도
let month = today.getMonth() + 1;  // 월
let date = today.getDate();  // 날짜

$("#process_date").val(year + '-' + month + '-' + date); //오늘날짜적용

$(function(){
  $("#product_history").addClass("open");
  $("#assembly").addClass("active");

  if($("#product_history").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
  if($("#assembly").find("a").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
});
var search_type = 0; //0=수주번호 검색 1=입고처 검색

$("#order_no").on("click", function(){
  search_type = 0;
  $("#search_modal").fadeIn(300);
  $("#modal_back").fadeIn(300);
  $("#search_text").val("");
  $("#search_table").empty();
  $(".search_result_box").css("display","none");
});
$("#from_id").on("click", function(){
  search_type = 1;
  $("#search_modal").fadeIn(300);
  $("#modal_back").fadeIn(300);
  $("#search_text").val("");
  $("#search_table").empty();
  $(".search_result_box").css("display","none");
});

$("#search_text").keydown(function(key) {
  if (key.keyCode == 13) {
    var search_text = $("#search_text").val();
    if(search_type == 0){
      if(search_text == ""){
        alert("수주번호를 입력해주세요");
        return;
      }else{
        order_search(search_text);
      }
    }else{
      if(search_text == ""){
        alert("거래처명을 입력해주세요");
        return;
      }else{
        customer_search(search_text);
      }
    }
  }
});

$("#search_btn").on("click", function(){
  var search_text = $("#search_text").val();
  if(search_type == 0){
    if(search_text == ""){
      alert("수주번호를 입력해주세요");
      return;
    }else{
      order_search(search_text);
    }
  }else{
    if(search_text == ""){
      alert("거래처명을 입력해주세요");
      return;
    }else{
      customer_search(search_text);
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
        console.log(code);
        var order_no = $(this).data("order_no");
        $("#order_no").val(order_no);
        $("#order_id").val(code);
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

function customer_search(search){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/master/customer/index.php",
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
          text += "<th>거래처명</th>";
          text += "</tr>";
      if(jsonResult.length == 0){
        text += '<tr><td>검색결과가 없습니다</td></tr>';
      }else{
        for(var i in jsonResult){
          text +='<tr data-code='+jsonResult[i].id+' data-name='+jsonResult[i].name+'>';
          text +="  <td>"+jsonResult[i].name+"</td>";
          text +="</tr>";
        }
      }

      $("#search_table").empty();
      $("#search_table").append(text);
      $("#search_table tr").on("click", function(){
        var code = $(this).data("code");
        var customer_name = $(this).data("name");
        $("#from_id").val(customer_name);
        $("#from_id").data("from_id", $(this).data("code"));
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
        text +='<option value='+jsonResult[i].id+' data-pakage='+jsonResult[i].product_code+' data-customer_name='+jsonResult[i].customer_name+' data-from_id='+jsonResult[i].customer_id+'>'+jsonResult[i].code+'</option>'
      }
      $("#barju_no").empty();
      $("#barju_no").append(text);

      $("#barju_no").on("change", function(){
        var barju_no = $(this).val();
        balju_data(barju_no);
        pakage_data($("#barju_no").find("option:selected").data("pakage"));
        $("#from_id").val($("#barju_no").find("option:selected").data("customer_name"));
        $("#from_id").data("from_id", $("#barju_no").find("option:selected").data("from_id"));
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
      $("#product_id").val(jsonResult[0].product_id);
      $("#asset_name").val(jsonResult[0].asset_no);
      $("#asset_id").val(jsonResult[0].asset_id);
    }else{
      alert(result.message);
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

function pakage_data(code){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/master/package/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        id:code
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
    var product_id = $("#product_id").val();
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
    if($("#from_id").val() == ""){
      alert("거래처를 입력해주세요");
    }
    for (const node of $("style")) {
        cssText += node.innerHTML
    }
    $.ajax({
        type    : "POST",
        url        : "../api/cosmetics/assemble/put/index.php",
        headers : {
          "content-type": "application/json",
          Authorization : user_data.token,
        },
        dataType:"json",
        data:JSON.stringify({
          type: "outsourcing",
          order_id : $("#order_id").val(),
          process_order_id : barju_no,
          product_id : product_id,
          qty : product_cnt,
          from_id : $("#from_id").data("from_id"),
          print_qty : $("#print_cnt").val(),
          process_date : $("#process_date").val()
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
        //  innerHtml +='      <div id="injection_id" class="injection_id">'+jsonResult[i].asset_no.substring(3,5)+'</div> ';
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
          innerHtml +='          <th>입고처</th>';
          innerHtml +='          <td>'+jsonResult[i].customer_name+'</td>';
          innerHtml +='        </tr>';
          innerHtml +='        <tr>';
          innerHtml +='          <th>입고일자</th>';
          innerHtml +='          <td>'+jsonResult[i].created_at.substr(0,10)+'</td>';
          innerHtml +='        </tr>';
          innerHtml +='        <tr>';
          innerHtml +='        <th>입고지</th>';
          innerHtml +='          <td>'+jsonResult[i].dept_name+'</td>';
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
