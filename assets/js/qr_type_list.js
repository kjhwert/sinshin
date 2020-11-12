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
  $("#qr_type_page").addClass("active");

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
      url        : "../api/cosmetics/master/qr/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json"
  }).done(function (result, textStatus, xhr) {
    var text = '';
    if(result.status == 200){
      var jsonResult = result.data;
      console.log(jsonResult);
      for(var i in jsonResult){
        text +='<tr>';
        text +='  <td class="text-center">'+jsonResult[i].RNUM+'</td>';
        text +='  <td>'+jsonResult[i].name+'</td>';
        text +='  <td class="text-center">'+jsonResult[i].type+'</td>';
        text +='  <td class="text-center">'+jsonResult[i].dept_name+'</td>';
        text +='  <td class="text-center">'+jsonResult[i].created_at+'</td>';
        text +='  <td class="text-center"> <button type="button" class="btn btn-bg-gradient-x-orange-yellow" onclick="qr_detail('+jsonResult[i].RNUM+');">상세보기</button></td>';
        text +='</tr>';
      }
      $("#qr_print_list").empty();
      $("#qr_print_list").append(text);
    }else{
      alert(result.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
}

function qr_detail(rnum){
  location.href="./qr_detail.html?id="+rnum;
}
