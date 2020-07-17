var page_no = "";
var per_page = 15;
if(getParam("page_no") == ""){
  page_no = 1;
}else{
  page_no = getParam("page_no");
}

log_list(page_no, per_page);

function log_list(page_no, per_page){
  $.ajax({
      type    : "GET",
      url        : "../api/user/log/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        page: page_no,
        perPage: per_page
      },
  }).done(function (result, textStatus, xhr) {
    var text = '';
    if(result.status == 200){
      console.log(result);
      for(var i in result.data){
        text +='<tr>';
        text +='  <td>'+result.data[i].RNUM+'</td>';
        text +='  <td>'+result.data[i].user_id+'</td>';
        text +='  <td>'+result.data[i].name+'</td>';
        text +='  <td>'+result.data[i].path+'</td>';
        text +='  <td>'+result.data[i].created_at+'</td>';
        text +='</tr>';
      }
      $("#log_list").empty();
      $("#log_list").append(text);
      paging(result.paging.end_page, result.paging.start_page, result.paging.total_page);
    }else{
      alert(response.message);
      return;
    }
  }).fail(function(response, textStatus, errorThrown){
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
    text +='<a class="page-link" href="./user_log.html?page_no='+pre_no+'" aria-label="Previous">';
    text +=' <span aria-hidden="true">Prev</span>';
    text +=' <span class="sr-only">Previous</span>';
    text +='</a>';
    text +='</li>';
  }
  for( var k = paging_init_num; k <= paging_end_num; k++){
    if (parseInt(page_no) == k)
    {
      text +='<li class="page-item active"><a class="page-link" href="./user_log.html?page_no='+k+'">'+k+'</a></li>';
    }else{
      text +='<li class="page-item"><a class="page-link" href="./user_log.html?page_no='+k+'">'+k+'</a></li>';
    }
  }
  if (total_paging_cnt == 0 || total_paging_cnt == 1 || next_no > total_paging_cnt)
  {
  }else{
    text +='<li class="page-item">';
    text +='  <a class="page-link" href="./user_log.html?page_no='+next_no+'" aria-label="Next">';
    text +='    <span aria-hidden="true">Next</span>';
    text +='    <span class="sr-only">Next</span>';
    text +='  </a>';
    text +='</li>';
  }

  $("#pagination").empty();
  $("#pagination").append(text);
}
