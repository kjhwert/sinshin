var page_no = getParam("page_no");
var per_page = 15;
var search_text = decodeURIComponent(getParam("search_text"));

if(getParam("page_no") == ""){
  page_no = 1;
}else{
  page_no = getParam("page_no");
}

$(function(){
  $("#system_management").addClass("open");
  $("#qr_print_page").addClass("active");

  qr_print_list(page_no, per_page);
  if($("#system_management").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
  if($("#qr_print_page").find("a").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
});

function qr_print_list(page_no, per_page){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/master/qr-code/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        search: search_text,
        page: page_no,
        perPage: per_page
      }
  }).done(function (result, textStatus, xhr) {
    var text = '';
    if(result.status == 200){
      var jsonResult = result.data;
      console.log(jsonResult);
      for(var i in jsonResult){
        text +='<tr>';
        text +='  <td>'+jsonResult[i].RNUM+'</td>';
        text +='  <td>'+jsonResult[i].id+'</td>';
        text +='  <td>'+jsonResult[i].qr_type+'</td>';
        text +='  <td>'+jsonResult[i].order_no+'</td>';
        text +='  <td>'+jsonResult[i].process_name+'</td>';
        text +='  <td>'+jsonResult[i].dept_name+'</td>';
        text +='  <td>'+jsonResult[i].created_at+'</td>';
        text +='  <td>'+jsonResult[i].manager+'</td>';
        text +='  <td>';
        text +='    <div class="btn-group btn-group-sm" role="group" aria-label="Basic example">';
        text +='      <a>';
        text +='        <button type="button" class="btn btn-bg-gradient-x-orange-yellow" onclick="print('+jsonResult[i].qr_master_id+', '+jsonResult[i].id+')">재출력</button>';
        text +='      </a>';
        text +='    </div>';
        text +='  </td>';
        text +='</tr>';
      }
      $("#qr_print_list").empty();
      $("#qr_print_list").append(text);

      paging(result.paging.end_page, result.paging.start_page, result.paging.total_page);
    }else{
      alert(result.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}


function paging(end, start, total){
  var paging_init_num = parseInt(start);
  var paging_end_num = parseInt(end);
  var total_paging_cnt = parseInt(total);
  var pre_no = parseInt(page_no) - 1;
  var next_no = parseInt(page_no) + 1;
  var text = '';
  if (total_paging_cnt == 0 || total_paging_cnt == 1 || pre_no == 0)
  {
  }else{
    text +='<li class="page-item">';
    text +='<a class="page-link" href="./qr_print_list.html?page_no='+pre_no+'&search_text='+search_text+'" aria-label="Previous">';
    text +=' <span aria-hidden="true">Prev</span>';
    text +=' <span class="sr-only">Previous</span>';
    text +='</a>';
    text +='</li>';
  }
  for( var k = paging_init_num; k <= paging_end_num; k++){
    if (parseInt(page_no) == k)
    {
      text +='<li class="page-item active"><a class="page-link" href="./qr_print_list.html?page_no='+k+'&search_text='+search_text+'">'+k+'</a></li>';
    }else{
      text +='<li class="page-item"><a class="page-link" href="./qr_print_list.html?page_no='+k+'&search_text='+search_text+'">'+k+'</a></li>';
    }
  }
  if (total_paging_cnt == 0 || total_paging_cnt == 1 || next_no > total_paging_cnt)
  {
  }else{
    text +='<li class="page-item">';
    text +='  <a class="page-link" href="./qr_print_list.html?page_no='+next_no+'&search_text='+search_text+'" aria-label="Next">';
    text +='    <span aria-hidden="true">Next</span>';
    text +='    <span class="sr-only">Next</span>';
    text +='  </a>';
    text +='</li>';
  }
  $("#pagination").empty();
  $("#pagination").append(text);
}

$("#search_btn").on("click", function(){
  location.href="./qr_print_list.html?search_text="+$("#search_text").val();
});

function print(qr_master_id, qr_no){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/master/qr-code/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        id: qr_no
      }
  }).done(function (result, textStatus, xhr) {
    var jsonResult = result.data;
    console.log(jsonResult);
    var innerHtml = "";
    sessionStorage.setItem("qr_no", jsonResult.id);
    //var num = parseInt(i)+1;
    if(qr_master_id == 1){ //원자재 입고 QR FORM
      innerHtml +='<div id="print_box">';
      innerHtml +='  <div class="print_box">';
      innerHtml +='    <div class="print_inner_line">';
      innerHtml +='      <div id="qrcode1" class="qrcode"></div>';
      innerHtml +='      <div id="qr_id" class="qr_id">No: '+jsonResult.id+'</div> ';
      innerHtml +='      <div id="injection_id" class="injection_id">'+jsonResult.asset_name.substring(3,5)+'</div> ';
      innerHtml +='      <table cellpadding="0" cellspacing="0" class="print_table" border="1" width="100%">';
      innerHtml +='        <tr>';
      innerHtml +='          <th>수주번호</th>';
      innerHtml +='          <td>'+jsonResult.order_no+'</td>';
      innerHtml +='        </tr>';
      innerHtml +='        <tr>';
      innerHtml +='          <th>원자재명</th>';
      innerHtml +='          <td>'+jsonResult.material_name+'</td>';
      innerHtml +='        </tr>';
      innerHtml +='        <tr>';
      innerHtml +='          <th>자재코드</th>';
      innerHtml +='          <td>'+jsonResult.jaje_code+'</td>';
      innerHtml +='        </tr>';
      innerHtml +='        <tr>';
      innerHtml +='          <th>용량</th>';
      innerHtml +='          <td>'+jsonResult.material_qty+' '+jsonResult.material_unit+'</td>';
      innerHtml +='        </tr>';
      innerHtml +='        <tr>';
      innerHtml +='          <th>제품명</th>';
      innerHtml +='          <td>'+jsonResult.product_name+'</td>';
      innerHtml +='        </tr>';
      innerHtml +='        <tr>';
      innerHtml +='        <th>설비번호</th>';
      innerHtml +='          <td>'+jsonResult.asset_name+'</td>';
      innerHtml +='        </tr>';
      innerHtml +='      </table>';
      innerHtml +='    </div>';
      innerHtml +='  </div>';
      innerHtml +='</div>';
    }else if(qr_master_id == 2){
      innerHtml +='<div id="print_box">';
      innerHtml +='  <div class="print_box">';
      innerHtml +='    <div class="print_inner_line">';
      innerHtml +='      <div id="qrcode1" class="qrcode"></div>';
      innerHtml +='      <div id="qr_id" class="qr_id">No: '+jsonResult.id+'</div> ';
      innerHtml +='      <div id="injection_id" class="injection_id">'+jsonResult.asset_name.substring(3,5)+'</div> ';
      innerHtml +='      <table cellpadding="0" cellspacing="0" class="print_table" border="1" width="100%">';
      innerHtml +='        <tr>';
      innerHtml +='          <th>수주번호</th>';
      innerHtml +='          <td>'+jsonResult.order_no+'</td>';
      innerHtml +='        </tr>';
      innerHtml +='        <tr>';
      innerHtml +='          <th>제품명</th>';
      innerHtml +='          <td>'+jsonResult.product_name+'</td>';
      innerHtml +='        </tr>';
      innerHtml +='        <tr>';
      innerHtml +='          <th>수량</th>';
      innerHtml +='          <td>'+jsonResult.product_qty+'</td>';
      innerHtml +='        </tr>';
      innerHtml +='        <tr>';
      innerHtml +='          <th>설비번호</th>';
      innerHtml +='          <td>'+jsonResult.asset_name+'</td>';
      innerHtml +='        </tr>';
      innerHtml +='        <tr>';
      innerHtml +='          <th>생산일자</th>';
      innerHtml +='          <td>'+jsonResult.created_at.substr(0,10)+'</td>';
      innerHtml +='        </tr>';
      innerHtml +='        <tr>';
      innerHtml +='          <th>부서</th>';
      innerHtml +='          <td>'+jsonResult.dept_name+'</td>';
      innerHtml +='        </tr>';
      innerHtml +='        <tr>';
      innerHtml +='          <td colspan="2" align="center" style="font-weight:bold;">(주)신신화학공업</td>';
      innerHtml +='        </tr>';
      innerHtml +='      </table>';
      innerHtml +='    </div>';
      innerHtml +='  </div>';
      innerHtml +='</div>';
    }else if(qr_master_id == 3){
      innerHtml +='<div id="print_box">';
      innerHtml +='  <div class="print_box">';
      innerHtml +='    <div class="print_inner_line">';
      innerHtml +='      <div id="qrcode1" class="qrcode"></div>';
      innerHtml +='      <div id="qr_id" class="qr_id">No: '+jsonResult.id+'</div> ';
      innerHtml +='      <table cellpadding="0" cellspacing="0" class="print_table" border="1" width="100%">';
      innerHtml +='        <tr>';
      innerHtml +='          <th>수주번호</th>';
      innerHtml +='          <td>'+jsonResult.order_no+'</td>';
      innerHtml +='        </tr>';
      innerHtml +='        <tr>';
      innerHtml +='          <th>제품명</th>';
      innerHtml +='          <td>'+jsonResult.product_name+'</td>';
      innerHtml +='        </tr>';
      innerHtml +='        <tr>';
      innerHtml +='          <th>수량</th>';
      innerHtml +='          <td>'+jsonResult.product_qty+'</td>';
      innerHtml +='        </tr>';
      innerHtml +='        <tr>';
      innerHtml +='          <th>생산일자</th>';
      innerHtml +='          <td>'+jsonResult.created_at.substr(0,10)+'</td>';
      innerHtml +='        </tr>';
      innerHtml +='        <tr>';
      innerHtml +='          <th>부서</th>';
      innerHtml +='          <td>'+jsonResult.dept_name+'</td>';
      innerHtml +='        </tr>';
      innerHtml +='        <tr>';
      innerHtml +='          <td colspan="2" align="center" style="font-weight:bold;">(주)신신화학공업</td>';
      innerHtml +='        </tr>';
      innerHtml +='      </table>';
      innerHtml +='    </div>';
      innerHtml +='  </div>';
      innerHtml +='</div>';
    }else if(qr_master_id == 5){
      innerHtml +='<div id="print_box">';
      innerHtml +='  <div class="print_box">';
      innerHtml +='    <div class="print_inner_line">';
      innerHtml +='			 <p class="qr_no">No. '+jsonResult.id+'</p>';
      innerHtml +='      <div id="qrcode1" class="qrcode2"></div>';
      innerHtml +='      <table cellpadding="0" cellspacing="0" class="print_table2" border="1">';
      innerHtml +='        <tr>';
      innerHtml +='          <th>신신화학공업(주)</th>';
      innerHtml +='        </tr>';
      innerHtml +='      </table>';
      innerHtml +='    </div>';
      innerHtml +='  </div>';
      innerHtml +='</div>';
    }else if(qr_master_id == 6){
      innerHtml +='<div id="print_box">';
      innerHtml +='  <div class="print_box">';
      innerHtml +='    <div class="print_inner_line">';
      innerHtml +='      <div id="qrcode1" class="qrcode"></div>';
      innerHtml +='      <div id="qr_id" class="qr_id">No: '+jsonResult.id+'</div> ';
    //  innerHtml +='      <div id="injection_id" class="injection_id">'+jsonResult[i].asset_no.substring(3,5)+'</div> ';
      innerHtml +='      <table cellpadding="0" cellspacing="0" class="print_table" border="1" width="100%">';
      innerHtml +='        <tr>';
      innerHtml +='          <th>수주번호</th>';
      innerHtml +='          <td>'+jsonResult.order_no+'</td>';
      innerHtml +='        </tr>';
      innerHtml +='        <tr>';
      innerHtml +='          <th>제품명</th>';
      innerHtml +='          <td>'+jsonResult.product_name+'</td>';
      innerHtml +='        </tr>';
      innerHtml +='        <tr>';
      innerHtml +='          <th>수량</th>';
      innerHtml +='          <td>'+jsonResult.product_qty+'</td>';
      innerHtml +='        </tr>';
      innerHtml +='        <tr>';
      innerHtml +='          <th>입고처</th>';
      innerHtml +='          <td>'+jsonResult.from_name+'</td>';
      innerHtml +='        </tr>';
      innerHtml +='        <tr>';
      innerHtml +='          <th>입고일자</th>';
      innerHtml +='          <td>'+jsonResult.created_at.substr(0,10)+'</td>';
      innerHtml +='        </tr>';
      innerHtml +='        <tr>';
      innerHtml +='        <th>입고지</th>';
      innerHtml +='          <td>'+jsonResult.dept_name+'</td>';
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
          "var qrcode = new QRCode(document.getElementById('qrcode1'), {"+
          "    text: '"+jsonResult.id+"',"+//qr_data[i].qr_id
          "    width: 113,"+
          "    height: 113,"+
          "    colorDark : '#000000',"+
          "    colorLight : '#ffffff',"+
          "    correctLevel : QRCode.CorrectLevel.H"+

          "});"+
        "</script>"+
      "</html>")

    popupWindow.document.close()
    popupWindow.focus()

    /** 1초 지연 */
    setTimeout(() => {
        popupWindow.print()         // 팝업의 프린트 도구 시작
        // popupWindow.close()         // 프린트 도구 닫혔을 경우 팝업 닫기
    }, 1000)

  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

function print_master1(){

}
$("#search_text").keydown(function(key) {
  if (key.keyCode == 13) {
    $("#search_btn").click();
  }
});
