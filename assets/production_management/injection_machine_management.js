var page_no = getParam("page_no");
var per_page = 15;
var decodeName = decodeURI(decodeURIComponent(getParam("asset")));
if(getParam("page_no") == ""){
  page_no = 1;
}else{
  page_no = getParam("page_no");
}
$("#asset_no").val(decodeName);

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

  repair_list();
});


$("#injection_insert").on("click", function(){
  if($("#manager").val() == ""){alert("담당자를 입력해주세요");return;};
  $.ajax({
      type    : "POST",
      url        : "../api/cosmetics/master/asset/repair/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:JSON.stringify({
        asset_id : getParam("id"),
        manager : $("#manager").val(),
        repair_date : $("#repair_date").val(),
        hydraulic_date : $("#hydraulic_date").val(),
        lubricant_date : $("#lubricant_date").val(),
        filter_date : $("#filter_date").val()
      })
  }).done(function (result, textStatus, xhr) {
    var text = '';
    if(result.status == 200){
      alert("등록 되었습니다");
      repair_list();
    }else{
      alert(result.message);
      return;
    }
  }).fail(function(data, textStatus, errorThrown){
    console.log("전송 실패");
  });
});

function repair_list(){
  $.ajax({
      type    : "GET",
      url        : "../api/cosmetics/master/asset/repair/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        id : getParam("id"),
        page: page_no,
        perPage: per_page
      }
  }).done(function (result, textStatus, xhr) {
    var jsonResult = result.data;
    console.log(jsonResult);
    var text = '';
    for(var i in jsonResult){
      text +='<tr>';
      text +='  <td>'+jsonResult[i].RNUM+'</td>';
      text +='  <td>'+jsonResult[i].repair_date+'</td>';
      text +='  <td>'+jsonResult[i].hydraulic_date+'</td>';
      text +='  <td>'+jsonResult[i].lubricant_date+'</td>';
      text +='  <td>'+jsonResult[i].filter_date+'</td>';
      text +='  <td>'+jsonResult[i].manager+'</td>';
      text +=' </tr>';
    }
    $("#repair_list").empty();
    $("#repair_list").append(text);
    paging(result.paging.end_page, result.paging.start_page, result.paging.total_page);
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
    text +='<a class="page-link" href="./injection_machine_management.html?page_no='+pre_no+'" aria-label="Previous">';
    text +=' <span aria-hidden="true">Prev</span>';
    text +=' <span class="sr-only">Previous</span>';
    text +='</a>';
    text +='</li>';
  }
  for( var k = paging_init_num; k <= paging_end_num; k++){
    if (parseInt(page_no) == k)
    {
      text +='<li class="page-item active"><a class="page-link" href="./injection_machine_management.html?page_no='+k+'">'+k+'</a></li>';
    }else{
      text +='<li class="page-item"><a class="page-link" href="./injection_machine_management.html?page_no='+k+'">'+k+'</a></li>';
    }
  }
  if (total_paging_cnt == 0 || total_paging_cnt == 1 || next_no > total_paging_cnt)
  {
  }else{
    text +='<li class="page-item">';
    text +='  <a class="page-link" href="./injection_machine_management.html?page_no='+next_no+'" aria-label="Next">';
    text +='    <span aria-hidden="true">Next</span>';
    text +='    <span class="sr-only">Next</span>';
    text +='  </a>';
    text +='</li>';
  }
  $("#pagination").empty();
  $("#pagination").append(text);
}
