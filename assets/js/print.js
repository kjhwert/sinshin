
// var qrcode = new QRCode(document.getElementById("qrcode"+i), {
//     text: "abc123"+i,
//     width: 113,
//     height: 113,
//     colorDark : "#000000",
//     colorLight : "#ffffff",
//     correctLevel : QRCode.CorrectLevel.H
// });

function print(){
    var print_cnt = $("#print_cnt").val();
    var product_cnt = $("#product_cnt").val();
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
    /** 팝업 */
    var innerHtml = "";
    for(var i=1;i<=print_cnt;i++){
      innerHtml +='<div id="print_box">';
      innerHtml +='  <div class="print_box">';
      innerHtml +='    <div class="print_inner_line">';
      innerHtml +='      <div id="qrcode'+i+'" class="qrcode"></div>';
      innerHtml +='      <table cellpadding="0" cellspacing="0" class="print_table" border="1">';
      innerHtml +='        <tr>';
      innerHtml +='          <th>수주번호</th>';
      innerHtml +='          <td>4502613654 / 빌리프</td>';
      innerHtml +='        </tr>';
      innerHtml +='        <tr>';
      innerHtml +='          <th>제품명</th>';
      innerHtml +='          <td>BFFA오버N브라이트닝마스크용기50ML 외용기</td>';
      innerHtml +='        </tr>';
      innerHtml +='        <tr>';
      innerHtml +='          <th>구분</th>';
      innerHtml +='          <td>주간</td>';
      innerHtml +='        </tr>';
      innerHtml +='        <tr>';
      innerHtml +='          <th>생산일자</th>';
      innerHtml +='          <td>2020년 7월 2일</td>';
      innerHtml +='        </tr>';
      innerHtml +='        <tr>';
      innerHtml +='          <th>부서명</th>';
      innerHtml +='          <td>사출실</td>';
      innerHtml +='        </tr>';
      innerHtml +='        <tr>';
      innerHtml +='        <th>수량</th>';
      innerHtml +='          <td>'+product_cnt+'</td>';
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
        "<body>"+innerHtml+"</body>"+
        "<script>"+
          "for(var i=1;i<="+print_cnt+";i++){"+
          "var qrcode = new QRCode(document.getElementById('qrcode'+i), {"+
          "    text: 'abc123'+i,"+
          "    width: 113,"+
          "    height: 113,"+
          "    colorDark : '#000000',"+
          "    colorLight : '#ffffff',"+
          "    correctLevel : QRCode.CorrectLevel.H"+

          "});"+
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
}
