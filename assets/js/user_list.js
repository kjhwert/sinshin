var page_no = "";
var per_page = 15;
var search_text = decodeURIComponent(getParam("search_text"));
if(getParam("page_no") == ""){
  page_no = 1;
}else{
  page_no = getParam("page_no");
}

user_list(page_no, per_page);
function user_list(page_no, per_page){
  $.ajax({
      type    : "GET",
      url        : "../api/user/index.php",
      headers : {
        "content-type": "application/json",
        Authorization : user_data.token,
      },
      dataType:"json",
      data:{
        page: page_no,
        perPage: per_page,
        search: search_text
      }
  }).done(function (data, textStatus, xhr) {
    var text = '';
    if(data.status == 200){
      for(var i in data.data){
        text +='<tr>';
        text +='<th scope="row">3</th>';
        text +='<td>'+data.data[i].user_id+'</td>';
        text +='<td>'+data.data[i].email+'</td>';
        text +='<td>'+data.data[i].name+'</td>';
        text +='<td>'+data.data[i].dept+'</td>';
        text +='  <td>'+data.data[i].position+'</td>';
        text +='  <td>'+data.data[i].duty+'</td>';
        text +='  <td>'+data.data[i].tel+'</td>';
        text +='  <td>'+data.data[i].created_at+'</td>';
        text +='  <td>'+data.data[i].last_access+'</td>';
        text +='  <td>';
        text +='    <div class="btn-group btn-group-sm" role="group" aria-label="Basic example">';
        text +='      <a href="../system_management/user_update.html?user_no='+data.data[i].id+'">';
        text +='        <button type="button" class="btn btn-warning">수정</button></a>';
        text +='    </div>';
        text +='  </td>';
        text +='</tr>';
      }
      $("#user_list_tbody").empty();
      $("#user_list_tbody").append(text);

      paging(data.paging.end_page, data.paging.start_page, data.paging.total_page);
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
    text +='<a class="page-link" href="./user_list.html?page_no='+pre_no+'" aria-label="Previous">';
    text +=' <span aria-hidden="true">Prev</span>';
    text +=' <span class="sr-only">Previous</span>';
    text +='</a>';
    text +='</li>';
  }
  for( var k = paging_init_num; k <= paging_end_num; k++){
    if (parseInt(page_no) == k)
    {
      text +='<li class="page-item active"><a class="page-link" href="./user_list.html?page_no='+k+'">'+k+'</a></li>';
    }else{
      text +='<li class="page-item"><a class="page-link" href="./user_list.html?page_no='+k+'">'+k+'</a></li>';
    }
  }
  if (total_paging_cnt == 0 || total_paging_cnt == 1 || next_no > total_paging_cnt)
  {
  }else{
    text +='<li class="page-item">';
    text +='  <a class="page-link" href="./user_list.html?page_no='+next_no+'" aria-label="Next">';
    text +='    <span aria-hidden="true">Next</span>';
    text +='    <span class="sr-only">Next</span>';
    text +='  </a>';
    text +='</li>';
  }

  $("#pagination").empty();
  $("#pagination").append(text);
}

$("#search_btn").on("click", function(){
  if($("#search_text").val() == ""){
    alert("이름을 입력해주세요");
    return;
  }else{
    $.ajax({
        type    : "GET",
        url        : "../api/user/index.php?page="+page_no+"&perPage="+per_page,
        headers : {
          "content-type": "application/json",
          Authorization : user_data.token,
        },
        dataType:"json"
    }).done(function (data, textStatus, xhr) {

      if(data.status == 200){
        location.href="../system_management/user_list.html?search_text="+$("#search_text").val();
      }else{
        alert(data.message);
      }
    })
  }
})
