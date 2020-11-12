var page_no = "";
var per_page = 15;
if(getParam("page_no") == "" || getParam("page_no") == "0"){
  page_no = 1;
}else{
  page_no = getParam("page_no");
}

$(function(){
  $("#system_management").addClass("open");
  $("#authority_management").addClass("active");

  if($("#system_management").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
  if($("#authority_management").find("a").css("display") == "none"){
    alert("페이지 접근 권한이 없습니다");
    history.back();
  }
  authority_list(page_no, per_page);
});

function authority_list(page, perPage){
  $.ajax({
      type    : "GET",
      url        : "../api/auth/group/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        page: page,
        perPage: perPage
      }
  }).done(function (data, textStatus, xhr) {
    var text = '';
    if(data.status == 200){
      for(var i in data.data){
        text+='<tr>';
        text+='  <td>'+data.data[i].id+'</td>';
        text+='  <td>'+data.data[i].name+'</td>';
        text+='  <td>';
        text+='    <div class="btn-group btn-group-sm" role="group" aria-label="Basic example">';
        text+='      <a href="../system_management/dept_management.html?id='+data.data[i].id+'">';
        text+='        <button type="button" class="btn btn-warning">부서관리</button>';
        text+='      </a>&nbsp';
        text+='      <a onclick="auth_delete('+data.data[i].id+')">';
        text+='        <button type="button" class="btn btn-danger">삭제</button>';
        text+='      </a>';
        text+='    </div>';
        text+='  </td>';
        text+='</tr>';
      }
      $("#authority_list_tbody").empty();
      $("#authority_list_tbody").append(text);

      paging(data.paging.end_page,data.paging.start_page,data.paging.total_page);
    }else{
      alert(data.message);
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
    text +='<a class="page-link" href="./authority_list.html?page_no='+pre_no+'" aria-label="Previous">';
    text +=' <span aria-hidden="true">Prev</span>';
    text +=' <span class="sr-only">Previous</span>';
    text +='</a>';
    text +='</li>';
  }
  for( var k = paging_init_num; k <= paging_end_num; k++){
    if (parseInt(page_no) == k)
    {
      text +='<li class="page-item active"><a class="page-link" href="./authority_list.html?page_no='+k+'">'+k+'</a></li>';
    }else{
      text +='<li class="page-item"><a class="page-link" href="./authority_list.html?page_no='+k+'">'+k+'</a></li>';
    }
  }
  if (total_paging_cnt == 0 || total_paging_cnt == 1 || next_no > total_paging_cnt)
  {
  }else{
    text +='<li class="page-item">';
    text +='  <a class="page-link" href="./authority_list.html?page_no='+next_no+'" aria-label="Next">';
    text +='    <span aria-hidden="true">Next</span>';
    text +='    <span class="sr-only">Next</span>';
    text +='  </a>';
    text +='</li>';
  }

  $("#pagination").empty();
  $("#pagination").append(text);
}

function auth_delete(id){
  var result = confirm('삭제 하시겠습니까?');
  if(result) { //yes location.replace('index.php');
    $.ajax({
        type    : "DELETE",
        url        : "../api/auth/group/index.php",
        headers : {
          "content-type": "application/json",
          Authorization : user_data.token,
        },
        dataType:"json",
        data:JSON.stringify({
          id: id
        })
    }).done(function (data, textStatus, xhr) {
      var text = '';
      if(data.status == 200){
        alert("삭제 되었습니다");
        location.reload();
      }else{
        alert(data.message);
        return;
      }
    }).fail(function(data, textStatus, errorThrown){
      console.log("전송 실패");
    });
  } else { //no

  }
}
